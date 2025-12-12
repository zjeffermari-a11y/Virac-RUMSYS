<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->enum('utility_type', ['Rent','Electricity','Water']);
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('cascade');
            $table->decimal('rate', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rates');
    }
};
