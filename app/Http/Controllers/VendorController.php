<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Billing;
use App\Models\Rate;
use App\Models\Stall;
use App\Models\BillingSetting;
use App\Models\UtilityReading;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password; 

class VendorController extends Controller
{
    public function dashboard(?User $vendor = null)
    {
        if (is_null($vendor) && Auth::user()->isVendor()) {
            $vendor = Auth::user();
        }

        if (!$vendor || !$vendor->stall) {
            abort(404, 'Vendor or stall not found.');
        }

        $vendor->load('stall.section');

        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();
        
        // Include unpaid bills OR paid bills from current month
        $outstandingBills = Billing::where('stall_id', $vendor->stall->id)
            ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
                $query->where('status', 'unpaid')
                    ->orWhere(function($q) use ($currentMonthStart, $currentMonthEnd) {
                        $q->where('status', 'paid')
                            ->whereHas('payment', function($paymentQuery) use ($currentMonthStart, $currentMonthEnd) {
                                $paymentQuery->whereBetween('payment_date', [
                                    $currentMonthStart->toDateString(),
                                    $currentMonthEnd->toDateString()
                                ]);
                            });
                    });
            })
            ->with('payment:id,billing_id,amount_paid,payment_date')
            ->select('id', 'stall_id', 'utility_type', 'period_start', 'period_end', 'amount', 'due_date', 'disconnection_date', 'status', 'consumption', 'current_reading', 'previous_reading', 'rate')
            ->orderBy('due_date', 'desc')
            ->get();

        // Cache billing settings (rarely changes)
        $billingSettings = cache()->remember('billing_settings', 3600, function () {
            return BillingSetting::all()->keyBy('utility_type');
        });
        
        $today = Carbon::today();

        // Pre-parse dates to avoid repeated parsing
        $today = Carbon::today();
        $todayDay = $today->day;
        $currentMonth = $today->format('Y-m');

        foreach ($outstandingBills as $bill) {
            $bill->original_amount = (float) $bill->amount;
            $originalDueDate = Carbon::parse($bill->due_date);
            $settings = $billingSettings->get($bill->utility_type);

            // Initialize calculation properties
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
                    // Bill is OVERDUE
                    if ($bill->utility_type === 'Rent' && $settings) {
                        $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                        $surcharge = $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                        $interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                        
                        // Store values for frontend display
                        $bill->interest_months = $interest_months;
                        $bill->penalty_applied = $surcharge + $interest;
                        
                        $bill->display_amount_due = $bill->original_amount + $surcharge + $interest;

                        $projected_interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * ($interest_months + 1);
                        $bill->amount_after_due = $bill->original_amount + $surcharge + $projected_interest;

                    } else if ($settings) {
                        $penalty = $bill->original_amount * (float)($settings->penalty_rate ?? 0);
                        
                        // Store penalty for frontend display
                        $bill->penalty_applied = $penalty;
                        
                        $bill->display_amount_due = $bill->original_amount + $penalty;
                        $bill->amount_after_due = $bill->original_amount + $penalty;
                    }
                } else {
                    // Bill is NOT YET OVERDUE
                    $bill->display_amount_due = $bill->original_amount;

                    // Check for early payment discount
                    if ($todayDay <= 15) {
                        $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                        if ($billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                            // Discount calculation: Original Price - (Original Price * discount_rate)
                            // Equivalent to: Original Price * (1 - discount_rate)
                            $bill->display_amount_due = $bill->original_amount - ($bill->original_amount * (float)$settings->discount_rate);
                            $bill->discount_applied = $bill->original_amount * (float)$settings->discount_rate;
                        }
                    }

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

        // Optimize grouping by pre-parsing dates
        $groupedBills = $outstandingBills->groupBy(function ($bill) {
            $periodDate = Carbon::parse($bill->period_start);
            if (in_array($bill->utility_type, ['Water', 'Electricity'])) {
                return $periodDate->copy()->addMonth()->format('F Y');
            }
            return $periodDate->format('F Y');
        });
        
        // Calculate total outstanding balance (sum of all unpaid bills' amount_after_due)
        $totalOutstandingBalance = $outstandingBills->sum('amount_after_due');
        
        // Cache utility rates (rarely changes)
        $utilityRates = cache()->remember('utility_rates', 3600, function () {
            return Rate::whereIn('utility_type', ['Electricity', 'Water'])
                ->select('id', 'utility_type', 'rate', 'monthly_rate')
                ->get()
                ->keyBy('utility_type');
        });
        
        $stallData = $vendor->stall;
        
        // Pre-load current month's payment history for instant display
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $paymentHistoryInitial = $this->getPaymentHistoryData($vendor, $currentYear, $currentMonth, null, 1, 20);

        return view('vendor_portal.vendor', [
            'vendor' => $vendor,
            'outstandingBills' => $outstandingBills,
            'groupedBills' => $groupedBills,
            'totalOutstandingBalance' => $totalOutstandingBalance,
            'utilityRates' => $utilityRates,
            'stallData' => $stallData,
            'billingSettings' => $billingSettings,
            'paymentHistoryInitial' => $paymentHistoryInitial,
            'isStaffView' => !Auth::user()->isVendor()
        ]);
    }

    public function getDashboardData(?User $vendor = null)
    {
    if (is_null($vendor) && Auth::user()->isVendor()) {
        $vendor = Auth::user();
    }

    if (!$vendor || !$vendor->stall) {
        return response()->json(['error' => 'Vendor or stall not found.'], 404);
    }

    $vendor->load('stall.section');

    $today = Carbon::today();
    $currentMonthStart = $today->copy()->startOfMonth();
    $currentMonthEnd = $today->copy()->endOfMonth();
    
    // Include unpaid bills OR paid bills from current month
    $outstandingBills = Billing::where('stall_id', $vendor->stall->id)
        ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
            $query->where('status', 'unpaid')
                ->orWhere(function($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->where('status', 'paid')
                        ->whereHas('payment', function($paymentQuery) use ($currentMonthStart, $currentMonthEnd) {
                            $paymentQuery->whereBetween('payment_date', [
                                $currentMonthStart->toDateString(),
                                $currentMonthEnd->toDateString()
                            ]);
                        });
                });
        })
        ->with('payment:id,billing_id,amount_paid,payment_date')
        ->select('id', 'stall_id', 'utility_type', 'period_start', 'period_end', 'amount', 'due_date', 'disconnection_date', 'status', 'consumption', 'current_reading', 'previous_reading', 'rate')
        ->orderBy('due_date', 'desc')
        ->get();

    // Cache billing settings (rarely changes)
    $billingSettings = cache()->remember('billing_settings', 3600, function () {
        return BillingSetting::all()->keyBy('utility_type');
    });
    
    $today = Carbon::today();

    // Pre-calculate today values
    $todayDay = $today->day;
    $currentMonth = $today->format('Y-m');

    foreach ($outstandingBills as $bill) {
        $bill->original_amount = (float) $bill->amount;
        $originalDueDate = Carbon::parse($bill->due_date);
        $settings = $billingSettings->get($bill->utility_type);

        $bill->interest_months = 0;
        $bill->discount_applied = 0;
        $bill->penalty_applied = 0;
        $current_total_due = $bill->original_amount;

        $projected_amount = $bill->original_amount;
        if ($settings) {
            if ($bill->utility_type === 'Rent') {
                $projected_amount += $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                $projected_amount += $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0);
            } else {
                $projected_amount += $bill->original_amount * (float)($settings->penalty_rate ?? 0);
            }
        }
        $bill->amount_after_due = $projected_amount;

        if ($bill->status === 'paid') {
            $bill->current_amount_due = (float) (optional($bill->payment)->amount_paid ?? $bill->original_amount);
        } else {
            if ($today->gt($originalDueDate)) {
                $penaltyAmount = 0;
                if ($bill->utility_type === 'Rent' && $settings) {
                    $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                    $surcharge = $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                    $interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                    $penaltyAmount = $surcharge + $interest;
                    $bill->interest_months = $interest_months;
                } else if ($settings) {
                    $penaltyAmount = $bill->original_amount * (float)($settings->penalty_rate ?? 0);
                }
                $current_total_due += $penaltyAmount;
                $bill->penalty_applied = $penaltyAmount;
            } else if ($todayDay <= 15) {
                $billMonth = Carbon::parse($bill->period_start)->format('Y-m');

                if ($billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                    // Discount calculation: Original Price - (Original Price * discount_rate)
                    // Equivalent to: Original Price * (1 - discount_rate)
                    $discountAmount = $bill->original_amount * (float)$settings->discount_rate;
                    $current_total_due -= $discountAmount;
                    $bill->discount_applied = $discountAmount;
                }
            }
        }

        $bill->current_amount_due = $current_total_due;
        $bill->display_amount_due = $current_total_due;
    }

    return response()->json([
        'outstandingBills' => $outstandingBills
    ]);
    }

    private function getPaymentHistoryData($vendor, $year = null, $month = null, $search = null, $page = 1, $perPage = 20)
    {
        if (!$vendor || !$vendor->stall) {
            return ['data' => [], 'total' => 0, 'has_more' => false];
        }

        // Build cache key for this query
        $cacheKey = "payment_history_vendor_{$vendor->stall->id}_y{$year}_m{$month}_s{$search}_p{$page}";
        
        // Cache results for 5 minutes (short cache for search results)
        $cachedResult = cache()->get($cacheKey);
        if ($cachedResult && !$search) { // Don't cache search results
            return $cachedResult;
        }

        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        
        // Optimized query: Start from payments table for better index usage
        // Exclude payments from current month (they stay in outstanding balance)
        $baseQuery = DB::table('payments')
            ->join('billing', 'payments.billing_id', '=', 'billing.id')
            ->where('billing.stall_id', $vendor->stall->id)
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
            // If only month is provided without year, use current year
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

        // Get total count (clone query to avoid affecting main query)
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
        $billingSettings = BillingSetting::all()->keyBy('utility_type');
        
        // Format response to match expected structure
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
        
        // Cache result for 5 minutes (only if no search, as search results change frequently)
        if (!$search) {
            cache()->put($cacheKey, $response, 300); // 5 minutes
        }

        return $response;
    }

    public function paymentHistoryApi(Request $request)
    {
        $vendor = Auth::user();
        if (!$vendor->stall) {
            return response()->json(['error' => 'User not assigned to a stall.'], 403);
        }
        
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search');
        $page = $request->get('page', 1);
        
        $result = $this->getPaymentHistoryData($vendor, $year, $month, $search, $page, 20);

        return response()->json($result);
    }

    public function paymentYearsApi()
    {
        $vendor = Auth::user();
        if (!$vendor->stall) {
            return response()->json(['error' => 'User not assigned to a stall.'], 403);
        }

        // Cache years list (changes infrequently)
        $cacheKey = "payment_years_vendor_{$vendor->stall->id}";
        $years = cache()->remember($cacheKey, 3600, function () use ($vendor) {
            // Optimized: Direct query on payments table
            $years = DB::table('payments')
                ->join('billing', 'payments.billing_id', '=', 'billing.id')
            ->where('billing.stall_id', $vendor->stall->id)
                ->where('billing.status', 'paid')
                ->select(DB::raw('DISTINCT YEAR(payments.payment_date) as year'))
            ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($years)) {
                $years = [now()->year];
        }

            return $years;
        });

        return response()->json($years);
    }

    public function index(): View
    {
        return view('vendor_portal.vendor');
    }

    public function showChangePasswordForm()
    {
        return view('vendor_portal.auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/', // Only alphanumeric, dots, underscores, hyphens
                'unique:users,username,' . $user->id, // Unique except for current user
            ],
            'current_password' => 'required|current_password',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->mixedCase()
            ],
        ], [
            'username.required' => 'Username is required.',
            'username.regex' => 'Username can only contain letters, numbers, dots, underscores, and hyphens.',
            'username.unique' => 'This username is already taken. Please choose another one.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character (!@#$%^&*).',
            'password.mixed' => 'Password must contain both uppercase and lowercase letters.',
        ]);

        // Update username (only allowed on first login)
        $usernameChanged = false;
        if ($request->username !== $user->username) {
            $user->username = $request->username;
            $usernameChanged = true;
        }
        
        // Update password
        $user->password = Hash::make($request->password);
        $user->password_changed_at = Carbon::now();
        $user->save();

        DB::table('audit_trails')->insert([
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'action' => 'Completed initial password and username change',
            'module' => 'Authentication',
            'result' => 'Success',
            'created_at' => now(),
        ]);

        $message = 'Account updated successfully!';
        if ($usernameChanged) {
            $message = 'Username and password updated successfully! Please use your new username for future logins.';
        }

        return redirect()->route('vendor.dashboard')
            ->with('success', $message);
    }

    public function analytics(?User $vendor = null)
    {
        if (is_null($vendor) && Auth::user()->isVendor()) {
            $vendor = Auth::user();
        }

        if (!$vendor || !$vendor->stall) {
            return response()->json(['error' => 'Vendor or stall not found.'], 404);
        }

        $stallId = $vendor->stall->id;

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
        // Optimize: Only get billings that are paid or overdue (skip future unpaid bills)
        $billings = Billing::where('stall_id', $stallId)
            ->where(function ($query) {
                $query->where('status', 'paid')
                      ->orWhere(function ($q) {
                          $q->where('status', 'unpaid')
                            ->where('due_date', '<=', Carbon::today());
                      });
            })
            ->with('payment:id,billing_id,payment_date')
            ->select('id', 'stall_id', 'utility_type', 'due_date', 'status')
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
}