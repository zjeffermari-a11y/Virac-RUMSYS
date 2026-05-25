<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StallsSeeder extends Seeder
{
    public function run()
    {
        // Due to the large size (784 stalls), we'll insert in batches for better performance
        
        // Batch 1: Wet Section stalls with vendors (MS-01 to MS-07)
        $batch1 = [
            ['id' => 1, 'section_id' => 1, 'table_number' => 'MS-01', 'vendor_id' => 2, 'daily_rate' => 126.00, 'created_at' => '2025-08-22 10:32:28', 'updated_at' => '2025-10-26 23:45:39', 'area' => null],
            ['id' => 2, 'section_id' => 1, 'table_number' => 'MS-02', 'vendor_id' => 5, 'daily_rate' => 126.00, 'created_at' => '2025-08-22 10:32:28', 'updated_at' => '2025-10-10 02:36:01', 'area' => null],
            ['id' => 3, 'section_id' => 1, 'table_number' => 'MS-03', 'vendor_id' => 104, 'daily_rate' => 126.00, 'created_at' => '2025-09-01 08:58:12', 'updated_at' => '2025-10-10 02:36:01', 'area' => null],
            ['id' => 4, 'section_id' => 1, 'table_number' => 'MS-04', 'vendor_id' => 91, 'daily_rate' => 126.00, 'created_at' => '2025-09-01 08:59:56', 'updated_at' => '2025-10-10 02:36:01', 'area' => null],
            ['id' => 5, 'section_id' => 1, 'table_number' => 'MS-05', 'vendor_id' => 94, 'daily_rate' => 126.00, 'created_at' => '2025-09-01 09:03:15', 'updated_at' => '2025-10-10 02:36:01', 'area' => null],
            ['id' => 17, 'section_id' => 1, 'table_number' => 'MS-06', 'vendor_id' => 93, 'daily_rate' => 126.00, 'created_at' => '2025-09-01 09:23:20', 'updated_at' => '2025-10-10 02:36:01', 'area' => null],
            ['id' => 107, 'section_id' => 1, 'table_number' => 'MS-07', 'vendor_id' => 102, 'daily_rate' => 126.00, 'created_at' => '2025-09-10 13:24:06', 'updated_at' => '2025-10-10 02:36:01', 'area' => null],
        ];
        
        DB::table('stalls')->insert($batch1);
        
        // Batch 2: Dry Section stalls with vendors
        $batch2 = [
            ['id' => 6, 'section_id' => 2, 'table_number' => 'L1', 'vendor_id' => 105, 'daily_rate' => 19.60, 'created_at' => '2025-08-22 10:32:28', 'updated_at' => '2025-10-11 02:45:44', 'area' => 18.00],
            ['id' => 16, 'section_id' => 2, 'table_number' => 'L6', 'vendor_id' => 89, 'daily_rate' => 19.00, 'created_at' => '2025-08-31 21:01:10', 'updated_at' => '2025-09-12 17:35:00', 'area' => 18.00],
        ];
        
        DB::table('stalls')->insert($batch2);
        
        // Batch 3: Semi-Wet stalls with vendors
        $batch3 = [
            ['id' => 493, 'section_id' => 1, 'table_number' => 'MS-12', 'vendor_id' => 117, 'daily_rate' => 105.00, 'created_at' => '2025-09-13 00:59:45', 'updated_at' => '2025-10-15 14:03:36', 'area' => null],
            ['id' => 494, 'section_id' => 1, 'table_number' => 'MS-13', 'vendor_id' => 118, 'daily_rate' => 105.00, 'created_at' => '2025-09-13 00:59:45', 'updated_at' => '2025-10-15 20:49:22', 'area' => null],
            ['id' => 777, 'section_id' => 3, 'table_number' => 'CPDFS-6', 'vendor_id' => 106, 'daily_rate' => 58.00, 'created_at' => '2025-09-13 01:32:30', 'updated_at' => '2025-10-13 02:00:54', 'area' => null],
        ];
        
        DB::table('stalls')->insert($batch3);
        
        // Now insert all vacant stalls (no vendor_id) - WET SECTION
        $this->insertWetSectionVacantStalls();
        
        // DRY SECTION vacant stalls
        $this->insertDrySectionVacantStalls();
        
        // SEMI-WET SECTION vacant stalls
        $this->insertSemiWetVacantStalls();
    }
    
    private function insertWetSectionVacantStalls()
    {
        $stalls = [];
        
        // MS-08 to MS-34 (vacant)
        $msNumbers = [8, 9, 10, 11, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34];
        $id = 489;
        foreach ($msNumbers as $num) {
            $stalls[] = [
                'id' => $id++,
                'section_id' => 1,
                'table_number' => 'MS-' . str_pad($num, 2, '0', STR_PAD_LEFT),
                'vendor_id' => null,
                'daily_rate' => $num <= 13 ? 126.00 : 105.00,
                'created_at' => '2025-09-13 00:59:45',
                'updated_at' => $num <= 13 ? '2025-10-10 02:36:01' : '2025-09-13 00:59:45',
                'area' => null
            ];
        }
        
        // FS-35 to FS-94 (Fish Section - vacant)
        for ($i = 35; $i <= 94; $i++) {
            $stalls[] = [
                'id' => $id++,
                'section_id' => 1,
                'table_number' => 'FS-' . $i,
                'vendor_id' => null,
                'daily_rate' => $i <= 84 ? 84.00 : 105.00,
                'created_at' => '2025-09-13 00:59:45',
                'updated_at' => '2025-09-13 00:59:45',
                'area' => null
            ];
        }
        
        // BS-1 to BS-11 (Butcher Section - vacant)
        for ($i = 1; $i <= 11; $i++) {
            $stalls[] = [
                'id' => $id++,
                'section_id' => 1,
                'table_number' => 'BS-' . $i,
                'vendor_id' => null,
                'daily_rate' => 47.00,
                'created_at' => '2025-09-13 00:59:45',
                'updated_at' => '2025-09-13 00:59:45',
                'area' => null
            ];
        }
        
        // PFS-1 to PFS-12 (Premium Fish Section - vacant)
        for ($i = 1; $i <= 12; $i++) {
            $stalls[] = [
                'id' => $id++,
                'section_id' => 1,
                'table_number' => 'PFS-' . $i,
                'vendor_id' => null,
                'daily_rate' => 105.00,
                'created_at' => '2025-09-13 00:59:45',
                'updated_at' => '2025-09-13 00:59:45',
                'area' => null
            ];
        }
        
        DB::table('stalls')->insert($stalls);
    }
    
    private function insertDrySectionVacantStalls()
    {
        $stalls = [];
        $id = 7; // Starting after L1 (id 6)
        
        // L2 to L5
        $lInitial = [
            ['id' => 7, 'table_number' => 'L2', 'area' => 11.00, 'rate' => 19.60],
            ['id' => 8, 'table_number' => 'L3', 'area' => 11.00, 'rate' => 19.60],
            ['id' => 9, 'table_number' => 'L4', 'area' => 11.00, 'rate' => 19.60],
            ['id' => 10, 'table_number' => 'L5', 'area' => 16.00, 'rate' => 19.60],
        ];
        
        foreach ($lInitial as $stall) {
            $stalls[] = [
                'id' => $stall['id'],
                'section_id' => 2,
                'table_number' => $stall['table_number'],
                'vendor_id' => null,
                'daily_rate' => $stall['rate'],
                'created_at' => '2025-08-22 10:32:28',
                'updated_at' => '2025-09-12 17:35:00',
                'area' => $stall['area']
            ];
        }
        
        // FVS stalls (11-15)
        for ($i = 11; $i <= 15; $i++) {
            $stalls[] = [
                'id' => $i,
                'section_id' => 3,
                'table_number' => 'FVS-' . str_pad($i - 10, 2, '0', STR_PAD_LEFT),
                'vendor_id' => null,
                'daily_rate' => 105.00,
                'created_at' => $i == 15 ? '2025-08-24 06:59:41' : '2025-08-22 10:32:28',
                'updated_at' => '2025-09-12 17:35:50',
                'area' => null
            ];
        }
        
        // L7 to L44 (id 616-653) - 38 stalls
        $id = 616;
        for ($i = 7; $i <= 44; $i++) {
            $rate = ($i >= 7 && $i <= 23) ? 17.50 : (($i >= 24 && $i <= 42) ? 15.40 : 17.50);
            $area = 10.00; // Default, varies by stall
            if ($i == 7) $area = 5.00;
            if ($i == 8) $area = 5.70;
            if ($i == 10 || $i == 24) $area = 10.50;
            if ($i >= 37 && $i <= 42) $area = 8.70;
            if ($i == 43 || $i == 44) $area = 10.50;
            
            $stalls[] = [
                'id' => $id++,
                'section_id' => 2,
                'table_number' => 'L' . $i,
                'vendor_id' => null,
                'daily_rate' => $rate,
                'created_at' => '2025-09-13 01:32:30',
                'updated_at' => $i <= 15 ? '2025-09-12 17:35:00' : '2025-09-13 01:32:30',
                'area' => $area
            ];
        }
        
        // R1 to R44 (id 654-697) - 44 stalls
        for ($i = 1; $i <= 44; $i++) {
            $rate = 19.60;
            $area = 18.00;
            
            if ($i == 2) $area = 11.50;
            if ($i == 3 || $i == 4) $area = 11.00;
            if ($i == 5 || $i == 6) { $rate = 17.50; $area = $i == 5 ? 5.70 : 5.00; }
            if ($i >= 7 && $i <= 21) { $rate = 15.40; $area = 10.00; }
            if ($i == 7) $area = 10.70;
            if ($i == 9) $area = 10.50;
            if ($i >= 22 && $i <= 35) { $rate = 17.50; $area = 10.00; }
            if ($i == 23) $area = 10.50;
            if ($i >= 36 && $i <= 42) { $rate = 15.40; $area = 8.70; }
            if ($i == 42) $area = 7.30;
            if ($i == 43 || $i == 44) { $rate = 17.50; $area = 7.20; }
            
            $stalls[] = [
                'id' => $id++,
                'section_id' => 2,
                'table_number' => 'R' . $i,
                'vendor_id' => null,
                'daily_rate' => $rate,
                'created_at' => '2025-09-13 01:32:30',
                'updated_at' => '2025-09-13 01:32:30',
                'area' => $area
            ];
        }
        
        DB::table('stalls')->insert($stalls);
    }
    
    private function insertSemiWetVacantStalls()
    {
        $stalls = [];
        $id = 698;
        
        // FVS-6 to FVS-71
        for ($i = 6; $i <= 71; $i++) {
            $rate = ($i >= 6 && $i <= 11) ? 105.00 : (($i >= 12 && $i <= 63) ? 84.00 : 105.00);
            
            $stalls[] = [
                'id' => $id++,
                'section_id' => 3,
                'table_number' => 'FVS-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'vendor_id' => null,
                'daily_rate' => $rate,
                'created_at' => '2025-09-13 01:32:30',
                'updated_at' => ($i >= 6 && $i <= 11) ? '2025-09-12 17:35:50' : '2025-09-13 01:32:30',
                'area' => null
            ];
        }
        
        // DFS-72 to DFS-79
        for ($i = 72; $i <= 79; $i++) {
            $stalls[] = [
                'id' => $id++,
                'section_id' => 3,
                'table_number' => 'DFS-' . $i,
                'vendor_id' => null,
                'daily_rate' => 105.00,
                'created_at' => '2025-09-13 01:32:30',
                'updated_at' => '2025-09-13 01:32:30',
                'area' => null
            ];
        }
        
        // CPDFS-1 to CPDFS-12 (id 772-783, skip 777 which has vendor)
        for ($i = 1; $i <= 12; $i++) {
            if ($i == 6) continue; // Skip CPDFS-6 as it has vendor (id 777)
            
            $stalls[] = [
                'id' => $id++,
                'section_id' => 3,
                'table_number' => 'CPDFS-' . $i,
                'vendor_id' => null,
                'daily_rate' => 58.00,
                'created_at' => '2025-09-13 01:32:30',
                'updated_at' => '2025-09-13 01:32:30',
                'area' => null
            ];
        }
        
        DB::table('stalls')->insert($stalls);
    }
}