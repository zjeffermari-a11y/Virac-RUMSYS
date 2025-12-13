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

$billingStatementTime = '08:00';
$paymentReminderTime = '08:00';
$overdueAlertTime = '09:00';
$billGenerationDay = 1;
$billGenerationTime = '07:00';
$applyPendingChangesTime = '06:00';

$allSchedules = collect();

// Get all schedules from database
if (Schema::hasTable('schedules')) {
    try {
        // Fetch all schedules from the database (works in both console and web context)
        $allSchedules = DB::table('schedules')->get()->keyBy('schedule_type');

        // Get SMS schedules
        $smsSchedules = $allSchedules->filter(function($schedule) {
            return str_contains($schedule->schedule_type, 'SMS -');
        });

        // Override default times with values from DB if they exist
        $billingStatementTime = $smsSchedules->get('SMS - Billing Statements')?->description ?? $billingStatementTime;
        $paymentReminderTime = $smsSchedules->get('SMS - Payment Reminders')?->description ?? $paymentReminderTime;
        $overdueAlertTime = $smsSchedules->get('SMS - Overdue Alerts')?->description ?? $overdueAlertTime;

        // Get Bill Generation schedule
        $billGenSchedule = $allSchedules->get('Bill Generation');
        if ($billGenSchedule) {
            $billGenerationDay = $billGenSchedule->schedule_day ?? 1;
            $billGenerationTime = $billGenSchedule->description ?? '07:00';
            // Ensure day is valid (1-31)
            $billGenerationDay = ($billGenerationDay >= 1 && $billGenerationDay <= 31) ? $billGenerationDay : 1;
        }

        // Get Apply Pending Changes schedule
        $applyPendingSchedule = $allSchedules->get('Apply Pending Changes');
        if ($applyPendingSchedule) {
            $applyPendingChangesTime = $applyPendingSchedule->description ?? '06:00';
        }

    } catch (\Exception $e) {
        // Log an error if fetching fails, but continue using default times
        Log::error("Error fetching schedule times from database: " . $e->getMessage());
    }
}

// 🗓️ GENERATE BILLS: Use dynamic day and time from database
Schedule::command('billing:generate')->monthlyOn($billGenerationDay, $billGenerationTime);

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

// 🔄 APPLY PENDING RATE CHANGES: Use dynamic time from database
Schedule::command('billing:apply-pending-changes')->dailyAt($applyPendingChangesTime);