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

        // Seed data removed to avoid conflicts with DatabaseSeeder/SQL dump
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};