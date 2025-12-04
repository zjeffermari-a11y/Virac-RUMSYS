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
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}