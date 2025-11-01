<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Stall;
use App\Models\Section;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\BillingSetting;
use App\Models\Rate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth; // <-- Import the Auth facade

class StaffController extends Controller
{
    public function getVendors()
    {
        $vendors = User::with(['role', 'stall.section'])
            ->whereHas('role', function ($query) {
                $query->where('name', 'Vendor');
            })
            ->get()
            ->map(function ($user) {
                $appDate = $user->application_date 
                ? $user->application_date->format('Y-m-d') 
                : null;
                return [
                    'id' => $user->id,
                    'vendorName' => $user->name,
                    'contact' => $user->contact_number,
                    'appDate' => $appDate,
                    'section' => optional(optional($user->stall)->section)->name ?? 'Unassigned',
                    'stallNumber' => optional($user->stall)->table_number ?? 'N/A',
                    'daily_rate' => optional($user->stall)->daily_rate,
                    'area' => optional($user->stall)->area,
                ];
            });
    
        return response()->json($vendors);
    }

    public function getSections()
    {
        $sections = Section::query()->distinct()->pluck('name');
        return response()->json($sections);
    }

    public function getBillManagementData()
    {
        $stallsWithMissingInfo = Stall::whereHas('vendor', function ($query) {
                $query->where('role_id', 2)
                      ->where(function ($subQuery) {
                          $subQuery->whereNull('contact_number')
                                   ->orWhere('contact_number', '');
                      });
            })
            ->with('vendor')
            ->get();
        $formattedData = $stallsWithMissingInfo->map(function ($stall) {
            return [
                'id' => $stall->vendor->id,
                'stallNumber' => $stall->table_number,
                'vendorName' => $stall->vendor->name,
            ];
        });
        return response()->json($formattedData);
    }

    public function updateVendor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vendorName' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'appDate' => 'nullable|date',
            'stallNumber' => 'required|string|max:255',
            'section' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $vendor = User::findOrFail($id);
            $vendor->name = $request->input('vendorName');
            $vendor->contact_number = $request->input('contact');
            $vendor->application_date = $request->input('appDate');
            $vendor->save();

            $section = Section::firstWhere('name', $request->input('section'));
            if ($section) {
                 DB::table('stalls')
                    ->where('vendor_id', $id)
                    ->update([
                        'table_number' => $request->input('stallNumber'),
                        'section_id' => $section->id
                    ]);
            }

