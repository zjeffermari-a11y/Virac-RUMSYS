<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the default meter reading schedule if it doesn't exist
        Schedule::firstOrCreate(
            ['schedule_type' => 'Meter Reading'],
            [
                'description' => '25', // Default day
                'schedule_date' => now()
            ]
        );
    }
}