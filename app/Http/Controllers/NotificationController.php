<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\BillingSetting;
use Illuminate\Support\Facades\Auth;

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
                $bill->original_amount = $bill->amount;
                $originalDueDate = Carbon::parse($bill->due_date);
                $settings = $billingSettings->get($bill->utility_type);
    
                $projected_amount = $bill->original_amount;
                if ($settings) {
                    if ($bill->utility_type === 'Rent') {
                        $projected_amount += $bill->original_amount * ($settings->surcharge_rate ?? 0);
                        $projected_amount += $bill->original_amount * ($settings->monthly_interest_rate ?? 0);
                    } else {
                        $projected_amount += $bill->original_amount * ($settings->penalty_rate ?? 0);
                    }
                }
                $bill->amount_after_due = $projected_amount;
    
                if ($bill->status === 'paid') {
                    $bill->current_amount_due = $bill->original_amount;
                } else {
                    if ($today->gt($originalDueDate)) {
                        $current_total_due = $bill->original_amount;
                        if ($bill->utility_type === 'Rent' && $settings) {
                            $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                            $surcharge = $bill->original_amount * ($settings->surcharge_rate ?? 0);
                            $interest = $bill->original_amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
                            $current_total_due += $surcharge + $interest;
                        } else if ($settings) {
                            $current_total_due += $bill->original_amount * ($settings->penalty_rate ?? 0);
                        }
                        $bill->current_amount_due = $current_total_due;
                    } else {
                        $bill->current_amount_due = $bill->original_amount;
                    }
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
            $u->currentBills = $currentBills;
            $u->totalAmountDue = $currentBills->sum('amount_after_due');
            $u->statementMonth = $month;
            $u->dueDate = $targetDate->endOfMonth()->format('F d, Y');
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
                $bill->original_amount = $bill->amount;
                $originalDueDate = Carbon::parse($bill->due_date);
                $settings = $billingSettings->get($bill->utility_type);

                $projected_amount = $bill->original_amount;
                if ($settings) {
                    if ($bill->utility_type === 'Rent') {
                        $projected_amount += $bill->original_amount * ($settings->surcharge_rate ?? 0);
                        $projected_amount += $bill->original_amount * ($settings->monthly_interest_rate ?? 0);
                    } else {
                        $projected_amount += $bill->original_amount * ($settings->penalty_rate ?? 0);
                    }
                }
                $bill->amount_after_due = $projected_amount;

                if ($today->gt($originalDueDate)) {
                    $current_total_due = $bill->original_amount;
                     if ($bill->utility_type === 'Rent' && $settings) {
                        $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                        $surcharge = $bill->original_amount * ($settings->surcharge_rate ?? 0);
                        $interest = $bill->original_amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
                        $current_total_due += $surcharge + $interest;
                    } else if ($settings) {
                         $current_total_due += $bill->original_amount * ($settings->penalty_rate ?? 0);
                    }
                    $bill->current_amount_due = $current_total_due;
                } else {
                    $bill->current_amount_due = $bill->original_amount;
                }
            }
            
            $user->currentBills = $currentBills;
            $user->totalAmountDue = $currentBills->sum('amount_after_due');
            $user->statementMonth = $statementMonth;
            $user->dueDate = $today->endOfMonth()->format('F d, Y');
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