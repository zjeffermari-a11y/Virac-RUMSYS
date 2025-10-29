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
        // For SQLite, we need to disable foreign key checks
        // and handle the migration differently
        if (DB::getDriverName() === 'sqlite') {
            // Disable foreign key constraints
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Get current table structure
            $stalls = DB::select("SELECT * FROM stalls LIMIT 1");
            
            // Drop and recreate the table with the new computed column
            Schema::dropIfExists('stalls_backup');
            
            // Create temporary backup table
            DB::statement('CREATE TABLE stalls_backup AS SELECT * FROM stalls');
            
            // Drop original table
            Schema::dropIfExists('stalls');
            
            // Recreate table with the new structure
            Schema::create('stalls', function (Blueprint $table) {
                $table->id();
                $table->string('stall_no')->unique();
                $table->unsignedBigInteger('section_id');
                $table->decimal('daily_rate', 10, 2)->default(0.00);
                $table->decimal('area', 10, 2)->nullable();
                // Add monthly_rate as a regular column for SQLite
                // We'll calculate it in the application layer or use database triggers
                $table->decimal('monthly_rate', 10, 2)
                      ->nullable()
                      ->comment('Calculated: (daily_rate * area * 30) or (daily_rate * 30)');
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->enum('status', ['Occupied', 'Available', 'Reserved', 'Under Maintenance'])->default('Available');
                $table->timestamps();
                
                $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
            
            // Copy data back and calculate monthly_rate
            DB::statement('
                INSERT INTO stalls (id, stall_no, section_id, daily_rate, area, monthly_rate, vendor_id, user_id, status, created_at, updated_at)
                SELECT 
                    id, 
                    stall_no, 
                    section_id, 
                    daily_rate, 
                    area,
                    CASE 
                        WHEN area IS NOT NULL AND area > 0 THEN daily_rate * area * 30 
                        ELSE daily_rate * 30 
                    END as monthly_rate,
                    vendor_id,
                    user_id,
                    status,
                    created_at,
                    updated_at
                FROM stalls_backup
            ');
            
            // Drop backup table
            Schema::dropIfExists('stalls_backup');
            
            // Re-enable foreign key constraints
            DB::statement('PRAGMA foreign_keys = ON');
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