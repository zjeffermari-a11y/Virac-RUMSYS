<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SmsService
{
    protected $apiKey;
    protected $senderName;
    protected $baseUrl = 'https://api.semaphore.co/api/v4';

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.api_key');
        $this->senderName = config('services.semaphore.sender_name');

        if (!$this->apiKey) {
            Log::warning('Semaphore API key is not set. SMS features will be disabled.');
            // throw new \Exception("Semaphore API key is missing."); // Disabled to prevent 500 error
        }
    }

    public function send($recipientNumber, $message, $priority = false)
    {
        $endpoint = $priority ? '/priority' : '/messages';
        $url = $this->baseUrl . $endpoint;

        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Semaphore API key is missing.'];
        }

        try {
            $payload = [
                'apikey'   => $this->apiKey,
                'number'   => $recipientNumber,
                'message'  => $message,
            ];

            if ($this->senderName) {
                $payload['sendername'] = $this->senderName;
            }

            $response = Http::asForm()->post($url, $payload);

            if ($response->successful()) {
                Log::info("SMS sent to {$recipientNumber} via Semaphore.", ['response' => $response->json()]);
                return ['success' => true, 'response' => $response->json()];
            } else {
                Log::error("Failed to send SMS to {$recipientNumber} via Semaphore.", ['response' => $response->body()]);
                return ['success' => false, 'response' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Semaphore SMS sending failed: ' . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendPriority($recipientNumber, $message)
    {
        return $this->send($recipientNumber, $message, true);
    }

    public function getCredits()
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Semaphore API key is missing.'];
        }

        $url = $this->baseUrl . '/account?apikey=' . $this->apiKey;

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                return ['success' => true, 'credit_balance' => $data['credit_balance'] ?? 'N/A'];
            } else {
                Log::error("Failed to fetch Semaphore credits.", ['response' => $response->body()]);
                return ['success' => false, 'message' => 'Failed to fetch credits.'];
            }
        } catch (\Exception $e) {
            Log::error('Semaphore credit fetch failed: ' . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function mergeTemplates(array $defaults, $customs): array
    {
        foreach ($customs as $custom) {
            switch ($custom->name) {
                case 'bill_statement_wet_section':
                    $defaults['bill_statement']['wet_section'] = $custom->message_template;
                    break;
                case 'bill_statement_dry_section':
                    $defaults['bill_statement']['dry_section'] = $custom->message_template;
                    break;
                case 'payment_reminder_template':
                    $defaults['payment_reminder']['template'] = $custom->message_template;
                    break;
                case 'overdue_alert_template':
                    $defaults['overdue_alert']['template'] = $custom->message_template;
                    break;
            }
        }
        return $defaults;
    }

    private function getFinalTemplates()
    {
        $path = config_path('message_templates.json');
        if (!File::exists($path)) {
            Log::error('Default templates file not found.');
            return [];
        }
        $defaultTemplates = json_decode(File::get($path), true);
        $customTemplatesFromDb = DB::table('sms_notification_settings')->get();
        return $this->mergeTemplates($defaultTemplates, $customTemplatesFromDb);
    }

    public function sendTemplatedSms(User $user, string $templateName, array $extraData = [])
    {
        Log::info("SmsService: Starting sendTemplatedSms for user {$user->id} with template '{$templateName}'.");
    
        $templates = $this->getFinalTemplates();
        $templateData = $templates[$templateName] ?? null;
    
        if (!$templateData) {
            Log::error("SMS template '{$templateName}' not found.");
            return ['success' => false, 'message' => "Template '{$templateName}' not found."];
        }
    
        $messageTemplate = '';
        if (is_array($templateData) && isset($user->stall->section)) {
            $sectionName = strtolower($user->stall->section->name);
            $sectionKey = str_contains($sectionName, 'wet') ? 'wet_section' : 'dry_section';
            $messageTemplate = $templateData[$sectionKey] ?? ($templateData['template'] ?? '');
        } elseif (is_array($templateData)) {
            $messageTemplate = $templateData['template'] ?? '';
        }
    
        if (empty($messageTemplate)) {
            Log::error("No suitable SMS template found for user {$user->id} and template name '{$templateName}'.");
            return ['success' => false, 'message' => 'No suitable template found.'];
        }
        
        Log::info("SmsService: Raw template found: \"{$messageTemplate}\"");
    
        $normalizedTemplate = preg_replace_callback(
            '/{{\s*(.*?)\s*}}/',
            function ($matches) {
                return '{{' . $matches[1] . '}}';
            },
            $messageTemplate
        );
    
        Log::info("SmsService: Normalized template: \"{$normalizedTemplate}\"");
        
        $unpaidBills = $user->billings()->where('status', 'unpaid')->get();
        $billingSettings = \App\Models\BillingSetting::all()->keyBy('utility_type');
        $today = Carbon::today();
    
        foreach ($unpaidBills as $bill) {
            $originalDueDate = Carbon::parse($bill->due_date);
            $bill->current_amount_due = $bill->amount;
    
            if ($today->gt($originalDueDate)) {
                $settings = $billingSettings->get($bill->utility_type);
                if ($bill->utility_type === 'Rent' && $settings) {
                    $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                    $surcharge = $bill->amount * ($settings->surcharge_rate ?? 0);
                    $interest = $bill->amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
                    $bill->current_amount_due = $bill->amount + $surcharge + $interest;
                } else if ($settings) {
                    $penalty = $bill->amount * ($settings->penalty_rate ?? 0);
                    $bill->current_amount_due = $bill->amount + $penalty;
                }
            }
        }

        list($upcomingBills, $overdueBills) = $unpaidBills->partition(function ($bill) use ($today) {
            return Carbon::parse($bill->due_date) >= $today;
        });
    
        $formatDetails = function ($bills) {
            if ($bills->isEmpty()) {
                return 'None';
            }
            return $bills->map(function ($bill) {
                $amount = number_format($bill->current_amount_due, 2);
                $dueDate = Carbon::parse($bill->due_date)->format('M d');
                return "{$bill->utility_type} (due {$dueDate}): P{$amount}";
            })->implode(', ');
        };
    
        
        $upcomingDetailsString = $formatDetails($upcomingBills);
        $overdueDetailsString = $formatDetails($overdueBills);
        
        $totalDue = $unpaidBills->sum('current_amount_due');
        $earliestUpcomingBill = $upcomingBills->sortBy('due_date')->first();
        $timestamp = Carbon::now('Asia/Manila')->format('M d, Y h:i A');
    
        $itemsForSummary = collect();
        switch ($templateName) {
            case 'overdue_alert':
                $itemsForSummary = $unpaidBills->where('due_date', '<', $today);
                break;
            case 'payment_reminder': // Group this with the default
            case 'bill_statement':
            default:
                // For reminders and statements, list all unpaid bills.
                $itemsForSummary = $unpaidBills;
                break;
        }
    
        $billDetailsString = '';
        if ($itemsForSummary->isNotEmpty()) {
            $details = [];
            foreach ($itemsForSummary as $bill) {
                $amount = number_format($bill->current_amount_due, 2); 
                $dueDate = Carbon::parse($bill->due_date)->format('M d');
                $details[] = "{$bill->utility_type} (due {$dueDate}): P{$amount}";
            }
            $billDetailsString = implode(', ', $details);
        }
        
        $totalDue = $itemsForSummary->sum('current_amount_due');
        $earliestDueDateBill = $itemsForSummary->sortBy('due_date')->first();
        $timestamp = Carbon::now('Asia/Manila')->format('M d, Y h:i A');
    
        $replacements = [
            '{{vendor_name}}'   => $user->name,
            '{{stall_number}}'  => $user->stall->table_number ?? 'N/A',
            '{{total_due}}'     => number_format($totalDue, 2),
            '{{due_date}}'      => $earliestDueDateBill ? Carbon::parse($earliestDueDateBill->due_date)->format('M d, Y') : 'N/A',
            '{{bill_details}}'  => $billDetailsString,
            '{{unpaid_items}}'  => $itemsForSummary->pluck('utility_type')->implode(', '),
            '{{overdue_items}}' => $itemsForSummary->pluck('utility_type')->implode(', '),
            '{{new_total_due}}' => number_format($totalDue, 2),
            '{{upcoming_bill_details}}' => $upcomingDetailsString,
            '{{overdue_bill_details}}'  => $overdueDetailsString,
            '{{timestamp}}'     => $timestamp,
        ];
    
        $replacements = array_merge($replacements, $extraData);
        $finalMessage = str_replace(array_keys($replacements), array_values($replacements), $normalizedTemplate);
        
        Log::info("SmsService: Final message for user {$user->id}: \"{$finalMessage}\"");
    
        $recipientNumber = $user->getSemaphoreReadyContactNumber();
        if (!$recipientNumber) {
            Log::warning("Skipping SMS for user {$user->id} due to missing contact number.");
            return ['success' => false, 'message' => 'No contact number.'];
        }
    
        return $this->send($recipientNumber, $finalMessage);
    }
}