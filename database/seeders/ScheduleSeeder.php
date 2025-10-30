<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchedulesSeeder extends Seeder
{
    public function run()
    {
        DB::table('schedules')->insert([
            ['id' => 1, 'schedule_type' => 'Meter Reading', 'description' => '30', 'schedule_day' => null, 'schedule_date' => '2025-01-01', 'created_at' => '2025-08-27 13:35:41', 'updated_at' => '2025-09-30 15:27:21'],
            ['id' => 3, 'schedule_type' => 'Due Date', 'description' => '27', 'schedule_day' => null, 'schedule_date' => '2025-01-01', 'created_at' => '2025-08-27 15:08:40', 'updated_at' => '2025-09-10 04:49:07'],
            ['id' => 4, 'schedule_type' => 'Disconnection', 'description' => '29', 'schedule_day' => null, 'schedule_date' => '2025-01-01', 'created_at' => '2025-08-27 15:08:40', 'updated_at' => '2025-09-07 00:41:38'],
            ['id' => 5, 'schedule_type' => 'undefined', 'description' => 'Not Set', 'schedule_day' => null, 'schedule_date' => '2025-09-12', 'created_at' => '2025-09-11 21:44:22', 'updated_at' => '2025-09-11 22:16:51'],
            ['id' => 6, 'schedule_type' => 'Due Date - Electricity', 'description' => '13', 'schedule_day' => null, 'schedule_date' => '2025-09-12', 'created_at' => '2025-09-11 22:20:55', 'updated_at' => '2025-10-16 00:47:00'],
            ['id' => 7, 'schedule_type' => 'Disconnection - Electricity', 'description' => '19', 'schedule_day' => null, 'schedule_date' => '2025-09-12', 'created_at' => '2025-09-11 22:20:57', 'updated_at' => '2025-10-16 00:47:00'],
            ['id' => 8, 'schedule_type' => 'Due Date - Water', 'description' => '13', 'schedule_day' => null, 'schedule_date' => '2025-09-12', 'created_at' => '2025-09-11 22:20:59', 'updated_at' => '2025-10-16 00:47:00'],
            ['id' => 9, 'schedule_type' => 'SMS - Billing Statements', 'description' => '08:00', 'schedule_day' => null, 'schedule_date' => '2025-09-30', 'created_at' => '2025-09-30 05:38:25', 'updated_at' => '2025-10-16 00:54:41'],
            ['id' => 10, 'schedule_type' => 'SMS - Payment Reminders', 'description' => '08:00', 'schedule_day' => null, 'schedule_date' => '2025-09-30', 'created_at' => '2025-09-30 05:38:26', 'updated_at' => '2025-10-10 01:56:47'],
            ['id' => 11, 'schedule_type' => 'SMS - Overdue Alerts', 'description' => '08:00', 'schedule_day' => null, 'schedule_date' => '2025-09-30', 'created_at' => '2025-09-30 05:38:26', 'updated_at' => '2025-10-07 12:42:05'],
        ]);
    }
}