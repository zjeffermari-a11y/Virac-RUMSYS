<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckPaymentStatus extends Command
{
    protected $signature = 'payments:check-status {--month= : Check specific month (YYYY-MM)}';
    protected $description = 'Check payment status and outstanding balance logic';

    public function handle()
    {
        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();
        
        $checkMonth = $this->option('month');
        if ($checkMonth) {
            $checkDate = Carbon::createFromFormat('Y-m', $checkMonth);
            $checkMonthStart = $checkDate->copy()->startOfMonth();
            $checkMonthEnd = $checkDate->copy()->endOfMonth();
        } else {
            $checkMonthStart = $currentMonthStart;
            $checkMonthEnd = $currentMonthEnd;
        }

        $this->info("=== Payment Status Check ===");
        $this->info("Current Date: " . $today->format('Y-m-d'));
        $this->info("Current Month: " . $currentMonthStart->format('F Y'));
        $this->info("Checking Month: " . $checkMonthStart->format('F Y'));
        $this->line("");

        // Check all paid bills with payments
        $this->info("1. All Paid Bills with Payment Dates:");
        $this->line("-----------------------------------");
        
        $paidBills = DB::table('billing')
            ->join('payments', 'billing.id', '=', 'payments.billing_id')
            ->select(
                'billing.id',
                'billing.stall_id',
                'billing.utility_type',
                'billing.period_start',
                'billing.period_end',
                'billing.status',
                'billing.amount',
                'payments.payment_date',
                'payments.amount_paid'
            )
            ->where('billing.status', 'paid')
            ->orderBy('payments.payment_date', 'desc')
            ->get();

        if ($paidBills->isEmpty()) {
            $this->warn("No paid bills found.");
        } else {
            $headers = ['ID', 'Stall', 'Type', 'Period', 'Status', 'Amount', 'Payment Date', 'Should be in Outstanding?'];
            $rows = [];
            
            foreach ($paidBills as $bill) {
                $paymentDate = Carbon::parse($bill->payment_date);
                $isCurrentMonth = $paymentDate->isSameMonth($currentMonthStart);
                $shouldBeOutstanding = $isCurrentMonth ? 'YES (Current Month)' : 'NO (Previous Month)';
                
                $rows[] = [
                    $bill->id,
                    $bill->stall_id,
                    $bill->utility_type,
                    Carbon::parse($bill->period_start)->format('M d') . ' - ' . Carbon::parse($bill->period_end)->format('M d, Y'),
                    $bill->status,
                    '₱' . number_format($bill->amount, 2),
                    $paymentDate->format('M d, Y'),
                    $shouldBeOutstanding
                ];
            }
            
            $this->table($headers, $rows);
        }

        $this->line("");

        // Check bills that should be in outstanding balance
        $this->info("2. Bills That SHOULD Be in Outstanding Balance:");
        $this->line("-----------------------------------");
        
        $outstandingBills = DB::table('billing')
            ->leftJoin('payments', 'billing.id', '=', 'payments.billing_id')
            ->select(
                'billing.id',
                'billing.stall_id',
                'billing.utility_type',
                'billing.period_start',
                'billing.period_end',
                'billing.status',
                'billing.amount',
                'payments.payment_date',
                'payments.amount_paid'
            )
            ->where(function($query) use ($currentMonthStart, $currentMonthEnd) {
                $query->where('billing.status', 'unpaid')
                    ->orWhere(function($q) use ($currentMonthStart, $currentMonthEnd) {
                        $q->where('billing.status', 'paid')
                            ->whereBetween('payments.payment_date', [
                                $currentMonthStart->toDateString(),
                                $currentMonthEnd->toDateString()
                            ]);
                    });
            })
            ->orderBy('billing.due_date', 'desc')
            ->get();

        if ($outstandingBills->isEmpty()) {
            $this->info("No bills in outstanding balance.");
        } else {
            $headers = ['ID', 'Stall', 'Type', 'Period', 'Status', 'Amount', 'Payment Date', 'Reason'];
            $rows = [];
            
            foreach ($outstandingBills as $bill) {
                $reason = $bill->status === 'unpaid' 
                    ? 'Unpaid' 
                    : 'Paid in Current Month';
                
                $paymentDate = $bill->payment_date 
                    ? Carbon::parse($bill->payment_date)->format('M d, Y')
                    : 'N/A';
                
                $rows[] = [
                    $bill->id,
                    $bill->stall_id,
                    $bill->utility_type,
                    Carbon::parse($bill->period_start)->format('M d') . ' - ' . Carbon::parse($bill->period_end)->format('M d, Y'),
                    $bill->status,
                    '₱' . number_format($bill->amount, 2),
                    $paymentDate,
                    $reason
                ];
            }
            
            $this->table($headers, $rows);
        }

        $this->line("");

        // Check for October payments specifically
        $this->info("3. October 2025 Payments (Should NOT be in Outstanding Balance):");
        $this->line("-----------------------------------");
        
        $octoberStart = Carbon::create(2025, 10, 1)->startOfMonth();
        $octoberEnd = Carbon::create(2025, 10, 31)->endOfMonth();
        
        $octoberPayments = DB::table('billing')
            ->join('payments', 'billing.id', '=', 'payments.billing_id')
            ->select(
                'billing.id',
                'billing.stall_id',
                'billing.utility_type',
                'billing.period_start',
                'billing.period_end',
                'billing.status',
                'billing.amount',
                'payments.payment_date',
                'payments.amount_paid'
            )
            ->where('billing.status', 'paid')
            ->whereBetween('payments.payment_date', [
                $octoberStart->toDateString(),
                $octoberEnd->toDateString()
            ])
            ->orderBy('payments.payment_date', 'desc')
            ->get();

        if ($octoberPayments->isEmpty()) {
            $this->info("No October 2025 payments found.");
        } else {
            $this->warn("Found " . $octoberPayments->count() . " October 2025 payment(s). These should NOT appear in outstanding balance.");
            $headers = ['ID', 'Stall', 'Type', 'Period', 'Status', 'Amount', 'Payment Date'];
            $rows = [];
            
            foreach ($octoberPayments as $bill) {
                $rows[] = [
                    $bill->id,
                    $bill->stall_id,
                    $bill->utility_type,
                    Carbon::parse($bill->period_start)->format('M d') . ' - ' . Carbon::parse($bill->period_end)->format('M d, Y'),
                    $bill->status,
                    '₱' . number_format($bill->amount, 2),
                    Carbon::parse($bill->payment_date)->format('M d, Y')
                ];
            }
            
            $this->table($headers, $rows);
        }

        $this->line("");
        $this->info("=== Summary ===");
        $this->info("Current Month: " . $currentMonthStart->format('F Y'));
        $this->info("Bills in Outstanding Balance: " . $outstandingBills->count());
        $this->info("October 2025 Payments Found: " . $octoberPayments->count());
        
        if ($octoberPayments->isNotEmpty() && $currentMonthStart->month > 10) {
            $this->warn("⚠️  October payments should NOT be in outstanding balance if current month is after October.");
            $this->line("");
            $this->info("Checking if any October payments are incorrectly in outstanding balance query...");
            
            // Get October payment IDs
            $octoberPaymentIds = $octoberPayments->pluck('id')->toArray();
            
            // Check if any October payments match the outstanding balance criteria
            $octoberInOutstanding = $outstandingBills->filter(function($bill) use ($octoberStart, $octoberEnd, $octoberPaymentIds) {
                if (!$bill->payment_date) return false;
                $paymentDate = Carbon::parse($bill->payment_date);
                $isOctober = $paymentDate->between($octoberStart, $octoberEnd);
                $isInOctoberList = in_array($bill->id, $octoberPaymentIds);
                return $isOctober || $isInOctoberList;
            });
            
            if ($octoberInOutstanding->isNotEmpty()) {
                $this->error("❌ FOUND " . $octoberInOutstanding->count() . " October payment(s) incorrectly in outstanding balance!");
                $this->table(
                    ['ID', 'Stall', 'Type', 'Status', 'Payment Date', 'Current Month Start', 'Issue'],
                    $octoberInOutstanding->map(function($bill) use ($currentMonthStart) {
                        return [
                            $bill->id,
                            $bill->stall_id,
                            $bill->utility_type,
                            $bill->status,
                            $bill->payment_date ? Carbon::parse($bill->payment_date)->format('M d, Y') : 'N/A',
                            $currentMonthStart->format('M d, Y'),
                            'Should NOT be in outstanding balance'
                        ];
                    })->toArray()
                );
                $this->line("");
                $this->error("This indicates the query filter is NOT working correctly!");
            } else {
                $this->info("✓ Good: No October payments found in outstanding balance query.");
                $this->info("The query filter is working correctly. If you still see October payments in the UI, it may be:");
                $this->line("  1. Browser/application cache - try clearing cache");
                $this->line("  2. Code not deployed yet - check if latest code is on server");
                $this->line("  3. Different endpoint being used - check network tab in browser");
            }
        }

        return 0;
    }
}
