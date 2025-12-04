<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Stall;
use App\Models\Rate;
use App\Models\Billing;
use App\Models\UtilityReading;
use Carbon\Carbon;

class GenerateNewVendorBills extends Command
{
    protected $signature = 'billing:generate-new-vendor {stall_id?}';
    protected $description = 'Generates the first set of bills for newly assigned vendors.';

    public function handle()
    {
        $stallId = $this->argument('stall_id');
        
        if ($stallId) {
            return $this->generateBillsForStall($stallId);
        } else {
            return $this->generateBillsForNewVendors();
        }
    }

    private function generateBillsForNewVendors()
    {
        $this->info('🔍 Scanning for newly assigned vendors without bills...');
        
        $today = Carbon::today();
        $currentMonth = $today->copy()->startOfMonth();
        
        $stallsWithoutBills = Stall::whereHas('vendor')
            ->whereNotExists(function ($query) use ($currentMonth) {
                $query->select(DB::raw(1))
                    ->from('billing')
                    ->whereColumn('billing.stall_id', 'stalls.id')
                    ->where('billing.utility_type', 'Rent')
                    ->where('billing.period_start', $currentMonth->toDateString());
            })
            ->with('vendor', 'section')
            ->get();

        if ($stallsWithoutBills->isEmpty()) {
            $this->info('✓ No new vendors found. All assigned vendors have bills for the current month.');
            return 0;
        }

        $this->info("Found {$stallsWithoutBills->count()} new vendor(s) without bills:");
        $this->table(
            ['Stall ID', 'Table Number', 'Section', 'Vendor Name'],
            $stallsWithoutBills->map(function ($stall) {
                return [
                    $stall->id,
                    $stall->table_number,
                    $stall->section->name ?? 'N/A',
                    $stall->vendor->name ?? 'N/A'
                ];
            })
        );

        foreach ($stallsWithoutBills as $stall) {
            $this->info("\n📝 Processing Stall: {$stall->table_number} ({$stall->section->name})");
            $this->processSingleStall($stall);
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("✓ Bill generation complete.");
        $this->info(str_repeat('=', 50));

        return 0;
    }

    private function generateBillsForStall($stallId)
    {
        $stall = Stall::whereHas('vendor')->with('vendor', 'section')->find($stallId);

        if (!$stall) {
            $this->error("Stall ID {$stallId} not found or has no assigned vendor.");
            return 1;
        }

        $this->info("Generating bills for Stall: {$stall->table_number}");
        $this->processSingleStall($stall);
        $this->info("Bill generation completed for Stall {$stall->table_number}!");
        
        return 0;
    }

    private function processSingleStall($stall)
    {
        $today = Carbon::today();
        
        // Periods for billing
        $rentPeriodStart = $today->copy()->startOfMonth();
        $rentPeriodEnd = $today->copy()->endOfMonth();
        $utilityPeriodStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $utilityPeriodEnd = $today->copy()->subMonthNoOverflow()->endOfMonth();
        
        // ✅ Get the number of days in the *previous* month for the water calculation.
        $daysInUtilityMonth = $utilityPeriodStart->daysInMonth;

        // Get rates and schedules
        $waterRate = Rate::where('utility_type', 'Water')->value('rate');
        $electricityRate = Rate::where('utility_type', 'Electricity')->value('rate');
        $schedules = DB::table('schedules')->get()->keyBy('schedule_type');

        // --- 1. Rent Bill (Current Month) ---
        Billing::updateOrCreate(
            ['stall_id' => $stall->id, 'utility_type' => 'Rent', 'period_start' => $rentPeriodStart->toDateString()],
            [
                'period_end' => $rentPeriodEnd->toDateString(), 
                'amount' => $stall->monthly_rate ?? 0, // Default to 0 if null
                'due_date' => $this->getDueDate('Rent', $rentPeriodStart, $schedules), 
                'disconnection_date' => $this->getDisconnectionDate('Rent', $rentPeriodStart, $schedules), 
                'status' => 'unpaid'
            ]
        );
        $this->info("  ✓ Rent bill for current month processed.");

        // --- 2. Water Bill (Previous Month, Calculated Amount) ---
        if ($waterRate && $stall->section && strcasecmp($stall->section->name, 'Wet Section') == 0) { //
            Billing::updateOrCreate(
                ['stall_id' => $stall->id, 'utility_type' => 'Water', 'period_start' => $utilityPeriodStart->toDateString()],
                [
                    'period_end' => $utilityPeriodEnd->toDateString(), 
                    // ✅ FIX: Calculate the amount based on days in the previous month.
                    'amount' => $daysInUtilityMonth * $waterRate, 
                    'due_date' => $this->getDueDate('Water', $rentPeriodStart, $schedules), 
                    'disconnection_date' => $this->getDisconnectionDate('Water', $rentPeriodStart, $schedules), 
                    'status' => 'unpaid'
                ]
            );
            $this->info("  ✓ Water bill for previous month processed.");
        }

        // --- 3. Electricity Bill (Previous Month, Zero Amount) ---
        if ($electricityRate) {
            Billing::updateOrCreate(
                ['stall_id' => $stall->id, 'utility_type' => 'Electricity', 'period_start' => $utilityPeriodStart->toDateString()],
                ['period_end' => $utilityPeriodEnd->toDateString(), 'amount' => 0, 'previous_reading' => 0, 'current_reading' => 0, 'consumption' => 0, 'rate' => $electricityRate, 'due_date' => $this->getDueDate('Electricity', $rentPeriodStart, $schedules), 'disconnection_date' => $this->getDisconnectionDate('Electricity', $rentPeriodStart, $schedules), 'status' => 'unpaid']
            );
            $this->info("  ✓ Placeholder Electricity bill for previous month processed.");
            
            // --- 4. Create Initial Electricity Reading for the CURRENT month ---
            // This prepares the system for the first actual meter reading at the end of the current month.
            UtilityReading::firstOrCreate(
                [
                    'stall_id' => $stall->id,
                    'utility_type' => 'Electricity', 
                    // Use a consistent date for the reading record, e.g., the end of the current month.
                    'reading_date' => $rentPeriodEnd->toDateString() 
                ],
                ['previous_reading' => 0, 'current_reading' => 0, 'consumption' => 0]
            );
            $this->info("  ✓ Initial Electricity reading record for current month processed.");
        }
    }

    private function getDueDate($type, Carbon $billingPeriod, $schedules)
    {
        if ($type === 'Rent') {
            return $billingPeriod->copy()->endOfMonth()->toDateString();
        }
        $key = "Due Date - {$type}";
        // Safe access using optional() or check existence
        $scheduleItem = $schedules->get($key);
        $day = $scheduleItem ? $scheduleItem->description : null;
        
        // Fallback to end of month if schedule is missing or invalid
        if (!is_numeric($day)) {
            return $billingPeriod->copy()->endOfMonth()->toDateString();
        }
        
        // Handle cases where day might be greater than days in month (e.g. 31st in Feb)
        try {
            return $billingPeriod->copy()->day((int)$day)->toDateString();
        } catch (\Exception $e) {
             return $billingPeriod->copy()->endOfMonth()->toDateString();
        }
    }

    private function getDisconnectionDate($type, Carbon $billingPeriod, $schedules)
    {
        $key = "Disconnection - {$type}";
        $scheduleItem = $schedules->get($key);
        $day = $scheduleItem ? $scheduleItem->description : null;
        
        if (is_numeric($day)) {
             try {
                return $billingPeriod->copy()->day((int)$day)->toDateString();
             } catch (\Exception $e) {
                return null;
             }
        }
        return null;
    }
}