            // <-- START: Audit Trail for Vendor Update -->
            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Updated vendor details for ' . $vendor->name,
                'module' => 'Vendor Management',
                'result' => 'Success',
                'created_at' => now(),
            ]);
            // <-- END: Audit Trail for Vendor Update -->

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor update failed for ID ' . $id . ': ' . $e->getMessage());

            // <-- START: Audit Trail for Failed Update -->
            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Attempted to update vendor with ID ' . $id,
                'module' => 'Vendor Management',
                'result' => 'Failure',
                'created_at' => now(),
            ]);
            // <-- END: Audit Trail for Failed Update -->

            return response()->json(['message' => 'Failed to update vendor: ' . $e->getMessage()], 500);
        }
    }

    public function getPaymentYears(User $user)
    {
        $stallId = DB::table('stalls')->where('vendor_id', $user->id)->value('id');

        if (!$stallId) {
            return response()->json([]);
        }

        $years = Billing::join('payments', 'billing.id', '=', 'payments.billing_id')
            ->where('billing.stall_id', $stallId)
            ->select(DB::raw('YEAR(payments.payment_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years->push(now()->year);
        }

        return response()->json($years);
    }

    public function getPaymentHistory(User $user)
    {
        $paidBillings = $user->billings()
            ->where('status', 'paid')
            ->with('payment')
            ->latest('period_end')
            ->get();

        return response()->json($paidBillings);
    }

    public function getOutstandingBills(User $user)
    {
        $allOutstandingBills = $user->billings()
            ->where('status', 'unpaid')
            ->get();

        return response()->json($allOutstandingBills);
    }

    public function getBillBreakdown(Billing $billing)
    {
        return response()->json($billing);
    }

    public function getFilteredPaymentHistory(Request $request, User $user)
    {
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search');

        $stallId = DB::table('stalls')->where('vendor_id', $user->id)->value('id');

        if (!$stallId) {
            return response()->json([]);
        }

        $query = Billing::with('payment')
            ->join('payments', 'billing.id', '=', 'payments.billing_id')
            ->where('billing.stall_id', $stallId)
            ->where('billing.status', 'paid')
            ->whereDate('payments.payment_date', '<', Carbon::now()->startOfMonth());
        
        if ($year) {
            $query->whereYear('payments.payment_date', $year);
        }

        if ($month && $month !== "all") {
            $query->whereMonth('payments.payment_date', $month);
        }

        if ($search) {
            $query->where('billing.utility_type', 'like', '%' . $search . '%');
        }

        $bills = $query->orderBy('payments.payment_date', 'desc')->select('billing.*')->get();
        
        return response()->json($bills);
    }

    public function markAsPaid(Request $request, $billingId)
    {
        Log::info('markAsPaid started for billing ID: ' . $billingId);
        
        $billing = Billing::with('stall.vendor')->find($billingId);

        if (!$billing) {
            return response()->json(['message' => 'Billing record not found.'], 404);
        }
        
        if ($billing->status !== 'unpaid') {
            return response()->json(['message' => 'This bill has already been paid.'], 409);
        }
        
        DB::beginTransaction();
        try {
            $billing->status = 'paid';
            $billing->save();

            Payment::create([
                'billing_id' => $billing->id,
                'amount_paid' => $billing->amount,
                'payment_date' => now(),
                'penalty' => 0, 
                'discount' => 0, 
            ]);
            
            // <-- START: Audit Trail for Marking Bill as Paid -->
            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Recorded payment of ₱' . number_format($billing->amount, 2) . ' for bill #' . $billing->id . ' (Vendor: ' . optional($billing->stall->vendor)->name . ')',
                'module' => 'Billing',
                'result' => 'Success',
                'created_at' => now(),
            ]);
            // <-- END: Audit Trail -->
            
            DB::commit();
            Log::info('markAsPaid completed for billing ID: ' . $billingId);
            return response()->json(['message' => 'Payment recorded successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('markAsPaid failed for billing ID: ' . $billingId . ' - ' . $e->getMessage());
            return response()->json(['message' => 'Failed to record payment.'], 500);
        }
    }

    public function getMonthlyReport(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $targetDate = Carbon::parse($validated['month']);
        $year = $targetDate->year;
        $month = $targetDate->month;

        $totalCollected = Payment::whereYear('payment_date', $year)->whereMonth('payment_date', $month)->sum('amount_paid');
        $newVendors = User::whereYear('application_date', $year)->whereMonth('application_date', $month)->count();

        $collectionsBreakdown = DB::table('payments')
            ->join('billing', 'payments.billing_id', '=', 'billing.id')
            ->join('stalls', 'billing.stall_id', '=', 'stalls.id')
            ->join('sections', 'stalls.section_id', '=', 'sections.id')
            ->whereYear('payments.payment_date', $year)
            ->whereMonth('payments.payment_date', $month)
            ->select(
                'sections.name as section_name',
                'billing.utility_type',
                DB::raw('SUM(payments.amount_paid) as total')
            )
            ->groupBy('sections.name', 'billing.utility_type')
            ->get();
            
        $delinquentVendors = User::whereHas('role', fn($q) => $q->where('name', 'Vendor'))
            ->whereHas('billings', function ($query) use ($targetDate) {
                $query->where('status', 'unpaid')
                      ->where('due_date', '<=', $targetDate->endOfMonth());
            })
            ->with(['stall:id,vendor_id,table_number', 'billings' => function($q) use ($targetDate) {
                $q->where('status', 'unpaid')->where('due_date', '<=', $targetDate->endOfMonth())->select('stall_id', 'utility_type', 'amount');
            }])
            ->get(['id', 'name']);

        $delinquentVendors->each(function ($vendor) {
            $vendor->total_due = $vendor->billings->sum('amount');
        });

        $utilityPeriod = $targetDate->copy()->subMonthNoOverflow();

        $collectionByUtility = Billing::select(
            'utility_type',
            DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid'),
            DB::raw('SUM(CASE WHEN status = "unpaid" THEN amount ELSE 0 END) as unpaid')
        )
            ->where(function ($query) use ($year, $month) {
                // Rent bills belong to their own consumption month
                $query->where('utility_type', 'Rent')
                    ->whereYear('period_start', $year)
                    ->whereMonth('period_start', $month);
            })
            ->orWhere(function ($query) use ($utilityPeriod) {
                // Utility bills belong to the month AFTER their consumption period
                $query->whereIn('utility_type', ['Water', 'Electricity'])
                    ->whereYear('period_start', $utilityPeriod->year)
                    ->whereMonth('period_start', $utilityPeriod->month);
            })
            ->groupBy('utility_type')
            ->get()
            ->keyBy('utility_type');

            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Generated Monthly Report for ' . $targetDate->format('F Y'),
                'module' => 'Reports',
                'result' => 'Success',
                'created_at' => now(),
            ]);

        return response()->json([
            'report_period' => $targetDate->format('F Y'),
            'kpis' => [
                'total_collection' => $totalCollected,
                'new_vendors' => $newVendors,
                'delinquent_vendors_count' => $delinquentVendors->count(),
            ],
            'collections_breakdown' => $collectionsBreakdown,
            'delinquent_vendors' => $delinquentVendors,
            'chart_data' => [
                'by_utility' => $collectionByUtility,
            ]
        ]);
    }
    
     public function getVendorDashboardData(User $user)
    {
        $vendor = $user;
        $vendor->load('stall.section');

        $outstandingBills = Billing::where('stall_id', $vendor->stall->id)
            ->where(function ($query) {
                $query->where('status', 'unpaid')
                      ->orWhere(function ($subQuery) {
                          $subQuery->where('status', 'paid')
                                   ->whereHas('payment', function ($paymentQuery) {
                                       $paymentQuery->whereMonth('payment_date', Carbon::now()->month)
                                                    ->whereYear('payment_date', Carbon::now()->year);
                                   });
                      });
            })
            ->with('payment')
            ->orderBy('due_date', 'desc')
            ->get();

        $billingSettings = BillingSetting::all()->keyBy('utility_type');
        $today = Carbon::today();

        foreach ($outstandingBills as $bill) {
            $bill->original_amount = $bill->amount;
            $originalDueDate = Carbon::parse($bill->due_date);

            if ($bill->status === 'unpaid' && $today->gt($originalDueDate)) {
                $settings = $billingSettings->get($bill->utility_type);
                if ($bill->utility_type === 'Rent' && $settings) {
                    $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                    $surcharge = $bill->original_amount * ($settings->surcharge_rate ?? 0);
                    $interest = $bill->original_amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
                    $bill->current_amount_due = $bill->original_amount + $surcharge + $interest;
                } else if ($settings) {
                    $penalty = $bill->original_amount * ($settings->penalty_rate ?? 0);
                    $bill->current_amount_due = $bill->original_amount + $penalty;
                }
            } else {
                 $bill->current_amount_due = $bill->original_amount;
            }
        }
        
        return response()->json([
            'outstandingBills' => $outstandingBills,
        ]);
    }
    
    public function getUnassignedVendors()
    {
        $vendors = User::whereHas('role', fn($q) => $q->where('name', 'Vendor'))
                        ->whereDoesntHave('stall')
                        ->where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'name']);

        return response()->json($vendors);
    }

    public function getAvailableStalls(Request $request)
    {
        $query = Stall::with('section')
                      ->whereNull('vendor_id');
        
        if ($request->filled('section')) {
            $query->whereHas('section', fn($q) => $q->where('name', 'like', '%' . $request->section . '%'));
        }

        $stalls = $query->orderBy('table_number')->get();

        return response()->json($stalls);
    }

    public function assignStall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|integer|exists:users,id',
            'stall_id' => 'required|integer|exists:stalls,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $stall = Stall::find($request->stall_id);
            $vendor = User::find($request->vendor_id);
            
            if ($stall->vendor_id) {
                return response()->json(['message' => 'This stall is already assigned.'], 409);
            }

            $stall->vendor_id = $request->vendor_id;
            $stall->save();

            // <-- START: Audit Trail for Stall Assignment -->
            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Assigned stall ' . $stall->table_number . ' to vendor ' . $vendor->name,
                'module' => 'Stall Assignment',
                'result' => 'Success',
                'created_at' => now(),
            ]);
            // <-- END: Audit Trail -->

            Artisan::call('billing:generate-new-vendor', [
                'stall_id' => $stall->id
            ]);      

            DB::commit();

            return response()->json(['message' => 'Stall assigned and initial bills generated successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stall assignment failed: ' . $e->getMessage());

             // <-- START: Audit Trail for Failed Assignment -->
            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Failed to assign stall to vendor ID ' . $request->vendor_id,
                'module' => 'Stall Assignment',
                'result' => 'Failure',
                'created_at' => now(),
            ]);
            // <-- END: Audit Trail -->
            
            return response()->json(['message' => 'An error occurred during stall assignment.'], 500);
        }
    }
}