<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('run:test-schedule', function () {
    $this->call('schedule:run');
});

// ============================================
// SCHEDULED TASKS
// ============================================

// TEST SCHEDULE: Runs every minute for testing

// 🗓️ GENERATE BILLS: Runs at 1:00 AM on the 1st day of every month
Schedule::command('billing:generate')->monthlyOn(1, '07:00');

$billingStatementTime = '08:00';
$paymentReminderTime = '08:00';
$overdueAlertTime = '09:00';

$smsSchedules = collect();

// Get SMS schedules from database
if (!app()->runningInConsole() && Schema::hasTable('schedules')) {
    try {
        // Fetch schedules from the database
        $smsSchedules = DB::table('schedules')
            ->where('schedule_type', 'like', 'SMS - %')
            ->get()
            ->keyBy('schedule_type');

        // Override default times with values from DB if they exist
        $billingStatementTime = $smsSchedules->get('SMS - Billing Statements')?->description ?? $billingStatementTime;
        $paymentReminderTime = $smsSchedules->get('SMS - Payment Reminders')?->description ?? $paymentReminderTime;
        $overdueAlertTime = $smsSchedules->get('SMS - Overdue Alerts')?->description ?? $overdueAlertTime;

    } catch (\Exception $e) {
        // Log an error if fetching fails, but continue using default times
        Log::error("Error fetching schedule times from database: " . $e->getMessage());
    }
}

// 🚀 SEND BILLING STATEMENTS: Use dynamic day and time from database
$billingStatementSchedule = $smsSchedules->get('SMS - Billing Statements');
$billingStatementTime = $billingStatementSchedule?->description ?? '08:00';
$billingStatementDay = $billingStatementSchedule?->schedule_day ?? 1;
// Ensure day is valid (1-31)
$billingStatementDay = ($billingStatementDay >= 1 && $billingStatementDay <= 31) ? $billingStatementDay : 1;
Schedule::command('sms:send-billing-statements')->monthlyOn($billingStatementDay, $billingStatementTime);

// ⏰ SEND PAYMENT REMINDERS: Use dynamic time, default to 08:00 if not set
$paymentReminderTime = $smsSchedules->get('SMS - Payment Reminders')?->description ?? '08:00';
Schedule::command('sms:send-payment-reminders')
    ->dailyAt($paymentReminderTime);

// ⚠️ SEND OVERDUE ALERTS: Use dynamic time, default to 09:00 if not set
$overdueAlertTime = $smsSchedules->get('SMS - Overdue Alerts')?->description ?? '09:00';
Schedule::command('sms:send-overdue-alerts')->dailyAt($overdueAlertTime);