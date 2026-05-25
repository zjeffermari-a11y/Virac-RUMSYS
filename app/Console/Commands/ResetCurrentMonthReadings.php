<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UtilityReading;
use Carbon\Carbon;

class ResetCurrentMonthReadings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'readings:reset-current-month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the current_reading for all utility readings in the current month to 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding and resetting readings for the current month...');

        $currentMonth = Carbon::today();

        // Find all readings for the current month and year and update them
        $updatedCount = UtilityReading::whereYear('reading_date', $currentMonth->year)
            ->whereMonth('reading_date', $currentMonth->month)
            ->update(['current_reading' => 0]);

        if ($updatedCount > 0) {
            $this->info("Successfully reset {$updatedCount} meter readings for {$currentMonth->format('F Y')}.");
        } else {
            $this->warn('No readings found for the current month to reset.');
        }

        return 0;
    }
}