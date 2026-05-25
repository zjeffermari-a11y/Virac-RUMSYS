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
        Schema::create('billing_setting_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_setting_id')->constrained('billing_settings')->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users');
            $table->string('field_changed');
            $table->string('old_value');
            $table->string('new_value');
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_setting_histories');
    }
};