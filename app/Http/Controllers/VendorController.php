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
                    if ($today->day <= 15) {
                        $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                        $currentMonth = $today->format('Y-m');
                        if ($billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                            $discountAmount = $bill->original_amount * (float)$settings->discount_rate;
                            $bill->display_amount_due = $bill->original_amount - $discountAmount;
                            $bill->discount_applied = $discountAmount;
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

        $groupedBills = $outstandingBills->groupBy(function ($bill) {
            $periodDate = Carbon::parse($bill->period_start);
            if (in_array($bill->utility_type, ['Water', 'Electricity'])) {
                return $periodDate->addMonth()->format('F Y');
            }
            return $periodDate->format('F Y');
        });
        
        $utilityRates = Rate::whereIn('utility_type', ['Electricity', 'Water'])->get()->keyBy('utility_type');
        $stallData = $vendor->stall;

        return view('vendor_portal.vendor', [
            'vendor' => $vendor,
            'outstandingBills' => $outstandingBills,
            'groupedBills' => $groupedBills,
            'utilityRates' => $utilityRates,
            'stallData' => $stallData,
            'billingSettings' => $billingSettings,
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
            } else if ($today->day <= 15) {
                $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                $currentMonth = $today->format('Y-m');

                if ($billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
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

    public function paymentHistoryApi(Request $request)
    {
        $vendor = Auth::user();
        if (!$vendor->stall) {
            return response()->json(['error' => 'User not assigned to a stall.'], 403);
        }
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search');

        $query = Billing::with('payment')
            ->join('payments', 'billing.id', '=', 'payments.billing_id')
            ->where('billing.stall_id', $vendor->stall->id)
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

    public function paymentYearsApi()
    {
        $vendor = Auth::user();
        if (!$vendor->stall) {
            return response()->json(['error' => 'User not assigned to a stall.'], 403);
        }

        $years = Billing::join('payments', 'billing.id', '=', 'payments.billing_id')
            ->where('billing.stall_id', $vendor->stall->id)
            ->select(DB::raw('EXTRACT(YEAR FROM payments.payment_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years->push(now()->year);
        }

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
}