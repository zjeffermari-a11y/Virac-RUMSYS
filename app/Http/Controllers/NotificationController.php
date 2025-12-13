<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\BillingSetting;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;

class NotificationController extends Controller
{
    public function print(User $user, $month)
    {   
        $users = collect([$user]);

        $targetDate = Carbon::createFromFormat('F Y', $month);
        $billingSettings = BillingSetting::all()->keyBy('utility_type');
        $today = Carbon::today();

        foreach ($users as $u) {
            $allBillsForPeriod = $u->billings()
                ->where('status', 'unpaid')
                ->with('payment')
                ->get();
    
            foreach ($allBillsForPeriod as $bill) {
                $bill->original_amount = (float) $bill->amount;
                $originalDueDate = Carbon::parse($bill->due_date);
                $settings = $billingSettings->get($bill->utility_type);
    
                // Initialize calculation properties (same as outstanding balance)
                $bill->interest_months = 0;
                $bill->discount_applied = 0;
                $bill->penalty_applied = 0;
                $bill->display_amount_due = $bill->original_amount;
                $bill->amount_after_due = $bill->original_amount;
    
                if ($bill->status === 'paid') {
                    $paid_amount = (float) (optional($bill->payment)->amount_paid ?? $bill->original_amount);
                    $bill->display_amount_due = $paid_amount;
                    $bill->current_amount_due = $paid_amount;
                    $bill->amount_after_due = $paid_amount;
                } else {
                    if ($today->gt($originalDueDate)) {
                        // Bill is OVERDUE - calculate penalties/interest (same as outstanding balance)
                        if ($bill->utility_type === 'Rent' && $settings) {
                            $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                            $surcharge = $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                            $interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                            
                            $bill->interest_months = $interest_months;
                            $bill->penalty_applied = $surcharge + $interest;
                            $bill->display_amount_due = $bill->original_amount + $surcharge + $interest;
                            $bill->current_amount_due = $bill->display_amount_due;
                        } else if ($settings) {
                            // For utilities (Water, Electricity)
                            $penalty = $bill->original_amount * (float)($settings->penalty_rate ?? 0);
                            $bill->penalty_applied = $penalty;
                            $bill->display_amount_due = $bill->original_amount + $penalty;
                            $bill->current_amount_due = $bill->display_amount_due;
                        } else {
                            $bill->current_amount_due = $bill->original_amount;
                        }
                    } else {
                        // Bill is NOT YET OVERDUE
                        $bill->display_amount_due = $bill->original_amount;
                        $bill->current_amount_due = $bill->original_amount;
                        
                        // Check for early payment discount
                        $todayDay = $today->day;
                        $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                        $currentMonth = $today->format('Y-m');
                        
                        if ($todayDay <= 15 && $billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                            $bill->display_amount_due = $bill->original_amount - ($bill->original_amount * (float)$settings->discount_rate);
                            $bill->discount_applied = $bill->original_amount * (float)$settings->discount_rate;
                            $bill->current_amount_due = $bill->display_amount_due;
                        }
                    }
                    
                    // Calculate projected amount after due date (for amount_after_due)
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
                }
            }
    
            list($currentBills, $previousBills) = $allBillsForPeriod->partition(function ($bill) use ($targetDate) {
                $periodDate = Carbon::parse($bill->period_start);
                if (in_array($bill->utility_type, ['Water', 'Electricity'])) {
                    return $periodDate->addMonth()->format('F Y') === $targetDate->format('F Y');
                }
                return $periodDate->format('F Y') === $targetDate->format('F Y');
            });

            // Attach the processed data directly to the user object for the view
            // Use display_amount_due (same as outstanding balance calculation)
            $u->currentBills = $currentBills;
            $u->totalAmountDue = $currentBills->sum('display_amount_due');
            $u->statementMonth = $month;
            $u->dueDate = $targetDate->endOfMonth()->format('F d, Y');
        }

        // Log print action
        if (Auth::check()) {
            AuditLogger::log(
                'Printed Billing Statement',
                'Reports',
                'Success',
                ['user_id' => $user->id, 'user_name' => $user->name, 'month' => $month]
            );
        }

        return view('printing.print', [
            'users' => $users, // Pass the collection of users
            'billingSettings' => $billingSettings,
        ]);
    }

