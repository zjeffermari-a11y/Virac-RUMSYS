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
    Schema::create('billing_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('billing_id')->constrained('billing')->onDelete('cascade');
        $table->string('field_changed');
        $table->string('old_value');
        $table->string('new_value');
        $table->foreignId('changed_by')->constrained('users');
        $table->timestamp('changed_at')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_histories');
    }
};
