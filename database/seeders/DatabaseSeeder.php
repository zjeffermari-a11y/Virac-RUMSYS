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
                            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                                // Ignore duplicate entries
                            } else {
                                $this->command->error("\nError importing query: " . $e->getMessage());
                            }
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