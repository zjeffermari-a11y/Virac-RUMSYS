<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UtilityReadingsSeeder extends Seeder
{
    public function run()
    {
        // Disable query log for performance
        DB::connection()->disableQueryLog();
        
        // Get all stall IDs
        $stallIds = DB::table('stalls')->pluck('id')->toArray();
        
        $batch = [];
        $id = 1514; // Starting ID from your SQL
        
        // August 31, 2025 readings (first batch)
        foreach ($stallIds as $stallId) {
            $batch[] = [
                'id' => $id++,
                'stall_id' => $stallId,
                'utility_type' => 'Electricity',
                'reading_date' => '2025-08-31',
                'current_reading' => 0.00,
                'previous_reading' => 0.00,
                'created_at' => '2025-09-25 02:34:11',
                'updated_at' => '2025-09-25 02:34:11',
            ];
            
            // Insert in batches of 500 for performance
            if (count($batch) >= 500) {
                DB::table('utility_readings')->insert($batch);
                $batch = [];
            }
        }
        
        if (!empty($batch)) {
            DB::table('utility_readings')->insert($batch);
        }
        
        // September 30, 2025 readings
        $batch = [];
        foreach ($stallIds as $stallId) {
            $batch[] = [
                'id' => $id++,
                'stall_id' => $stallId,
                'utility_type' => 'Electricity',
                'reading_date' => '2025-09-30',
                'current_reading' => 0.00,
                'previous_reading' => 0.00,
                'created_at' => '2025-10-12 05:56:00',
                'updated_at' => '2025-10-12 05:56:00',
            ];
            
            if (count($batch) >= 500) {
                DB::table('utility_readings')->insert($batch);
                $batch = [];
            }
        }
        
        if (!empty($batch)) {
            DB::table('utility_readings')->insert($batch);
        }
        
        // October 31, 2025 readings (excluding some stalls)
        $batch = [];
        // Skip stall_id 3 and 6 based on your SQL
        $excludedStalls = [3, 6];
        $filteredStalls = array_diff($stallIds, $excludedStalls);
        
        foreach ($filteredStalls as $stallId) {
            $batch[] = [
                'id' => $id++,
                'stall_id' => $stallId,
                'utility_type' => 'Electricity',
                'reading_date' => '2025-10-31',
                'current_reading' => 0.00,
                'previous_reading' => 0.00,
                'created_at' => '2025-10-31 07:32:23',
                'updated_at' => '2025-10-31 07:32:23',
            ];
            
            if (count($batch) >= 500) {
                DB::table('utility_readings')->insert($batch);
                $batch = [];
            }
        }
        
        if (!empty($batch)) {
            DB::table('utility_readings')->insert($batch);
        }
        
        // Add specific readings with actual values
        $this->insertSpecificReadings();
    }
    
    private function insertSpecificReadings()
    {
        // Stall 1 electricity readings with actual consumption
        DB::table('utility_readings')->insert([
            [
                'id' => 2106,
                'stall_id' => 3,
                'utility_type' => 'Electricity',
                'reading_date' => '2025-10-09',
                'current_reading' => 0.00,
                'previous_reading' => 0.00,
                'created_at' => '2025-10-09 10:31:08',
                'updated_at' => '2025-10-09 10:31:08',
            ],
            [
                'id' => 2107,
                'stall_id' => 3,
                'utility_type' => 'Electricity',
                'reading_date' => '2025-10-31',
                'current_reading' => 0.00,
                'previous_reading' => 0.00,
                'created_at' => '2025-10-09 10:56:30',
                'updated_at' => '2025-10-09 10:56:30',
            ],
            [
                'id' => 2108,
                'stall_id' => 6,
                'utility_type' => 'Electricity',
                'reading_date' => '2025-10-31',
                'current_reading' => 0.00,
                'previous_reading' => 0.00,
                'created_at' => '2025-10-11 02:45:45',
                'updated_at' => '2025-10-11 02:45:45',
            ],
        ]);
    }
}