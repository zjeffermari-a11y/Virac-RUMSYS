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
        Schema::table('schedules', function (Blueprint $table) {
            // Add JSON column to store days array for Payment Reminders and Overdue Alerts
            if (!Schema::hasColumn('schedules', 'sms_days')) {
                $table->json('sms_days')->nullable()->after('schedule_day');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'sms_days')) {
                $table->dropColumn('sms_days');
            }
        });
    }
};

