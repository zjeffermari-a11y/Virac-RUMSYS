<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class SendBillingStatements extends Command
{
    protected $signature = 'sms:send-billing-statements';
    protected $description = 'Sends a monthly billing statement SMS to all vendors with unpaid bills.';

    public function handle(SmsService $smsService)
    {
        $this->info('Starting to send monthly billing statements...');

        $vendors = User::vendors()->active()
            ->whereHas('stall')
            ->whereNotNull('contact_number')
            ->whereHas('billings', fn ($q) => $q->where('status', 'unpaid'))
            ->get();
            
        $this->info("Found {$vendors->count()} vendors with outstanding bills to notify.");

        foreach ($vendors as $vendor) {
            $result = $smsService->sendTemplatedSms($vendor, 'bill_statement');
            if ($result['success']) {
                $this->info("Sent statement to {$vendor->name}.");
            } else {
                $this->warn("Failed to send statement to {$vendor->name}: {$result['message']}");
            }
        }

        $this->info('Finished sending billing statements.');
        return 0;
    }
}
