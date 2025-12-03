<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       DB::disableQueryLog();
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $sqlPath = database_path('seeders/cap102_latest.sql');

        if (File::exists($sqlPath)) {
            $this->command->info('Importing cap102_latest.sql (Streaming Mode)...');
            
            $handle = fopen($sqlPath, "r");
            if ($handle) {
                $query = ""; 
                $count = 0;

                while (($line = fgets($handle)) !== false) {
                    $trimLine = trim($line);
                    
                    // Skip comments
                    if (empty($trimLine) || str_starts_with($trimLine, '--') || (str_starts_with($trimLine, '/*') && !str_starts_with($trimLine, '/*!'))) {
                        continue;
                    }

                    // Skip migrations table to avoid duplicates there
                    if (Str::contains($line, 'INSERT INTO `migrations`') || Str::contains($line, 'INSERT INTO migrations')) {
                        continue;
                    }

                    $query .= $line;

                    if (str_ends_with($trimLine, ';')) {
                        try {
                            DB::unprepared($query);
                            $count++;
                            if ($count % 50 == 0) $this->command->getOutput()->write('.');
                        } catch (\Exception $e) {
                            // Ignore duplicates, keep going
                        }
                        $query = "";
                    }
                }
                fclose($handle);
                $this->command->info("\nData imported successfully!");
            }
        } else {
            $this->command->error('SQL file not found at: ' . $sqlPath);
        }
        // Important: Seed in order to respect foreign key constraints
        
        //$this->call([
            // 1. Foundation tables (no dependencies)
            //RolesSeeder::class,
            //SectionsSeeder::class,
            //RatesSeeder::class,
            //BillingSettingsSeeder::class,
            
            // 2. Users (depends on roles)
            //UsersSeeder::class,
            
            // 3. Stalls (depends on sections and users)
            //StallsSeeder::class,
            
            // 4. Schedules (standalone)
            //SchedulesSeeder::class,
            //SmsNotificationSettingsSeeder::class,
            
            // 5. Billing and Payments (depends on stalls)
            //BillingSeeder::class,
            //PaymentsSeeder::class,
            
            // 6. Large historical data (depends on stalls)
            // Note: This seeder contains 2,698 records
            //UtilityReadingSeeder::class,
            
            // 7. Audit and history tables (optional - depends on users)
            // AuditTrailsSeeder::class,  // Uncomment if needed
        //]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}