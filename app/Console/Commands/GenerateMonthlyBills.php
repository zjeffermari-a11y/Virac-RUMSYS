<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Stall;
use App\Models\Rate;
use App\Models\Billing;
use App\Models\UtilityReading;
use Carbon\Carbon;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'billing:generate {date? : The date to generate bills for (YYYY-MM-DD)}';
    protected $description = 'Generates monthly bills for all stalls.';

    public function handle()
    {
        $dateInput = $this->argument('date');
        $today = $dateInput ? Carbon::parse($dateInput) : Carbon::today();

        $this->info("Starting monthly bill generation for date: {$today->toDateString()}...");
        $rentPeriodStart = $today->copy()->startOfMonth();
        $rentPeriodEnd = $today->copy()->endOfMonth();
        $utilityPeriodStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $utilityPeriodEnd = $today->copy()->subMonthNoOverflow()->endOfMonth();
        
        // FIX: Make sure we get the actual days in the utility month (e.g., 31 for August)
        $daysInUtilityMonth = $utilityPeriodEnd->day; // This gets the last day number = total days

        // FIX: Explicitly get the daily rate (not monthly_rate)
        $waterRate = Rate::where('utility_type', 'Water')->value('rate'); // This should be 5.00
        $electricityRate = Rate::where('utility_type', 'Electricity')->value('rate');
        
        $schedules = DB::table('schedules')->get()->keyBy('schedule_type');
        $stalls = Stall::whereHas('vendor')->with('vendor', 'section')->get();
        $daysInRentMonth = $rentPeriodStart->daysInMonth;

        $this->info("Utility period: {$utilityPeriodStart->toDateString()} to {$utilityPeriodEnd->toDateString()}");
        $this->info("Days in utility month: {$daysInUtilityMonth}");
        $this->info("Water rate per day: ₱{$waterRate}");

        foreach ($stalls as $stall) {
            $this->info("Processing bills for Stall: {$stall->table_number}");

            // --- Rent Bill (Period: Current Month, e.g., September) ---
            Billing::updateOrCreate(
                [
                    'stall_id' => $stall->id,
                    'utility_type' => 'Rent',
                    'period_start' => $rentPeriodStart->toDateString(),
                ],
                [
                    'period_end' => $rentPeriodEnd->toDateString(), 
                    'amount' => $stall->monthly_rate,
                    'due_date' => $this->getDueDate('Rent', $rentPeriodStart, $schedules),
                    'disconnection_date' => $this->getDisconnectionDate('Rent', $rentPeriodStart, $schedules),
                    'status' => 'unpaid'
                ]
            );

            // --- Water Bill (Period: Previous Month, e.g., August) ---
            if ($waterRate && $stall->section && strcasecmp($stall->section->name, 'Wet Section') == 0) {
                // FIX: Calculate water amount using actual days in the utility month
                $waterAmount = $daysInUtilityMonth * $waterRate;
                
                $this->info("  Water bill: {$daysInUtilityMonth} days × ₱{$waterRate} = ₱{$waterAmount}");
                
                Billing::updateOrCreate(
                    [
                        'stall_id' => $stall->id,
                        'utility_type' => 'Water',
                        'period_start' => $utilityPeriodStart->toDateString(),
                    ],
                    [
                        'period_end' => $utilityPeriodEnd->toDateString(), 
                        'amount' => $waterAmount, // FIX: Use calculated amount
                        'due_date' => $this->getDueDate('Water', $rentPeriodStart, $schedules),
                        'disconnection_date' => $this->getDisconnectionDate('Water', $rentPeriodStart, $schedules),
                        'status' => 'unpaid'
                    ]
                );
            }

            // --- Electricity Bill (Period: Previous Month, e.g., August) ---
            if ($electricityRate) {
                $latestReading = UtilityReading::where('stall_id', $stall->id)
                    ->whereYear('reading_date', $utilityPeriodStart->year)
                    ->whereMonth('reading_date', $utilityPeriodStart->month)
                    ->first();

                if ($latestReading) {
                    // Bill with actual consumption
                    $consumption = $latestReading->current_reading - $latestReading->previous_reading;
                    Billing::updateOrCreate(
                        [
                            'stall_id' => $stall->id,
                            'utility_type' => 'Electricity',
                            'period_start' => $utilityPeriodStart->toDateString(),
                        ],
                        [
                            'period_end' => $utilityPeriodEnd->toDateString(),
                            'amount' => $consumption * $electricityRate,
                            'previous_reading' => $latestReading->previous_reading,
                            'current_reading' => $latestReading->current_reading,
                            'consumption' => $consumption, 
                            'rate' => $electricityRate,
                            'due_date' => $this->getDueDate('Electricity', $rentPeriodStart, $schedules),
                            'disconnection_date' => $this->getDisconnectionDate('Electricity', $rentPeriodStart, $schedules),
                            'status' => 'unpaid'
                        ]
                    );
                    $this->info("  Electricity bill: {$consumption} kWh × ₱{$electricityRate} = ₱" . ($consumption * $electricityRate));
                } else {
                    // Create placeholder bill even if no reading exists (amount = 0)
                    // This ensures electricity bills always appear in outstanding balance
                    Billing::updateOrCreate(
                        [
                            'stall_id' => $stall->id,
                            'utility_type' => 'Electricity',
                            'period_start' => $utilityPeriodStart->toDateString(),
                        ],
                        [
                            'period_end' => $utilityPeriodEnd->toDateString(),
                            'amount' => 0,
                            'previous_reading' => 0,
                            'current_reading' => 0,
                            'consumption' => 0, 
                            'rate' => $electricityRate,
                            'due_date' => $this->getDueDate('Electricity', $rentPeriodStart, $schedules),
                            'disconnection_date' => $this->getDisconnectionDate('Electricity', $rentPeriodStart, $schedules),
                            'status' => 'unpaid'
                        ]
                    );
                    $this->info("  Electricity bill: Placeholder created (no reading yet)");
                }
            }
        }
        $this->info('Monthly bill generation completed successfully!');
        return 0;
    }

    private function getDueDate($type, Carbon $billingPeriod, $schedules)
    {
        if ($type === 'Rent') { 
            return $billingPeriod->copy()->endOfMonth()->toDateString(); 
        }
        $key = "Due Date - {$type}";
        $day = $schedules->get($key)->description ?? null;
        return is_numeric($day) ? $billingPeriod->copy()->day((int)$day)->toDateString() : null;
    }

    private function getDisconnectionDate($type, Carbon $billingPeriod, $schedules)
    {
        $key = "Disconnection - {$type}";
        $day = $schedules->get($key)->description ?? null;
        return is_numeric($day) ? $billingPeriod->copy()->day((int)$day)->toDateString() : null;
    }
}