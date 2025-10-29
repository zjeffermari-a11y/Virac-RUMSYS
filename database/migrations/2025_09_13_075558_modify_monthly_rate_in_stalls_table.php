<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need a different approach
        if (DB::getDriverName() === 'sqlite') {
            // Disable foreign key constraints
            DB::statement('PRAGMA foreign_keys = OFF');
            
            try {
                // Get all column names from the current stalls table
                $columns = DB::select("PRAGMA table_info(stalls)");
                $columnNames = collect($columns)->pluck('name')->filter(function($name) {
                    return $name !== 'monthly_rate'; // Exclude monthly_rate from copy
                })->toArray();
                
                // Create backup
                DB::statement("CREATE TABLE stalls_backup AS SELECT * FROM stalls");
                
                // Drop original table
                Schema::dropIfExists('stalls');
                
                // Recreate table with the new structure
                Schema::create('stalls', function (Blueprint $table) {
                    $table->id();
                    $table->string('stall_no')->unique();
                    $table->unsignedBigInteger('section_id');
                    $table->decimal('daily_rate', 10, 2)->default(0.00);
                    $table->decimal('area', 10, 2)->nullable();
                    $table->decimal('monthly_rate', 10, 2)->nullable();
                    $table->unsignedBigInteger('vendor_id')->nullable();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->enum('status', ['Occupied', 'Available', 'Reserved', 'Under Maintenance'])->default('Available');
                    $table->timestamps();
                    
                    $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                    $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                });
                
                // Build column list for INSERT (excluding monthly_rate from source)
                $insertColumns = implode(', ', $columnNames);
                
                // Build SELECT with calculated monthly_rate
                $selectParts = [];
                foreach ($columnNames as $col) {
                    $selectParts[] = $col;
                }
                $selectParts[] = "CASE WHEN area IS NOT NULL AND area > 0 THEN daily_rate * area * 30 ELSE daily_rate * 30 END as monthly_rate";
                $selectStatement = implode(', ', $selectParts);
                
                // Copy data back with calculated monthly_rate
                DB::statement("
                    INSERT INTO stalls ({$insertColumns}, monthly_rate)
                    SELECT {$selectStatement}
                    FROM stalls_backup
                ");
                
                // Drop backup table
                Schema::dropIfExists('stalls_backup');
                
            } finally {
                // Re-enable foreign key constraints
                DB::statement('PRAGMA foreign_keys = ON');
            }
        } else {
            // For MySQL/PostgreSQL, use generated columns
            Schema::table('stalls', function (Blueprint $table) {
                $table->dropColumn('monthly_rate');
            });

            Schema::table('stalls', function (Blueprint $table) {
                $table->decimal('monthly_rate', 10, 2)->storedAs(
                    'CASE WHEN area IS NOT NULL AND area > 0 THEN daily_rate * area * 30 ELSE daily_rate * 30 END'
                )->after('daily_rate');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            $table->dropColumn('monthly_rate');
        });
        
        Schema::table('stalls', function (Blueprint $table) {
            $table->decimal('monthly_rate', 10, 2)->default(0.00)->after('daily_rate');
        });
    }
};