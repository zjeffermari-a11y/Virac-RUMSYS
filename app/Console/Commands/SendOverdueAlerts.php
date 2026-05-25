<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendOverdueAlerts extends Command
{
    /**
     * The signature now accepts an optional date to simulate running on that day.
     * @var string
     */
    protected $signature = 'sms:send-overdue-alerts {date?}';

    protected $description = 'Sends SMS alerts to vendors with bills that are currently overdue.';

    public function handle(SmsService $smsService)
    {
        
    $today = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::today();
    $this->info("Checking for overdue bills at key milestones as of: " . $today->toDateString());

    // Get overdue days from database (default to [1, 3, 7, 14, 21, 30] if not set)
    $schedule = \App\Models\Schedule::where('schedule_type', 'SMS - Overdue Alerts')->first();
    $overdueDays = $schedule && $schedule->sms_days && is_array($schedule->sms_days) 
        ? $schedule->sms_days 
        : [1, 3, 7, 14, 21, 30];
    
    // Sort and filter valid days
    $overdueDays = array_filter(array_map('intval', $overdueDays), fn($d) => $d > 0 && $d <= 365);
    sort($overdueDays);
    
    if (empty($overdueDays)) {
        $this->info("No overdue days configured. Using default: [1, 3, 7, 14, 21, 30]");
        $overdueDays = [1, 3, 7, 14, 21, 30];
    }
    
    $targetDueDates = collect($overdueDays)->map(fn($days) => $today->copy()->subDays($days)->toDateString());

    // Find vendors with unpaid bills that were due on one of the target dates.
    $vendors = User::vendors()->active()
        ->whereHas('stall')
        ->whereNotNull('contact_number')
        ->whereHas('billings', function ($query) use ($targetDueDates) {
            $query->where('status', 'unpaid')
                  // ✅ START OF FIX: Change the query to look for specific past dates.
                  ->whereIn('due_date', $targetDueDates);
                  // ✅ END OF FIX
        })
        ->get();
    
    if ($vendors->isEmpty()) {
        Log::info("No vendors with bills overdue by 1, 3, 7, 14, 21, or 30 days.");
        $this->info("No alerts sent.");
        return 0;
    }
    
    $this->info("Found {$vendors->count()} vendors with strategically overdue bills. Preparing alerts...");
    $successCount = 0;
    $failCount = 0;

    foreach ($vendors as $vendor) {
        $this->info("Processing overdue alert for vendor ID: {$vendor->id} ({$vendor->name})");
        $result = $smsService->sendTemplatedSms($vendor, 'overdue_alert');
        if ($result['success']) {
            $this->info("Sent overdue alert to {$vendor->name}.");
            $successCount++;
        } else {
            $this->warn("Failed to send overdue alert to {$vendor->name}: {$result['message']}");
            $failCount++;
        }
    }

    Log::info("=== OVERDUE ALERTS COMMAND COMPLETED: {$successCount} sent, {$failCount} failed ===");
    $this->info('Finished sending overdue alerts.');
    return 0;
    }
}