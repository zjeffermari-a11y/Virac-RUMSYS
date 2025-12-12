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

        $sqlPath = database_path('seeders/database(ceyver).sql');

        if (File::exists($sqlPath)) {
            $this->command->info('Importing database(ceyver).sql (Streaming Mode)...');
            
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

                    // Start skipping if we hit migrations insert
                    if (Str::contains($line, 'INSERT INTO `migrations`') || Str::contains($line, 'INSERT INTO migrations')) {
                        $count++;
                        $query = ""; // Clear any partial query
                        // Consume lines until we find the semicolon
                        if (!str_ends_with($trimLine, ';')) {
                            while (($nextLine = fgets($handle)) !== false) {
                                if (str_ends_with(trim($nextLine), ';')) {
                                    break;
                                }
                            }
                        }
                        continue;
                    }

                    $query .= $line;

                    if (str_ends_with($trimLine, ';')) {
                        try {
                            DB::unprepared($query);
                            $count++;
                            if ($count % 50 == 0) $this->command->getOutput()->write('.');
                        } catch (\Exception $e) {
                             $this->command->error("Error importing statement: " . $e->getMessage());
                             // Optional: don't stop on error, just log it. The loop continues.
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
        
        // Ensure Admin User exists regardless of SQL dump
        $this->call([
            AdminUserSeeder::class,
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}