<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsSeeder extends Seeder
{
    public function run()
    {
        DB::table('payments')->insert([
            ['id' => 1, 'billing_id' => 9, 'amount_paid' => 3780.00, 'payment_date' => '2025-09-26', 'penalty' => 945.00, 'discount' => 0.00, 'created_at' => '2025-09-25 21:24:08', 'updated_at' => '2025-10-31 23:58:05'],
            ['id' => 8, 'billing_id' => 4, 'amount_paid' => 4725.00, 'payment_date' => '2025-10-06', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-06 11:02:21', 'updated_at' => '2025-10-31 23:54:14'],
            ['id' => 9, 'billing_id' => 4, 'amount_paid' => 4725.00, 'payment_date' => '2025-10-06', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-06 11:44:36', 'updated_at' => '2025-10-31 23:54:14'],
            ['id' => 10, 'billing_id' => 59, 'amount_paid' => 3402.00, 'payment_date' => '2025-10-12', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-12 07:15:26', 'updated_at' => '2025-10-31 23:58:05'],
            ['id' => 12, 'billing_id' => 2, 'amount_paid' => 155.00, 'payment_date' => '2025-10-13', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-12 23:51:00', 'updated_at' => '2025-10-12 23:51:00'],
            ['id' => 13, 'billing_id' => 16, 'amount_paid' => 155.00, 'payment_date' => '2025-10-13', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-13 00:04:56', 'updated_at' => '2025-10-13 00:04:56'],
            ['id' => 14, 'billing_id' => 52, 'amount_paid' => 150.00, 'payment_date' => '2025-10-13', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-13 00:12:42', 'updated_at' => '2025-10-13 00:12:42'],
            ['id' => 15, 'billing_id' => 53, 'amount_paid' => 0.00, 'payment_date' => '2025-10-13', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-13 00:13:34', 'updated_at' => '2025-10-13 00:13:34'],
            ['id' => 16, 'billing_id' => 25, 'amount_paid' => 150.00, 'payment_date' => '2025-10-13', 'penalty' => 0.00, 'discount' => 0.00, 'created_at' => '2025-10-13 00:17:53', 'updated_at' => '2025-10-13 00:17:53'],
        ]);
    }
}