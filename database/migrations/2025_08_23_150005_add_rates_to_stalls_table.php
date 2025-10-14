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
        Schema::table('stalls', function (Blueprint $table) {
            // Add daily_rate column, allowing decimal values, with a default of 0.00
            $table->decimal('daily_rate', 10, 2)->default(0.00)->after('vendor_id');
            
            // Add monthly_rate column, allowing decimal values, with a default of 0.00
            $table->decimal('monthly_rate', 10, 2)->default(0.00)->after('daily_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stalls', function (Blueprint $table) {
            // Define how to reverse the changes if you ever need to rollback the migration
            $table->dropColumn(['daily_rate', 'monthly_rate']);
        });
    }
};
