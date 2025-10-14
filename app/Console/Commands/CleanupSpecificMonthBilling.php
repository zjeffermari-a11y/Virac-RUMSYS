<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Billing;
use Carbon\Carbon;

class CleanupSpecificMonthBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:cleanup-specific {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all billing and payment records for a specific billing month (format: YYYY-MM).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monthInput = $this->argument('month');

        try {
            // Target month for deletion (e.g., 2025-11)
            $targetBillingMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use YYYY-MM (e.g., 2025-11).');
            return 1;
        }

        $monthName = $targetBillingMonth->format('F Y');

        if (!$this->confirm("Are you sure you want to PERMANENTLY DELETE all billing and payment data for the billing month of {$monthName}? This action cannot be undone.")) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        $this->info("Starting cleanup for {$monthName}...");

        // --- START OF FIX ---
        // Find all potentially relevant bills (from the previous and target month)
        $potentialBills = Billing::whereMonth('period_start', $targetBillingMonth->month)
                                 ->orWhereMonth('period_start', $targetBillingMonth->copy()->subMonth()->month)
                                 ->get();

        $billingIdsToDelete = $potentialBills->filter(function ($bill) use ($targetBillingMonth) {
            $periodDate = Carbon::parse($bill->period_start);
            
            // Determine the "Billing Month" for the bill
            $billBelongsToMonth = $periodDate;
            if (in_array($bill->utility_type, ['Water', 'Electricity'])) {
                $billBelongsToMonth = $periodDate->addMonth();
            }

            // Check if the bill's "Billing Month" matches the month we want to delete
            return $billBelongsToMonth->isSameMonth($targetBillingMonth);
        })->pluck('id');
        // --- END OF FIX ---


        if ($billingIdsToDelete->isEmpty()) {
            $this->info("No billing records found for {$monthName}. Nothing to delete.");
            return 0;
        }

        try {
            DB::transaction(function () use ($billingIdsToDelete, $monthName) {
                // Delete payments associated with those bills
                $deletedPayments = DB::table('payments')->whereIn('billing_id', $billingIdsToDelete)->delete();
                $this->info("Deleted {$deletedPayments} payment record(s) for {$monthName}.");

                // Delete the billing records themselves
                $deletedBillings = DB::table('billing')->whereIn('id', $billingIdsToDelete)->delete();
                $this->info("Deleted {$deletedBillings} billing record(s) for {$monthName}.");
            });

            $this->info("Cleanup for {$monthName} is complete.");

        } catch (\Exception $e) {
            $this->error('An error occurred during the database transaction: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}