    public function bulkPrint(Request $request)
    {
        $userIds = explode(',', $request->query('users'));
        $users = User::with(['billings' => function ($query) {
            $query->where('status', 'unpaid')->orderBy('period_start');
        }, 'stall.section'])->find($userIds);

        $billingSettings = BillingSetting::all()->keyBy('utility_type');
        $today = Carbon::today();
        $statementMonth = $today->format('F Y');
        $targetDate = Carbon::createFromFormat('F Y', $statementMonth);

        foreach ($users as $user) {
            
            list($currentBills, $previousBills) = $user->billings->partition(function ($bill) use ($targetDate) {
                $periodDate = Carbon::parse($bill->period_start);
                if (in_array($bill->utility_type, ['Water', 'Electricity'])) {
                    return $periodDate->addMonth()->format('F Y') === $targetDate->format('F Y');
                }
                return $periodDate->format('F Y') === $targetDate->format('F Y');
            });

            foreach ($currentBills as $bill) {
                $bill->original_amount = (float) $bill->amount;
                $originalDueDate = Carbon::parse($bill->due_date);
                $settings = $billingSettings->get($bill->utility_type);

                // Initialize calculation properties (same as outstanding balance)
                $bill->interest_months = 0;
                $bill->discount_applied = 0;
                $bill->penalty_applied = 0;
                $bill->display_amount_due = $bill->original_amount;
                $bill->amount_after_due = $bill->original_amount;

                if ($bill->status === 'paid') {
                    $paid_amount = (float) (optional($bill->payment)->amount_paid ?? $bill->original_amount);
                    $bill->display_amount_due = $paid_amount;
                    $bill->current_amount_due = $paid_amount;
                    $bill->amount_after_due = $paid_amount;
                } else {
                    if ($today->gt($originalDueDate)) {
                        // Bill is OVERDUE - calculate penalties/interest (same as outstanding balance)
                        if ($bill->utility_type === 'Rent' && $settings) {
                            $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                            $surcharge = $bill->original_amount * (float)($settings->surcharge_rate ?? 0);
                            $interest = $bill->original_amount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                            
                            $bill->interest_months = $interest_months;
                            $bill->penalty_applied = $surcharge + $interest;
                            $bill->display_amount_due = $bill->original_amount + $surcharge + $interest;
                            $bill->current_amount_due = $bill->display_amount_due;
                        } else if ($settings) {
                            // For utilities (Water, Electricity)
                            $penalty = $bill->original_amount * (float)($settings->penalty_rate ?? 0);
                            $bill->penalty_applied = $penalty;
                            $bill->display_amount_due = $bill->original_amount + $penalty;
                            $bill->current_amount_due = $bill->display_amount_due;
                        } else {
                            $bill->current_amount_due = $bill->original_amount;
                        }
                    } else {
                        // Bill is NOT YET OVERDUE
                        $bill->display_amount_due = $bill->original_amount;
                        $bill->current_amount_due = $bill->original_amount;
                        
                        // Check for early payment discount
                        $todayDay = $today->day;
                        $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
                        $currentMonth = $today->format('Y-m');
                        
                        if ($todayDay <= 15 && $billMonth === $currentMonth && $bill->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                            $bill->display_amount_due = $bill->original_amount - ($bill->original_amount * (float)$settings->discount_rate);
                            $bill->discount_applied = $bill->original_amount * (float)$settings->discount_rate;
                            $bill->current_amount_due = $bill->display_amount_due;
                        }
                    }
                    
                    // Calculate projected amount after due date (for amount_after_due)
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
                }
            }
            
            // Use display_amount_due (same as outstanding balance calculation)
            $user->currentBills = $currentBills;
            $user->totalAmountDue = $currentBills->sum('display_amount_due');
            $user->statementMonth = $statementMonth;
            $user->dueDate = $today->endOfMonth()->format('F d, Y');
        }

        // Log bulk print action
        if (Auth::check()) {
            $userNames = $users->pluck('name')->toArray();
            $userIds = $users->pluck('id')->toArray();
            AuditLogger::log(
                'Bulk Printed Billing Statements',
                'Reports',
                'Success',
                ['user_count' => count($users), 'user_ids' => $userIds, 'user_names' => $userNames, 'month' => $statementMonth]
            );
        }

        return view('printing.print', [
            'users' => $users,
            'billingSettings' => $billingSettings,
        ]);
    }
    public function fetch(Request $request)
    {
        $user = Auth::user();

        $notifications = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('channel', 'in_app')
            ->orderBy('created_at', 'desc')
            ->limit(10) // Let's limit it to the 10 most recent
            ->get();

        $unreadCount = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('channel', 'in_app')
            ->whereNull('read_at')
            ->count();

        return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Fetch all notifications for the current user (for notifications page)
     */
    public function fetchAll(Request $request)
    {
        $user = Auth::user();

        // Optimize: Use single query with subquery for unread count
        $unreadCount = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('channel', 'in_app')
            ->whereNull('read_at')
            ->count();

        // Optimize: Use select with COALESCE for sender name to avoid leftJoin overhead
        $notifications = DB::table('notifications')
            ->leftJoin('users as senders', 'notifications.sender_id', '=', 'senders.id')
            ->where('notifications.recipient_id', $user->id)
            ->where('notifications.channel', 'in_app')
            ->select(
                'notifications.id',
                'notifications.title',
                'notifications.message',
                'notifications.status',
                'notifications.read_at',
                'notifications.created_at',
                'notifications.sent_at',
                DB::raw('COALESCE(senders.name, NULL) as sender_name')
            )
            ->orderBy('notifications.created_at', 'desc')
            ->limit(100) // Limit to most recent 100 notifications for performance
            ->get();

        return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all pending notifications for the user as 'read'.
     */
    public function markAsRead(Request $request)
    {
        $user = Auth::user();

        DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $user = Auth::user();

        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated) {
            return response()->json(['message' => 'Notification marked as read.']);
        }

        return response()->json(['message' => 'Notification not found or already read.'], 404);
    }
}