<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This command tells Laravel to modify the existing 'stalls' table
        Schema::table('stalls', function (Blueprint $table) {
            // First, we remove the old 'monthly_rate' column to prevent the error.
            $table->dropColumn('monthly_rate');
        });

        // We run a second Schema command to ensure the drop happens first.
        Schema::table('stalls', function (Blueprint $table) {
            // Now, we add the column back with the automatic calculation.
            // storedAs is slightly better than virtualAs for performance.
            $table->decimal('monthly_rate', 10, 2)->storedAs(
                'CASE WHEN area IS NOT NULL AND area > 0 THEN daily_rate * area * 30 ELSE daily_rate * 30 END'
            )->after('daily_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            // To reverse, we drop our generated column...
            $table->dropColumn('monthly_rate');
        });
        
        Schema::table('stalls', function (Blueprint $table) {
            // ...and add back a normal column.
            $table->decimal('monthly_rate', 10, 2)->default(0.00)->after('daily_rate');
        });
    }
};