<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupBillingData extends Command
{
    protected $signature = 'billing:cleanup-current';
    protected $description = 'Deletes billing and payment records for the current month to allow for regeneration.';

    public function handle()
    {
        if ($this->confirm('Are you sure you want to delete all billing and payment records for the CURRENT month? This is necessary to regenerate them with the correct calculations.')) {

            $this->info('Starting cleanup for ' . Carbon::now()->format('F Y') . '...');

            $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();

            // Find billing IDs for the current month
            $billingIds = DB::table('billing')
                            ->where('period_start', '>=', $currentMonthStart)
                            ->pluck('id');

            if ($billingIds->isEmpty()) {
                $this->info('No billing records found for the current month. Nothing to delete.');
                return 0;
            }

            // Disable foreign key checks to delete records safely
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete payments associated with those bills
            $deletedPayments = DB::table('payments')->whereIn('billing_id', $billingIds)->delete();
            $this->info("Deleted {$deletedPayments} payment record(s) for the current month.");

            // Delete the billing records themselves
            $deletedBillings = DB::table('billing')->whereIn('id', $billingIds)->delete();
            $this->info("Deleted {$deletedBillings} billing record(s) for the current month.");

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('Cleanup for the current month is complete. You can now run the billing generator.');
        } else {
            $this->info('Cleanup cancelled.');
        }
        return 0;
    }
}