<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rates')->insert([
            [
                'id' => 1,
                'utility_type' => 'Electricity',
                'rate' => 25.00,
                'created_at' => '2025-08-26 03:38:31',
                'updated_at' => '2025-10-26 23:50:25'
            ],
            [
                'id' => 2,
                'utility_type' => 'Water',
                'rate' => 5.00,
                'created_at' => '2025-08-26 03:38:31',
                'updated_at' => '2025-09-29 15:48:55'
            ]
        ]);
    }
}