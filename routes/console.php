<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

if (!app()->runningInConsole() && Schema::hasTable('schedules')) {
    // Get SMS schedules from database
    $smsSchedules = DB::table('schedules')
        ->where('schedule_type', 'like', 'SMS - %')
        ->get()
        ->keyBy('schedule_type');

    // Default times (these will be used during console commands like migrate)
    $billingStatementTime = '08:00';
    $paymentReminderTime = '08:00';
    $overdueAlertTime = '09:00';

    // Try to get dynamic times from DB if available
    $billingStatementTime = $smsSchedules->get('SMS - Billing Statements')?->description ?? $billingStatementTime;
    $paymentReminderTime = $smsSchedules->get('SMS - Payment Reminders')?->description ?? $paymentReminderTime;
    $overdueAlertTime = $smsSchedules->get('SMS - Overdue Alerts')?->description ?? $overdueAlertTime;

    // Define schedules using the determined times
    Schedule::command('sms:send-billing-statements')->monthlyOn(1, $billingStatementTime);
    Schedule::command('sms:send-payment-reminders')->dailyAt($paymentReminderTime);
    Schedule::command('sms:send-overdue-alerts')->dailyAt($overdueAlertTime);

} else if (app()->runningInConsole()) {
    // If running in console (like during migrate), define schedules with default times
    // This prevents errors if the table doesn't exist yet.
    Schedule::command('sms:send-billing-statements')->monthlyOn(1, '08:00');
    Schedule::command('sms:send-payment-reminders')->dailyAt('08:00');
    Schedule::command('sms:send-overdue-alerts')->dailyAt('09:00');
}

// 🚀 SEND BILLING STATEMENTS: Use dynamic time, default to 08:00 if not set
$billingStatementTime = $smsSchedules->get('SMS - Billing Statements')?->description ?? '08:00';
Schedule::command('sms:send-billing-statements')->monthlyOn(1, $billingStatementTime);

// ⏰ SEND PAYMENT REMINDERS: Use dynamic time, default to 08:00 if not set
$paymentReminderTime = $smsSchedules->get('SMS - Payment Reminders')?->description ?? '08:00';
Schedule::command('sms:send-payment-reminders')
    ->dailyAt($paymentReminderTime);

// ⚠️ SEND OVERDUE ALERTS: Use dynamic time, default to 09:00 if not set
$overdueAlertTime = $smsSchedules->get('SMS - Overdue Alerts')?->description ?? '09:00';
Schedule::command('sms:send-overdue-alerts')->dailyAt($overdueAlertTime);