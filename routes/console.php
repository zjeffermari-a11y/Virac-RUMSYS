<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

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

// Get SMS schedules from database
$smsSchedules = DB::table('schedules')
    ->where('schedule_type', 'like', 'SMS - %')
    ->get()
    ->keyBy('schedule_type');

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