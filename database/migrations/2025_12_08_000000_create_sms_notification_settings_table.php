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
        if (!Schema::hasTable('sms_notification_settings')) {
            Schema::create('sms_notification_settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('message_template');
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_notification_settings');
    }
};
