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

        // Truncate all tables to ensure a clean slate and avoid duplicate entry errors
        $tables = DB::select('SHOW TABLES');
        
        foreach ($tables as $table) {
            $tableName = "";
            foreach ($table as $key => $value) {
                $tableName = $value;
                break;
            }

            if ($tableName == 'migrations') {
                continue;
            }

            try {
                DB::table($tableName)->truncate();
                $this->command->info("Truncated table: $tableName");
            } catch (\Exception $e) {
                // Ignore errors if table doesn't exist or other issues
            }
        }

        $sqlPath = database_path('seeders/cap102_latest.sql');

        if (File::exists($sqlPath)) {
            $this->command->info('Importing cap102_latest.sql (Streaming Mode)...');
            
            $handle = fopen($sqlPath, "r");
            if ($handle) {
                $query = ""; 
                $count = 0;
                $skipping = false;

                while (($line = fgets($handle)) !== false) {
                    $trimLine = trim($line);
                    
                    // Skip comments
                    if (empty($trimLine) || str_starts_with($trimLine, '--') || (str_starts_with($trimLine, '/*') && !str_starts_with($trimLine, '/*!'))) {
                        continue;
                    }

                    // Handle Migrations Table Skipping (Multi-line support)
                    if (!$skipping && (Str::contains($line, 'INSERT INTO `migrations`') || Str::contains($line, 'INSERT INTO migrations'))) {
                        $skipping = true;
                    }

                    if ($skipping) {
                        if (str_ends_with($trimLine, ';')) {
                            $skipping = false;
                        }
                        continue;
                    }

                    $query .= $line;

                    if (str_ends_with($trimLine, ';')) {
                        try {
                            // Extract table name for logging
                            if (preg_match('/INSERT INTO [`"]?(\w+)[`"]?/', $query, $matches)) {
                                $this->command->info("Inserting into table: " . $matches[1]);
                            }

                            DB::unprepared($query);
                            $count++;
                        } catch (\Exception $e) {
                            // Log ALL errors now, since we are truncating, duplicates shouldn't happen.
                            // If they do, we need to see them to debug the "missing users" issue.
                            $this->command->error("\nError importing query: " . $e->getMessage());
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
        
        $this->call([
            AdminUserSeeder::class,
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}