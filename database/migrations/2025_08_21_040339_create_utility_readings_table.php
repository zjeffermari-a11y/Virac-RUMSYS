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
    Schema::create('utility_readings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('stall_id')->constrained('stalls');
        $table->enum('utility_type', ['Electricity', 'Water']);
        $table->date('reading_date');
        $table->decimal('current_reading', 10, 2);
        $table->decimal('previous_reading', 10, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};
