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
use App\Models\UtilityReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth; // <-- Import the Auth facade
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogger;

class StaffController extends Controller
{
    public function getVendors()
    {
        // Fixed N+1 by eager loading with lean selects
        $vendors = User::select('users.id', 'users.name', 'users.contact_number', 'users.application_date', 'users.profile_picture')
            ->with(['role:id,name', 'stall:id,vendor_id,table_number,daily_rate,area,section_id', 'stall.section:id,name'])
            ->leftJoin('stalls', 'users.id', '=', 'stalls.vendor_id')
            ->whereHas('role', function ($query) {
                $query->where('name', 'Vendor');
            })
            ->orderBy('stalls.table_number', 'asc') // Sort alphabetically by stall/table number
            ->groupBy('users.id', 'users.name', 'users.contact_number', 'users.application_date', 'users.profile_picture')
            ->paginate(15) // Replaced get() with paginate()
            ->through(function ($user) {
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
                    'profile_picture' => $user->profile_picture ? $user->profile_picture_url : null,
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
        // Fixed N+1 and used lean selects
        $stallsWithMissingInfo = Stall::select('id', 'vendor_id', 'table_number')
            ->whereHas('vendor', function ($query) {
                $query->where('role_id', 2)
                      ->where(function ($subQuery) {
                          $subQuery->whereNull('contact_number')
                                   ->orWhere('contact_number', '');
                      });
            })
            ->with('vendor:id,name')
            ->orderBy('table_number', 'asc') // Sort alphabetically by stall/table number
            ->paginate(15) // Replaced get() with paginate()
            ->through(function ($stall) {
                return [
                    'id' => $stall->vendor->id,
                    'stallNumber' => $stall->table_number,
                    'vendorName' => $stall->vendor->name,
                ];
            });

        return response()->json($stallsWithMissingInfo);
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
            // <-- START: Audit Trail for Vendor Update -->
            AuditLogger::log(
                'Updated vendor details',
                'Vendor Management',
                'Success',
                ['vendor_id' => $vendor->id, 'name' => $vendor->name, 'changes' => $request->all()]
            );
            // <-- END: Audit Trail for Vendor Update -->
            // <-- END: Audit Trail for Vendor Update -->

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor update failed for ID ' . $id . ': ' . $e->getMessage());

            // <-- START: Audit Trail for Failed Update -->
            // <-- START: Audit Trail for Failed Update -->
            AuditLogger::log(
                'Attempted to update vendor',
                'Vendor Management',
                'Failure',
                ['vendor_id' => $id, 'error' => $e->getMessage()]
            );
            // <-- END: Audit Trail for Failed Update -->
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
            ->select(DB::raw('EXTRACT(YEAR FROM payments.payment_date) as year'))
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
        $today = \Carbon\Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        
        // Only show paid bills from previous months (before current month)
        $paidBillings = $user->billings()
            ->where('status', 'paid')
            ->whereHas('payment', function($query) use ($currentMonthStart) {
                $query->where('payment_date', '<', $currentMonthStart->toDateString());
            })
            ->with('payment')
            ->latest('period_end')
            ->get();

        return response()->json($paidBillings);
    }

    public function getOutstandingBills(User $user)
    {
        $today = \Carbon\Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();
        
        // Include unpaid bills OR paid bills from current month
        // Use leftJoin for more explicit control over the payment_date filter
        $allOutstandingBills = $user->billings()
            ->leftJoin('payments', 'billing.id', '=', 'payments.billing_id')
            ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
                $query->where('billing.status', 'unpaid')
                    ->orWhere(function($q) use ($currentMonthStart, $currentMonthEnd) {
                        $q->where('billing.status', 'paid')
                            ->whereNotNull('payments.payment_date')
                            ->whereBetween('payments.payment_date', [
                                $currentMonthStart->toDateString(),
                                $currentMonthEnd->toDateString()
                            ]);
                    });
            })
            ->select('billing.*')
            ->with('payment')
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
        $page = $request->get('page', 1);
        $perPage = 20;

        $stallId = DB::table('stalls')->where('vendor_id', $user->id)->value('id');

        if (!$stallId) {
            return response()->json(['data' => [], 'total' => 0, 'has_more' => false]);
        }

        // Build cache key
        $cacheKey = "payment_history_staff_vendor_{$user->id}_y{$year}_m{$month}_s{$search}_p{$page}";
        
        // Cache results for 5 minutes (short cache for search results)
        $cachedResult = cache()->get($cacheKey);
        if ($cachedResult && !$search) {
            return response()->json($cachedResult);
        }

        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        
        // Optimized query: Start from payments table for better index usage
        // Exclude payments from current month (they stay in outstanding balance)
        $baseQuery = DB::table('payments')
            ->join('billing', 'payments.billing_id', '=', 'billing.id')
            ->where('billing.stall_id', $stallId)
            ->where('billing.status', 'paid')
            ->where('payments.payment_date', '<', $currentMonthStart->toDateString());
        
        // Use date range instead of whereYear/whereMonth for better index usage
        if ($year) {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();
            
            if ($month && $month !== "all") {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            }
            
            $baseQuery->whereBetween('payments.payment_date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ]);
        } elseif ($month && $month !== "all") {
            $year = Carbon::now()->year;
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $baseQuery->whereBetween('payments.payment_date', [
                $startDate->toDateString(),
                $endDate->toDateString()
            ]);
        }

        if ($search) {
            $baseQuery->where('billing.utility_type', 'like', '%' . $search . '%');
        }

        // Get total count
        $total = (clone $baseQuery)->count();
        
        // Get paginated results
        $bills = (clone $baseQuery)
            ->select(
                'billing.id',
                'billing.stall_id',
                'billing.utility_type',
                'billing.period_start',
                'billing.period_end',
                'billing.amount',
                'billing.due_date',
                'billing.disconnection_date',
                'billing.status',
                'payments.amount_paid',
                'payments.payment_date'
            )
            ->orderBy('payments.payment_date', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Get billing settings for recalculation
        $billingSettings = \App\Models\BillingSetting::all()->keyBy('utility_type');
        
        // Format response
        $formattedBills = $bills->map(function ($bill) use ($billingSettings) {
            $originalAmount = (float) $bill->amount;
            $paymentDate = Carbon::parse($bill->payment_date);
            $dueDate = Carbon::parse($bill->due_date);
            $finalAmount = $originalAmount;
            $settings = $billingSettings->get($bill->utility_type);
            
            // Recalculate the correct amount based on payment date
            if ($paymentDate->gt($dueDate)) {
                // Payment was LATE, calculate penalties
                if ($bill->utility_type === 'Rent' && $settings) {
                    $interest_months = (int) floor($dueDate->floatDiffInMonths($paymentDate));
                    $surcharge = $originalAmount * (float)($settings->surcharge_rate ?? 0);
                    $interest = $originalAmount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                    $finalAmount = $originalAmount + $surcharge + $interest;
                } else if ($settings) {
                    $penalty = $originalAmount * (float)($settings->penalty_rate ?? 0);
                    $finalAmount = $originalAmount + $penalty;
                }
            } else if ($paymentDate->day <= 15) {
                // Payment was ON TIME (before due date and within discount period), check for discount
                $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                $paymentMonth = $paymentDate->format('Y-m');

                if ($billMonth === $paymentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                    // Discount calculation: Original Price - (Original Price * discount_rate)
                    // Equivalent to: Original Price * (1 - discount_rate)
                    $finalAmount = $originalAmount - ($originalAmount * (float)$settings->discount_rate);
                }
            }
            
            return (object) [
                'id' => $bill->id,
                'stall_id' => $bill->stall_id,
                'utility_type' => $bill->utility_type,
                'period_start' => $bill->period_start,
                'period_end' => $bill->period_end,
                'amount' => $bill->amount,
                'due_date' => $bill->due_date,
                'disconnection_date' => $bill->disconnection_date,
                'status' => $bill->status,
                'payment' => (object) [
                    'amount_paid' => $finalAmount, // Use recalculated amount
                    'payment_date' => $bill->payment_date,
                ],
            ];
        });

        $lastPage = (int) ceil($total / $perPage);
        
        $response = [
            'data' => $formattedBills,
            'current_page' => (int) $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'has_more' => $page < $lastPage,
        ];
        
        // Cache result for 5 minutes (only if no search)
        if (!$search) {
            cache()->put($cacheKey, $response, 300);
        }
        
        return response()->json($response);
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
            // <-- START: Audit Trail for Marking Bill as Paid -->
            AuditLogger::log(
                'Recorded payment',
                'Billing',
                'Success',
                [
                    'billing_id' => $billing->id,
                    'amount' => $billing->amount,
                    'vendor' => optional($billing->stall->vendor)->name
                ]
            );
            // <-- END: Audit Trail -->
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
            
        // Fixed N+1 by using withSum
        $delinquentVendors = User::whereHas('role', fn($q) => $q->where('name', 'Vendor'))
            ->whereHas('billings', function ($query) use ($targetDate) {
                $query->where('status', 'unpaid')
                      ->where('due_date', '<=', $targetDate->endOfMonth());
            })
            ->with(['stall:id,vendor_id,table_number', 'billings' => function($q) use ($targetDate) {
                $q->where('status', 'unpaid')->where('due_date', '<=', $targetDate->endOfMonth())->select('stall_id', 'utility_type', 'amount');
            }])
            ->withSum(['billings' => function($q) use ($targetDate) {
                $q->where('status', 'unpaid')->where('due_date', '<=', $targetDate->endOfMonth());
            }], 'amount')
            ->get(['id', 'name']);

        // No need for loop to calculate total_due, it's now in billings_sum_amount
        $delinquentVendors->each(function ($vendor) {
            $vendor->total_due = $vendor->billings_sum_amount;
        });

        $utilityPeriod = $targetDate->copy()->subMonthNoOverflow();

        $collectionByUtility = Billing::select(
            'utility_type',
            DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid"),
            DB::raw("SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) as unpaid")
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

            AuditLogger::log(
                'Generated Monthly Report',
                'Reports',
                'Success',
                ['period' => $targetDate->format('F Y')]
            );

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

        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();
        
        // Include unpaid bills OR paid bills from current month
        // Use leftJoin for more explicit control over the payment_date filter
        $outstandingBills = Billing::where('billing.stall_id', $vendor->stall->id)
            ->leftJoin('payments', 'billing.id', '=', 'payments.billing_id')
            ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
                $query->where('billing.status', 'unpaid')
                    ->orWhere(function($q) use ($currentMonthStart, $currentMonthEnd) {
                        $q->where('billing.status', 'paid')
                            ->whereNotNull('payments.payment_date')
                            ->whereBetween('payments.payment_date', [
                                $currentMonthStart->toDateString(),
                                $currentMonthEnd->toDateString()
                            ]);
                    });
            })
            ->select('billing.*')
            ->with('payment')
            ->orderBy('billing.due_date', 'desc')
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

    public function getVendorAnalytics(User $user)
    {
        if (!$user->stall) {
            return response()->json(['error' => 'Vendor or stall not found.'], 404);
        }

        $stallId = $user->stall->id;

        // Get electricity consumption data (last 12 months)
        $electricityReadings = UtilityReading::where('stall_id', $stallId)
            ->where('utility_type', 'Electricity')
            ->orderBy('reading_date', 'desc')
            ->limit(12)
            ->get()
            ->reverse();

        $consumptionData = [];
        $consumptionLabels = [];
        
        foreach ($electricityReadings as $index => $reading) {
            if ($index > 0) {
                $previousReading = $electricityReadings[$index - 1];
                $consumption = $reading->current_reading - $previousReading->current_reading;
                $consumptionData[] = max(0, $consumption); // Ensure non-negative
                $consumptionLabels[] = Carbon::parse($reading->reading_date)->format('M Y');
            }
        }

        // Get payment tracking data (on-time vs late payments)
        // Get ALL billings for this stall (both paid and unpaid)
        $billings = Billing::where('stall_id', $stallId)
            ->with('payment')
            ->orderBy('due_date', 'desc')
            ->get();

        $today = Carbon::today();
        $onTimeCount = 0;
        $lateCount = 0;
        $paymentTimeline = [];

        foreach ($billings as $billing) {
            $dueDate = Carbon::parse($billing->due_date)->startOfDay();
            $isOnTime = false;
            $recordDate = null;
            
            if ($billing->status === 'paid' && $billing->payment) {
                // For paid bills: check if payment was made on or before due date
                $paymentDate = Carbon::parse($billing->payment->payment_date)->startOfDay();
                $isOnTime = $paymentDate->lte($dueDate);
                $recordDate = $paymentDate;
            } else {
                // For unpaid bills: check if due date has passed
                if ($today->gt($dueDate)) {
                    // Unpaid and past due date = late
                    $isOnTime = false;
                    $recordDate = $dueDate; // Use due date for timeline grouping
                } else {
                    // Unpaid but not yet due = not counted (not late, not on-time yet)
                    continue; // Skip bills that aren't due yet
                }
            }
            
            if ($isOnTime) {
                $onTimeCount++;
            } else {
                $lateCount++;
            }

            // Add to timeline for trend analysis
            if ($recordDate) {
                $paymentTimeline[] = [
                    'date' => $recordDate->format('Y-m'),
                    'on_time' => $isOnTime ? 1 : 0,
                    'late' => $isOnTime ? 0 : 1,
                ];
            }
        }

        // Group timeline by month
        $timelineGrouped = [];
        foreach ($paymentTimeline as $item) {
            $month = $item['date'];
            if (!isset($timelineGrouped[$month])) {
                $timelineGrouped[$month] = ['on_time' => 0, 'late' => 0];
            }
            $timelineGrouped[$month]['on_time'] += $item['on_time'];
            $timelineGrouped[$month]['late'] += $item['late'];
        }

        // Sort by date and get last 12 months
        ksort($timelineGrouped);
        $timelineGrouped = array_slice($timelineGrouped, -12, 12, true);

        $timelineLabels = array_keys($timelineGrouped);
        $timelineOnTime = array_column($timelineGrouped, 'on_time');
        $timelineLate = array_column($timelineGrouped, 'late');

        return response()->json([
            'electricity' => [
                'labels' => $consumptionLabels,
                'data' => $consumptionData,
            ],
            'paymentTracking' => [
                'onTime' => $onTimeCount,
                'late' => $lateCount,
                'total' => $onTimeCount + $lateCount,
            ],
            'paymentTimeline' => [
                'labels' => $timelineLabels,
                'onTime' => $timelineOnTime,
                'late' => $timelineLate,
            ],
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
            // <-- START: Audit Trail for Stall Assignment -->
            AuditLogger::log(
                'Assigned stall',
                'Vendor Management',
                'Success',
                ['stall_number' => $stall->table_number, 'vendor_name' => $vendor->name, 'vendor_id' => $vendor->id]
            );
            // <-- END: Audit Trail -->
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
             // <-- START: Audit Trail for Failed Assignment -->
            AuditLogger::log(
                'Failed to assign stall',
                'Vendor Management',
                'Failure',
                ['vendor_id' => $request->vendor_id, 'error' => $e->getMessage()]
            );
            // <-- END: Audit Trail -->
            // <-- END: Audit Trail -->
            
            return response()->json(['message' => 'An error occurred during stall assignment.'], 500);
        }
    }

    /**
     * Upload profile picture for a vendor (staff only)
     */
    public function uploadVendorProfilePicture(Request $request, $vendorId)
    {
        $vendor = User::with('role')->findOrFail($vendorId);
        
        // Ensure the user is a vendor
        if (!$vendor->role || $vendor->role->name !== 'Vendor') {
            Log::warning('Attempted to upload profile picture for non-vendor user', [
                'user_id' => $vendorId,
                'role' => $vendor->role ? $vendor->role->name : 'No role'
            ]);
            return response()->json(['message' => 'User is not a vendor.'], 403);
        }

        $validated = $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ], [
            'profile_picture.required' => 'Please select an image to upload.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'The image must be a jpeg, png, jpg, or gif file.',
            'profile_picture.max' => 'The image must not be larger than 2MB.',
        ]);

        try {
            // Delete old profile picture if exists
            if ($vendor->profile_picture) {
                Storage::disk('public')->delete($vendor->profile_picture);
            }

            // Store the image
            $image = $request->file('profile_picture');
            $filename = 'profile_' . $vendor->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profile-pictures', $filename, 'public');

            // Update vendor record
            $vendor->profile_picture = $path;
            $vendor->save();

            AuditLogger::log(
                'Uploaded Vendor Profile Picture',
                'Vendor Management',
                'Success',
                ['vendor_id' => $vendor->id, 'staff_id' => Auth::id()]
            );

            // Generate absolute URL for the profile picture from B2
            // B2 returns full public URLs when visibility is 'public'
            $url = Storage::disk('b2')->url($path);
            
            // Ensure HTTPS (B2 URLs should already be HTTPS)
            if (strpos($url, 'http://') === 0) {
                $url = str_replace('http://', 'https://', $url);
            }
            
            return response()->json([
                'message' => 'Vendor profile picture uploaded successfully.',
                'profile_picture_url' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('Vendor profile picture upload failed: ' . $e->getMessage(), [
                'vendor_id' => $vendorId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to upload vendor profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove profile picture for a vendor (staff only)
     */
    public function removeVendorProfilePicture(Request $request, $vendorId)
    {
        $vendor = User::findOrFail($vendorId);
        
        // Ensure the user is a vendor
        if (!$vendor->role || $vendor->role->name !== 'Vendor') {
            return response()->json(['message' => 'User is not a vendor.'], 403);
        }
        
        if ($vendor->profile_picture) {
            try {
                Storage::disk('b2')->delete($vendor->profile_picture);
            } catch (\Exception $e) {
                Log::warning('Failed to delete vendor profile picture from B2', [
                    'path' => $vendor->profile_picture,
                    'error' => $e->getMessage()
                ]);
            }
            $vendor->profile_picture = null;
            $vendor->save();

            AuditLogger::log(
                'Removed Vendor Profile Picture',
                'Vendor Management',
                'Success',
                ['vendor_id' => $vendor->id, 'staff_id' => Auth::id()]
            );

            return response()->json(['message' => 'Vendor profile picture removed successfully.']);
        }

        return response()->json(['message' => 'No profile picture to remove.'], 400);
    }
}