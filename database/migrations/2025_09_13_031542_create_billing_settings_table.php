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
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('utility_type')->unique();
            $table->decimal('surcharge_rate', 5, 4)->default(0.0000);
            $table->decimal('monthly_interest_rate', 5, 4)->default(0.0000);
            $table->decimal('penalty_rate', 5, 4)->default(0.0000);
            $table->decimal('discount_rate', 5, 4)->default(0.0000);
            $table->timestamps();
        });

        // --- CORRECTED SEED DATA ---
        // All arrays now have the same keys to prevent the column count mismatch error.
        DB::table('billing_settings')->insert([
            [
                'utility_type' => 'Rent',
                'surcharge_rate' => 0.25,
                'monthly_interest_rate' => 0.02,
                'penalty_rate' => 0.00,
                'discount_rate' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'utility_type' => 'Electricity',
                'surcharge_rate' => 0.00,
                'monthly_interest_rate' => 0.00,
                'penalty_rate' => 0.10,
                'discount_rate' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'utility_type' => 'Water',
                'surcharge_rate' => 0.00,
                'monthly_interest_rate' => 0.00,
                'penalty_rate' => 0.10,
                'discount_rate' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};