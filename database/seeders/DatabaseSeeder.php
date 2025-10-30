<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Important: Seed in order to respect foreign key constraints
        
        $this->call([
            // 1. Foundation tables (no dependencies)
            RolesSeeder::class,
            SectionsSeeder::class,
            RatesSeeder::class,
            BillingSettingsSeeder::class,
            
            // 2. Users (depends on roles)
            UsersSeeder::class,
            
            // 3. Stalls (depends on sections and users)
            StallsSeeder::class,
            
            // 4. Schedules (standalone)
            SchedulesSeeder::class,
            SmsNotificationSettingsSeeder::class,
            
            // 5. Billing and Payments (depends on stalls)
            BillingSeeder::class,
            PaymentsSeeder::class,
            
            // 6. Large historical data (depends on stalls)
            // Note: This seeder contains 2,698 records
            UtilityReadingSeeder::class,
            
            // 7. Audit and history tables (optional - depends on users)
            // AuditTrailsSeeder::class,  // Uncomment if needed
        ]);
    }
}