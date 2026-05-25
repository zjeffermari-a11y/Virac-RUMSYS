<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    protected $signature = 'sms:send-payment-reminders';

    protected $description = 'Sends SMS reminders for bills that are due soon or today.';

    public function handle(SmsService $smsService)
    {
    $this->info('Checking for bills due at strategic intervals...');

    $today = Carbon::today();
    
    // Get reminder days from database (default to [7, 5, 3, 1] if not set)
    $schedule = \App\Models\Schedule::where('schedule_type', 'SMS - Payment Reminders')->first();
    $reminderDays = $schedule && $schedule->sms_days && is_array($schedule->sms_days) 
        ? $schedule->sms_days 
        : [7, 5, 3, 1];
    
    // Sort and filter valid days
    $reminderDays = array_filter(array_map('intval', $reminderDays), fn($d) => $d > 0 && $d <= 365);
    sort($reminderDays);
    
    if (empty($reminderDays)) {
        $this->info("No reminder days configured. Using default: [7, 5, 3, 1]");
        $reminderDays = [7, 5, 3, 1];
    }
    
    $targetDates = collect($reminderDays)->map(fn($days) => $today->copy()->addDays($days)->toDateString());

    // Find vendors with unpaid bills that are due on one of the target dates.
    $vendors = User::vendors()->active()
        ->whereHas('stall')
        ->whereNotNull('contact_number')
        ->whereHas('billings', function ($query) use ($targetDates) {
            $query->where('status', 'unpaid')
                  // ✅ START OF FIX: Change the query to look for specific dates.
                  ->whereIn('due_date', $targetDates);
                  // ✅ END OF FIX
        })
        ->get();
    
    if ($vendors->isEmpty()) {
        $this->info("No vendors found with payments due in 7, 3, or 1 day. No reminders sent.");
        return 0;
    }
    
    $this->info("Found {$vendors->count()} vendors with upcoming payments. Preparing reminders...");

    foreach ($vendors as $vendor) {
        $this->info("Processing reminder for vendor ID: {$vendor->id} ({$vendor->name})");
        $result = $smsService->sendTemplatedSms($vendor, 'payment_reminder');
         if ($result['success']) {
            $this->info("Sent payment reminder to {$vendor->name}.");
        } else {
            $this->warn("Failed to send reminder to {$vendor->name}: {$result['message']}");
        }
    }

    $this->info('Finished sending payment reminders.');
    return 0;
}   
}