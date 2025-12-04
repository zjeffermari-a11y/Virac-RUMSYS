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
            // Change schedule_type from ENUM to STRING to allow flexible types
            // Note: We might need to drop the enum constraint first depending on DB driver, 
            // but usually modifying to string works in Laravel for MySQL.
            $table->string('schedule_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Revert to ENUM (warning: data loss if non-enum values exist)
            // We list all known types here to be safe
            $table->enum('schedule_type', [
                'Meter Reading', 
                'Due Date', 
                'Disconnection', 
                'SMS Notification',
                'Due Date - Electricity',
                'Disconnection - Electricity',
                'Due Date - Water',
                'Disconnection - Water',
                'Due Date - Rent',
                'Disconnection - Rent',
                'SMS - Billing Statements',
                'SMS - Payment Reminders',
                'SMS - Overdue Alerts'
            ])->change();
        });
    }
};
