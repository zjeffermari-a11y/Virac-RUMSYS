<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Section;
use App\Models\Billing;
use App\Models\Payment;
use Illuminate\View\View;
use App\Models\BillingSetting;
use App\Http\Controllers\Api\DashboardController;
use Carbon\Carbon;

class StaffPortalController extends Controller
{
    /**
     * Display the staff portal and pre-load initial data.
     */
    public function index(): View
    {
        // 1. Prepare data for the Vendor Management part of the page
        $vendors = User::with(['role', 'stall' => function ($query) {
                $query->select('id', 'section_id', 'table_number', 'daily_rate', 'area', 'vendor_id');
            }, 'stall.section'])
            ->whereHas('role', function ($query) {
                $query->where('name', 'Vendor');
            })
            ->get()
            ->map(function ($user) {
                $appDate = $user->application_date instanceof \Carbon\Carbon
                    ? $user->application_date->format('Y-m-d')
                    : $user->application_date;

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

        $sections = Section::query()->distinct()->pluck('name');

        $initialState = [
            'vendors' => $vendors,
            'sections' => $sections,
        ];

        // Fetch years and sections for dashboard filters FIRST
        $years = Billing::select(DB::raw('YEAR(period_start) as year'))
            ->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years->push(now()->year);
        }
        $sectionsForFilter = Section::all(['name', 'id']);

        // 2. Prepare dashboard data with robust error handling
        $dashboardState = null;
        try {
            $dashboardApiController = new DashboardController();
            $request = new Request();
            
            $needsSupportResponse = $dashboardApiController->getVendorsNeedingSupport($request)->getData(true);
            
            $dashboardState = [
                'kpis' => $dashboardApiController->getKpis($request)->getData(true),
                'vendorDistribution' => $dashboardApiController->getVendorDistribution($request)->getData(true),
                'collectionTrends' => $dashboardApiController->getCollectionTrends($request)->getData(true),
                'utilityConsumption' => $dashboardApiController->getUtilityConsumption($request)->getData(true),
                'vendorPulse' => [
                    'topPerformers' => $dashboardApiController->getTopPerformingVendors($request)->getData(true),
                    'needsSupport' => $needsSupportResponse['data'] ?? [],
                ],
                'filterData' => [
                    'years' => $years,
                    'sections' => $sectionsForFilter,
                ],
            ];
            
        } catch (\Exception $e) {
            \Log::error('Staff Dashboard data fetching error: ' . $e->getMessage());
            $dashboardState = null;
        }

        // Fetch billing settings for discount/penalty calculations
        $billingSettings = BillingSetting::all()->keyBy('utility_type');

        return view('staff_portal.staff', [
            'initialState' => $initialState,
            'dashboardState' => $dashboardState,
            'billingSettings' => $billingSettings
        ]);
    }

    public function outstandingBillsPartial(User $vendor): View
    {
        $outstandingBills = $vendor->billings()
            ->where('status', 'unpaid')
            ->get();

        return view('staff_portal.partials.outstanding_bills', [
            'vendor' => $vendor,
            'outstandingBills' => $outstandingBills,
        ]);
    }

    public function paymentHistoryPartial(User $vendor): View
    {
        $paymentHistory = $vendor->billings()
            ->where('status', 'paid')
            ->with('payment')
            ->latest('period_end')
            ->get();

        return view('staff_portal.partials.payment_history', [
            'vendor' => $vendor,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    public function storePayment(Request $request, User $vendor)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($request, $vendor) {
                $unpaidBills = $vendor->billings()->where('status', 'unpaid')->get();

                if ($unpaidBills->isEmpty()) {
                    throw new \Exception("This vendor has no outstanding bills to pay.");
                }

                foreach ($unpaidBills as $bill) {
                    Payment::create([
                        'billing_id' => $bill->id,
                        'amount_paid' => $bill->amount,
                        'payment_date' => $request->payment_date,
                    ]);

                    $bill->status = 'paid';
                    $bill->save();
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payment recorded successfully!');
    }

    public function viewAsVendorPartial(User $vendor): View
    {
        if (!$vendor->stall) {
            return view('staff_portal.partials._vendor-no-stall-partial', ['vendor' => $vendor]);
        }
        
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
            $bill->original_amount = (float) $bill->amount;
            $originalDueDate = Carbon::parse($bill->due_date);
            $settings = $billingSettings->get($bill->utility_type);
            
            // Initialize all calculation properties
            $bill->interest_months = 0;
            $bill->discount_applied = 0;
            $bill->penalty_applied = 0;
            $bill->display_amount_due = $bill->original_amount;
            $bill->amount_after_due = $bill->original_amount;


            if ($bill->status === 'paid') {
                $paid_amount = (float) (optional($bill->payment)->amount_paid ?? $bill->original_amount);
                $bill->display_amount_due = $paid_amount;
                $bill->amount_after_due = $paid_amount;
            } else {
                if ($today->gt($originalDueDate)) {
                    // --- Bill is OVERDUE ---
                    if ($bill->utility_type === 'Rent' && $settings) {
                        $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                        $surcharge = $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                        $interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                        
                        // Store values for frontend display
                        $bill->interest_months = $interest_months;
                        $bill->penalty_applied = $surcharge + $interest;
                        
                        // "Amount Due" is the current total owed
                        $bill->display_amount_due = $bill->original_amount + $surcharge + $interest;

                        // "Amount After Due" projects the total for the NEXT month's penalty
                        $projected_interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * ($interest_months + 1);
                        $bill->amount_after_due = $bill->original_amount + $surcharge + $projected_interest;

                    } else if ($settings) { // For overdue utilities
                        $penalty = $bill->original_amount * (float)($settings->penalty_rate ?? 0);
                        
                        // Store penalty for frontend display
                        $bill->penalty_applied = $penalty;
                        
                        $bill->display_amount_due = $bill->original_amount + $penalty;
                        $bill->amount_after_due = $bill->original_amount + $penalty; // No further increase
                    }
                } else {
                    // --- Bill is NOT YET OVERDUE ---
                    // "Amount Due" is the base amount or discounted amount
                    $bill->display_amount_due = $bill->original_amount;
                    
                    // Check for early payment discount for Rent
                    if ($today->day <= 15) {
                        $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                        $currentMonth = $today->format('Y-m');
                        if ($billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                            $discountAmount = $bill->original_amount * (float)$settings->discount_rate;
                            $bill->display_amount_due = $bill->original_amount - $discountAmount;
                            $bill->discount_applied = $discountAmount;
                        }
                    }

                    // "Amount After Due" shows the first penalty that will be applied if the due date is missed
                    $projected_penalty = 0;
                    if ($settings) {
                         if ($bill->utility_type === 'Rent') {
                            $projected_penalty = $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                        } else {
                            $projected_penalty = $bill->original_amount * (float)($settings->penalty_rate ?? 0);
                        }
                    }
                    $bill->amount_after_due = $bill->original_amount + $projected_penalty;
                }
            }
        }

        $groupedBills = $outstandingBills->groupBy(function ($bill) {
            $periodDate = Carbon::parse($bill->period_start);
            if (in_array($bill->utility_type, ['Water', 'Electricity'])) {
                return $periodDate->addMonth()->format('F Y');
            }
            return $periodDate->format('F Y');
        });

        return view('staff_portal.partials._view-as-vendor-partial', [
            'vendor' => $vendor,
            'groupedBills' => $groupedBills,
            'outstandingBills' => $outstandingBills,
            'billingSettings' => $billingSettings,
        ]);
    }

    public function paymentHistoryContainerPartial(User $vendor): View
    {
        return view('staff_portal.partials._payment-history-partial', [
            'vendor' => $vendor,
        ]);
    }
}
