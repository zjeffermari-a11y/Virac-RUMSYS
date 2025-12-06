<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SmsService;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;

class SendBillingStatements extends Command
{
    protected $signature = 'sms:send-billing-statements {--force : Force send even if not the scheduled day} {--stall= : Send only to specific stall number (e.g., MS-04)}';
    protected $description = 'Sends a monthly billing statement SMS to all vendors with unpaid bills.';

    public function handle(SmsService $smsService)
    {
        $this->info('Starting to send monthly billing statements...');

        // Get the scheduled day from database (default to day 1 if not set)
        $schedule = Schedule::where('schedule_type', 'SMS - Billing Statements')->first();
        $scheduledDay = $schedule && $schedule->schedule_day ? (int)$schedule->schedule_day : 1;
        
        // Only send if today is the scheduled day (unless --force is used)
        $today = now();
        if (!$this->option('force') && $today->day != $scheduledDay) {
            $this->info("Today is not the scheduled day ({$scheduledDay}). Skipping.");
            $this->info("Use --force to send anyway.");
            return 0;
        }
        
        if ($this->option('force')) {
            $this->warn("⚠️  FORCE MODE: Sending billing statements regardless of scheduled day.");
        }

        $vendorsQuery = User::vendors()->active()
            ->whereHas('stall')
            ->whereNotNull('contact_number')
            ->whereHas('billings', fn ($q) => $q->where('status', 'unpaid'));
        
        // Filter by specific stall if provided
        if ($this->option('stall')) {
            $stallNumber = $this->option('stall');
            $vendorsQuery->whereHas('stall', function($q) use ($stallNumber) {
                $q->where('table_number', $stallNumber);
            });
            $this->info("Filtering for stall: {$stallNumber}");
        }
        
        $vendors = $vendorsQuery->get();
            
        if ($vendors->isEmpty()) {
            $this->warn("No vendors found with outstanding bills" . ($this->option('stall') ? " for stall {$this->option('stall')}" : '') . ".");
            return 0;
        }
        
        $this->info("Found {$vendors->count()} vendor(s) with outstanding bills to notify.");

        $successCount = 0;
        $failCount = 0;
        
        foreach ($vendors as $vendor) {
            $this->line("Processing {$vendor->name} (ID: {$vendor->id})...");
            $this->line("  Contact Number: " . ($vendor->contact_number ?? 'NULL'));
            
            $result = $smsService->sendTemplatedSms($vendor, 'bill_statement');
            if ($result['success']) {
                $this->info("✓ Sent statement to {$vendor->name}.");
                $successCount++;
            } else {
                $this->warn("✗ Failed to send statement to {$vendor->name}: {$result['message']}");
                $failCount++;
            }
        }
        
        $this->info("\n=== Summary ===");
        $this->info("Successfully sent: {$successCount}");
        $this->info("Failed: {$failCount}");
        $this->info("Total vendors processed: " . $vendors->count());

        $this->info('Finished sending billing statements.');
        return 0;
    }
}
