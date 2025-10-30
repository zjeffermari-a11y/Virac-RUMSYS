<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('billing_settings')->insert([
            [
                'id' => 1,
                'utility_type' => 'Rent',
                'surcharge_rate' => 0.2500,
                'monthly_interest_rate' => 0.0200,
                'penalty_rate' => 0.0000,
                'discount_rate' => 0.1000,
                'created_at' => '2025-09-12 19:28:46',
                'updated_at' => '2025-10-06 23:13:17'
            ],
            [
                'id' => 2,
                'utility_type' => 'Electricity',
                'surcharge_rate' => 0.0000,
                'monthly_interest_rate' => 0.0000,
                'penalty_rate' => 0.0000,
                'discount_rate' => 0.0000,
                'created_at' => '2025-09-12 19:28:46',
                'updated_at' => '2025-09-13 06:56:30'
            ],
            [
                'id' => 3,
                'utility_type' => 'Water',
                'surcharge_rate' => 0.0000,
                'monthly_interest_rate' => 0.0000,
                'penalty_rate' => 0.0000,
                'discount_rate' => 0.0000,
                'created_at' => '2025-09-12 19:28:46',
                'updated_at' => '2025-09-13 06:56:33'
            ]
        ]);
    }
}