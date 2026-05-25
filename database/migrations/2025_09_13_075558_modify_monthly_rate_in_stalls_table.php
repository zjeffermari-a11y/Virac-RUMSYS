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
                $existingColumnNames = collect($columns)->pluck('name')->toArray();
                
                // Create backup
                DB::statement("CREATE TABLE stalls_backup AS SELECT * FROM stalls");
                
                // Drop original table
                Schema::dropIfExists('stalls');
                
                // Recreate table with the current structure (keep the column names as they are)
                Schema::create('stalls', function (Blueprint $table) use ($existingColumnNames) {
                    $table->id();
                    
                    // Use the actual column name from the existing table
                    if (in_array('stall_no', $existingColumnNames)) {
                        $table->string('stall_no')->unique();
                    } elseif (in_array('stall_number', $existingColumnNames)) {
                        $table->string('stall_number')->unique();
                    }
                    
                    // Check for table_number column
                    if (in_array('table_number', $existingColumnNames)) {
                        $table->string('table_number')->nullable();
                    }
                    
                    $table->unsignedBigInteger('section_id');
                    $table->decimal('daily_rate', 10, 2)->default(0.00);
                    $table->decimal('area', 10, 2)->nullable();
                    $table->decimal('monthly_rate', 10, 2)->nullable();
                    $table->unsignedBigInteger('vendor_id')->nullable();
                    
                    // Check for user_id column
                    if (in_array('user_id', $existingColumnNames)) {
                        $table->unsignedBigInteger('user_id')->nullable();
                    }
                    
                    // Check for status column
                    if (in_array('status', $existingColumnNames)) {
                        $table->enum('status', ['Occupied', 'Available', 'Reserved', 'Under Maintenance'])->default('Available');
                    }
                    
                    $table->timestamps();
                    
                    $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                    $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
                    
                    if (in_array('user_id', $existingColumnNames)) {
                        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                    }
                });
                
                // Get columns excluding monthly_rate
                $columnNames = array_filter($existingColumnNames, function($name) {
                    return $name !== 'monthly_rate';
                });
                
                // Build column list for INSERT
                $insertColumns = implode(', ', $columnNames);
                
                // Build SELECT with calculated monthly_rate
                $selectParts = array_values($columnNames);
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