<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UtilityReading;
use App\Models\ReadingEditRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClearCurrentMonthReadings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'readings:clear-current-month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all readings and associated edit requests for the current month.';

    /**
     * Execute the console command.
     */
// In app/Console/Commands/ClearCurrentMonthReadings.php

public function handle()
{
    if (!$this->confirm('Are you sure you want to PERMANENTLY DELETE all meter readings and edit requests for the CURRENT BILLING PERIOD (LAST MONTH)? This cannot be undone.')) {
        $this->info('Operation cancelled.');
        return 1;
    }

    $this->info('Finding and deleting data for the previous month...');

    // CORRECTED: Target the previous month to match the controller's logic
    $billingPeriodMonth = Carbon::today()->subMonthNoOverflow();

    $readingIds = UtilityReading::whereYear('reading_date', $billingPeriodMonth->year)
        ->whereMonth('reading_date', $billingPeriodMonth->month)
        ->pluck('id');

    if ($readingIds->isEmpty()) {
        $this->warn('No readings found for the previous month to delete.');
        return 0;
    }

    // Initialize variables before the try block
    $deletedRequestsCount = 0;
    $deletedReadingsCount = 0;

    try {
        DB::transaction(function () use ($readingIds, &$deletedRequestsCount, &$deletedReadingsCount) {
            // Delete associated edit requests first
            $deletedRequestsCount = ReadingEditRequest::whereIn('reading_id', $readingIds)->delete();

            // Then, delete the readings themselves
            $deletedReadingsCount = UtilityReading::whereIn('id', $readingIds)->delete();
        });

        $this->info("Successfully deleted {$deletedRequestsCount} edit request(s).");
        $this->info("Successfully deleted {$deletedReadingsCount} meter reading(s) for {$billingPeriodMonth->format('F Y')}.");
        $this->info('The data has been cleared. The Meter Reader can now log in to generate fresh records.');

    } catch (\Exception $e) {
        $this->error('An error occurred during deletion: ' . $e->getMessage());
        return 1;
    }

    return 0;
}
}