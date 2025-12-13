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
            Log::error('Semaphore API key is not set.');
            throw new \Exception("Semaphore API key is missing.");
        }
    }

    public function send($recipientNumber, $message, $priority = false)
    {
        $endpoint = $priority ? '/priority' : '/messages';
        $url = $this->baseUrl . $endpoint;

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
            
            // Log the full response for debugging
            Log::info("Semaphore API Response", [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_json' => $response->json(),
                'recipient' => $recipientNumber,
                'message_length' => strlen($message)
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if Semaphore actually accepted the message
                // Semaphore returns array with message_id on success, or error message on failure
                if (isset($responseData[0]) && isset($responseData[0]['message_id'])) {
                    Log::info("SMS sent to {$recipientNumber} via Semaphore.", [
                        'message_id' => $responseData[0]['message_id'],
                        'response' => $responseData
                    ]);
                    return ['success' => true, 'response' => $responseData, 'message_id' => $responseData[0]['message_id']];
                } else {
                    // Semaphore might return success status but with error in body
                    $errorMsg = $responseData[0]['message'] ?? $response->body() ?? 'Unknown error';
                    Log::error("Semaphore returned success but message not sent", [
                        'recipient' => $recipientNumber,
                        'error' => $errorMsg,
                        'full_response' => $responseData
                    ]);
                    return ['success' => false, 'message' => $errorMsg, 'response' => $responseData];
                }
            } else {
                Log::error("Failed to send SMS to {$recipientNumber} via Semaphore.", [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['success' => false, 'response' => $response->body(), 'status_code' => $response->status()];
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
        // Cache credits for 5 minutes to avoid rate limiting
        $cacheKey = 'semaphore_credits';
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }

        $url = $this->baseUrl . '/account?apikey=' . $this->apiKey;

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $result = ['success' => true, 'credit_balance' => $data['credit_balance'] ?? 'N/A'];
                
                // Cache successful response for 5 minutes
                \Illuminate\Support\Facades\Cache::put($cacheKey, $result, now()->addMinutes(5));
                
                return $result;
            } else {
                $responseBody = $response->body();
                Log::error("Failed to fetch Semaphore credits.", ['response' => $responseBody, 'status' => $response->status()]);
                
                // If rate limited, return cached value if available, otherwise return error
                if (str_contains($responseBody, 'Too Many Attempts') || $response->status() === 429) {
                    $oldCache = \Illuminate\Support\Facades\Cache::get($cacheKey . '_fallback');
                    if ($oldCache) {
                        return $oldCache;
                    }
                    return ['success' => false, 'message' => 'Rate limit exceeded. Please try again in a few minutes.', 'rate_limited' => true];
                }
                
                return ['success' => false, 'message' => 'Failed to fetch credits.'];
            }
        } catch (\Exception $e) {
            Log::error('Semaphore credit fetch failed: ' . $e->getMessage(), ['exception' => $e]);
            
            // Return cached value if available
            $oldCache = \Illuminate\Support\Facades\Cache::get($cacheKey . '_fallback');
            if ($oldCache) {
                return $oldCache;
            }
            
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
    
        // Ensure stall relationship is loaded
        if (!$user->relationLoaded('stall')) {
            $user->load('stall');
        }
    
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
                return '{{' . trim($matches[1]) . '}}';
            },
            $messageTemplate
        );
    
        Log::info("SmsService: Normalized template: \"{$normalizedTemplate}\"");
        
        $unpaidBills = $user->billings()
            ->where('billing.status', 'unpaid')
            ->select('billing.id', 'billing.stall_id', 'billing.utility_type', 'billing.period_start', 'billing.period_end', 'billing.amount', 'billing.due_date', 'billing.disconnection_date', 'billing.status', 'billing.consumption', 'billing.current_reading', 'billing.previous_reading', 'billing.rate')
            ->get();
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
                $itemsForSummary = $unpaidBills->where(function($bill) use ($today) {
                    return Carbon::parse($bill->due_date)->lt($today);
                });
                break;
            case 'payment_reminder': // Group this with the default
            case 'bill_statement':
            default:
                // For reminders and statements, list all unpaid bills.
                $itemsForSummary = $unpaidBills;
                break;
        }
        
        Log::info("SmsService: Items for summary", [
            'template_name' => $templateName,
            'unpaid_bills_count' => $unpaidBills->count(),
            'items_for_summary_count' => $itemsForSummary->count(),
            'unpaid_bills_types' => $unpaidBills->pluck('utility_type')->toArray()
        ]);
    
        Log::info("SmsService: Bills summary", [
            'template_name' => $templateName,
            'total_unpaid_bills' => $unpaidBills->count(),
            'items_for_summary_count' => $itemsForSummary->count(),
            'utility_types' => $itemsForSummary->pluck('utility_type')->toArray()
        ]);
    
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
        
        // Get bill month - use current month for billing statements
        // This ensures the bill month reflects when the statement is being sent, not when old bills were created
        $billMonthFormatted = Carbon::now()->format('F Y');
        
        // Separate bills by type - use case-insensitive matching
        // Get the MOST RECENT unpaid bill for each utility type (sorted by period_start DESC)
        // Search in itemsForSummary first, then fallback to all unpaid bills
        $rentBill = $itemsForSummary->filter(function ($bill) {
            return strtolower(trim($bill->utility_type)) === 'rent';
        })->sortByDesc('period_start')->first();
        if (!$rentBill) {
            $rentBill = $unpaidBills->filter(function ($bill) {
                return strtolower(trim($bill->utility_type)) === 'rent';
            })->sortByDesc('period_start')->first();
        }
        
        $waterBill = $itemsForSummary->filter(function ($bill) {
            return strtolower(trim($bill->utility_type)) === 'water';
        })->sortByDesc('period_start')->first();
        if (!$waterBill) {
            $waterBill = $unpaidBills->filter(function ($bill) {
                return strtolower(trim($bill->utility_type)) === 'water';
            })->sortByDesc('period_start')->first();
        }
        
        $electricityBill = $itemsForSummary->filter(function ($bill) {
            return strtolower(trim($bill->utility_type)) === 'electricity';
        })->sortByDesc('period_start')->first();
        if (!$electricityBill) {
            $electricityBill = $unpaidBills->filter(function ($bill) {
                return strtolower(trim($bill->utility_type)) === 'electricity';
            })->sortByDesc('period_start')->first();
        }
        
        Log::info("SmsService: Bill details found", [
            'rent_bill' => $rentBill ? ['id' => $rentBill->id, 'amount' => $rentBill->amount] : null,
            'water_bill' => $waterBill ? ['id' => $waterBill->id, 'amount' => $waterBill->amount] : null,
            'electricity_bill' => $electricityBill ? ['id' => $electricityBill->id, 'amount' => $electricityBill->amount] : null,
            'bill_month_formatted' => $billMonthFormatted
        ]);
        
        // Format Rent details
        $rentDetails = 'N/A';
        if ($rentBill) {
            $rentOriginal = number_format($rentBill->amount, 2);
            $rentDueDate = Carbon::parse($rentBill->due_date)->format('M d, Y');
            
            // Calculate discount if applicable (if today is <= 15th and bill is for current month)
            $rentSettings = $billingSettings->get('Rent');
            $rentDiscount = 0;
            $todayDay = $today->day;
            $rentBillMonth = Carbon::parse($rentBill->period_start)->format('Y-m');
            $currentMonth = $today->format('Y-m');
            
            if ($todayDay <= 15 && $rentBillMonth === $currentMonth && $rentSettings && (float)$rentSettings->discount_rate > 0) {
                // Discount calculation: Original Price - (Original Price * discount_rate)
                // Equivalent to: Original Price * (1 - discount_rate)
                $rentDiscount = $rentBill->amount * (float)$rentSettings->discount_rate;
            }
            
            $rentDiscounted = $rentBill->amount - $rentDiscount;
            
            if ($rentDiscount > 0) {
                $rentDetails = "Original: P{$rentOriginal}\nDiscounted: P" . number_format($rentDiscounted, 2) . "\nDue: {$rentDueDate}";
            } else {
                $rentDetails = "Amount: P{$rentOriginal}\nDue: {$rentDueDate}";
            }
        }
        
        // Format Water details
        $waterDetails = 'N/A';
        if ($waterBill) {
            $waterAmount = number_format($waterBill->current_amount_due, 2);
            $waterDueDate = Carbon::parse($waterBill->due_date)->format('M d, Y');
            $waterDetails = "Amount: P{$waterAmount}\nDue: {$waterDueDate}";
        }
        
        // Format Electricity details
        $electricityDetails = 'N/A';
        if ($electricityBill) {
            $elecOriginalAmount = (float)$electricityBill->amount;
            $elecConsumption = $electricityBill->consumption ?? 0;
            $elecRate = $electricityBill->rate ?? 0;
            
            // If consumption is missing, calculate from readings
            if ($elecConsumption == 0 && $electricityBill->current_reading && $electricityBill->previous_reading) {
                $elecConsumption = (float)$electricityBill->current_reading - (float)$electricityBill->previous_reading;
            }
            
            // If rate is missing but we have amount and consumption, calculate rate
            if ($elecRate == 0 && $elecConsumption > 0 && $elecOriginalAmount > 0) {
                $elecRate = $elecOriginalAmount / $elecConsumption;
            }
            // If consumption is missing but we have amount and rate, calculate consumption
            else if ($elecConsumption == 0 && $elecRate > 0 && $elecOriginalAmount > 0) {
                $elecConsumption = $elecOriginalAmount / $elecRate;
            }
            
            $elecCalculatedAmount = $elecConsumption * $elecRate;
            $elecAmountToPay = number_format($electricityBill->current_amount_due, 2);
            $elecDueDate = Carbon::parse($electricityBill->due_date)->format('M d, Y');
            $elecDisconnectionDate = $electricityBill->disconnection_date 
                ? Carbon::parse($electricityBill->disconnection_date)->format('M d, Y') 
                : 'N/A';
            
            // Format calculation: show the formula with actual values
            $elecCalculation = "(" . number_format($elecConsumption, 2) . " kWh) x P" . number_format($elecRate, 2) . " = P" . number_format($elecCalculatedAmount > 0 ? $elecCalculatedAmount : $elecOriginalAmount, 2);
            $electricityDetails = "Calculation: {$elecCalculation}\nAmount to Pay: P{$elecAmountToPay}\nDue: {$elecDueDate}\nDisconnection: {$elecDisconnectionDate}";
        }
        
        // Website URL
        $websiteUrl = url('/vendor/home');
    
        // Ensure all values are strings (not null)
        $replacements = [
            '{{vendor_name}}'   => $user->name ?? 'N/A',
            '{{stall_number}}'  => ($user->stall->table_number ?? 'N/A'),
            '{{total_due}}'     => number_format($totalDue, 2),
            '{{due_date}}'      => $earliestDueDateBill ? Carbon::parse($earliestDueDateBill->due_date)->format('M d, Y') : 'N/A',
            '{{bill_details}}'  => $billDetailsString ?: 'None',
            '{{unpaid_items}}'  => $itemsForSummary->pluck('utility_type')->implode(', ') ?: 'None',
            '{{overdue_items}}' => $itemsForSummary->pluck('utility_type')->implode(', ') ?: 'None',
            '{{new_total_due}}' => number_format($totalDue, 2),
            '{{upcoming_bill_details}}' => $upcomingDetailsString ?: 'None',
            '{{overdue_bill_details}}'  => $overdueDetailsString ?: 'None',
            '{{timestamp}}'     => $timestamp,
            '{{bill_month}}'    => $billMonthFormatted ?: Carbon::now()->format('F Y'),
            '{{rent_details}}'  => $rentDetails ?: 'N/A',
            '{{water_details}}' => $waterDetails ?: 'N/A',
            '{{electricity_details}}' => $electricityDetails ?: 'N/A',
            '{{website_url}}'   => $websiteUrl ?: url('/vendor/home'),
        ];
        
        Log::info("SmsService: Replacement values check", [
            'bill_month' => $replacements['{{bill_month}}'],
            'rent_details_length' => strlen($replacements['{{rent_details}}']),
            'water_details_length' => strlen($replacements['{{water_details}}']),
            'electricity_details_length' => strlen($replacements['{{electricity_details}}']),
            'rent_details_preview' => substr($replacements['{{rent_details}}'], 0, 100),
            'water_details_preview' => substr($replacements['{{water_details}}'], 0, 100),
            'electricity_details_preview' => substr($replacements['{{electricity_details}}'], 0, 100),
        ]);
        
        Log::info("SmsService: Replacement values", [
            'bill_month' => $replacements['{{bill_month}}'],
            'rent_details' => substr($replacements['{{rent_details}}'], 0, 50),
            'water_details' => substr($replacements['{{water_details}}'], 0, 50),
            'electricity_details' => substr($replacements['{{electricity_details}}'], 0, 50),
        ]);
    
        $replacements = array_merge($replacements, $extraData);
        
        // Debug: Log what placeholders we're trying to replace
        Log::info("SmsService: Placeholders to replace", [
            'placeholders' => array_keys($replacements),
            'normalized_template_sample' => substr($normalizedTemplate, 0, 200)
        ]);
        
        $finalMessage = str_replace(array_keys($replacements), array_values($replacements), $normalizedTemplate);
        
        // Debug: Check if any placeholders remain unreplaced
        $unreplacedPlaceholders = [];
        preg_match_all('/{{[^}]+}}/', $finalMessage, $matches);
        if (!empty($matches[0])) {
            $unreplacedPlaceholders = array_unique($matches[0]);
            Log::warning("SmsService: Unreplaced placeholders found", [
                'unreplaced' => $unreplacedPlaceholders,
                'user_id' => $user->id
            ]);
        }
        
        Log::info("SmsService: Final message for user {$user->id}: \"{$finalMessage}\"");
    
        $recipientNumber = $user->getSemaphoreReadyContactNumber();
        if (!$recipientNumber) {
            Log::warning("Skipping SMS for user {$user->id} ({$user->name}) due to invalid/missing contact number.", [
                'contact_number' => $user->contact_number,
                'user_id' => $user->id
            ]);
            return ['success' => false, 'message' => 'No valid contact number. Contact number format: ' . ($user->contact_number ?? 'NULL')];
        }

        // Log the final message length and preview
        Log::info("SmsService: Sending SMS to user {$user->id}", [
            'recipient' => $recipientNumber,
            'message_length' => strlen($finalMessage),
            'message_preview' => substr($finalMessage, 0, 200),
            'full_message' => $finalMessage
        ]);

        $result = $this->send($recipientNumber, $finalMessage);
        
        if (!$result['success']) {
            Log::error("SmsService: Failed to send SMS to user {$user->id}", [
                'recipient' => $recipientNumber,
                'error' => $result['message'] ?? $result['response'] ?? 'Unknown error',
                'status_code' => $result['status_code'] ?? null
            ]);
        } else {
            Log::info("SmsService: SMS successfully sent to user {$user->id}", [
                'recipient' => $recipientNumber,
                'message_id' => $result['message_id'] ?? 'N/A',
                'response' => $result['response'] ?? []
            ]);
            
            // Store SMS message in notifications table for tracking
            try {
                $adminUser = \App\Models\User::whereHas('role', function($query) {
                    $query->where('name', 'Admin');
                })->first();
                
                $smsTitle = $this->getSmsTitle($templateName);
                
                DB::table('notifications')->insert([
                    'recipient_id' => $user->id,
                    'sender_id' => $adminUser ? $adminUser->id : null,
                    'channel' => 'sms',
                    'title' => $smsTitle,
                    'message' => json_encode([
                        'text' => $finalMessage,
                        'type' => $templateName,
                        'template_name' => $templateName,
                    ]),
                    'status' => 'sent',
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to store SMS notification: " . $e->getMessage());
            }
        }
        
        return $result;
    }

    /**
     * Get SMS title based on template name
     */
    private function getSmsTitle(string $templateName): string
    {
        $titles = [
            'bill_statement' => 'Bill Statement',
            'payment_reminder' => 'Payment Reminder',
            'overdue_alert' => 'Overdue Bill Alert',
        ];
        
        return $titles[$templateName] ?? 'SMS Notification';
    }
}