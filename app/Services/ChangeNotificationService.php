<?php

namespace App\Services;

use App\Models\User;
use App\Models\Stall;
use App\Models\Billing;
use App\Models\BillingSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ChangeNotificationService
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send rate change notification
     * @param string $utilityType Water or Electricity
     * @param float $oldRate Old rate value
     * @param float $newRate New rate value
     * @param float|null $oldMonthlyRate Old monthly rate (optional)
     * @param float|null $newMonthlyRate New monthly rate (optional)
     * @param string|null $effectivityDate Effectivity date in Y-m-d format (optional, defaults to today)
     */
    public function sendRateChangeNotification($utilityType, $oldRate, $newRate, $oldMonthlyRate = null, $newMonthlyRate = null, $effectivityDate = null)
    {
        try {
            $recipients = $this->getRecipientsForUtilityRate($utilityType);
            
            // Calculate current month bill amounts for each vendor
            $currentMonth = Carbon::now()->format('Y-m');
            $currentMonthStart = Carbon::now()->startOfMonth();
            
            // Use provided effectivity date or default to today
            $effectiveDate = $effectivityDate ? Carbon::parse($effectivityDate) : Carbon::now();
            
            $message = $this->buildRateChangeMessage($utilityType, $oldRate, $newRate, $oldMonthlyRate, $newMonthlyRate, $effectiveDate);
            
            // Create in-app notifications (runs in background)
            $this->createInAppNotifications($recipients, "Rate Change: {$utilityType}", $message);
            
            // Regenerate bills for current month (runs in background)
            $this->regenerateCurrentMonthBills();
            
            // Send SMS in background using fast_exec
            $this->sendSmsInBackground($recipients, $message, $utilityType, $newRate, $newMonthlyRate);
            
            // Return immediately without waiting for SMS
            return ['success' => true, 'sent' => $recipients->count(), 'failed' => 0, 'processing' => 'background'];
        } catch (\Exception $e) {
            Log::error("Error sending rate change notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send SMS and store immediately (synchronous) to ensure storage works on cloud
     * Storage is fast enough that blocking for a few milliseconds is acceptable
     */
    private function sendSmsInBackground($recipients, $baseMessage, $utilityType = null, $newRate = null, $newMonthlyRate = null)
    {
        // Store admin user ID before processing
        $adminUser = \App\Models\User::whereHas('role', function($query) {
            $query->where('name', 'Admin');
        })->first();
        $adminUserId = $adminUser ? $adminUser->id : null;
        $smsTitle = $this->getSmsTitleForChange($baseMessage);
        
        // Use register_shutdown_function for sending (non-blocking) but store immediately after send
        register_shutdown_function(function() use ($recipients, $baseMessage, $utilityType, $newRate, $newMonthlyRate, $adminUserId, $smsTitle) {
            try {
                $successCount = 0;
                $failCount = 0;
                
                foreach ($recipients as $user) {
                    $contactNumber = $user->getSemaphoreReadyContactNumber();
                    if (!$contactNumber) {
                        $failCount++;
                        continue;
                    }
                    
                    $personalizedMessage = $baseMessage;
                    
                    // Get current month bill if applicable
                    if ($utilityType) {
                        $currentBill = $this->getCurrentMonthBill($user, $utilityType, $newRate, $newMonthlyRate);
                        if ($currentBill) {
                            $personalizedMessage .= "\n\nBayadan sa bulan na ini: ₱" . number_format($currentBill, 2);
                        }
                    }
                    
                    $personalizedMessage .= "\n\n- Virac Public Market";
                    
                    // Store SMS message immediately (synchronous) to ensure it works on cloud
                    // Pass metadata to send() method so it can store if needed
                    $result = $this->smsService->send($contactNumber, $personalizedMessage, false, [
                        'store' => true,
                        'title' => $smsTitle,
                        'recipient_id' => $user->id,
                        'type' => 'change_notification'
                    ]);
                    
                    if ($result['success']) {
                        $successCount++;
                        
                        // Also store directly here as backup (in case send() method doesn't store)
                        try {
                            $notificationId = DB::table('notifications')->insertGetId([
                                'recipient_id' => $user->id,
                                'sender_id' => $adminUserId,
                                'channel' => 'sms',
                                'title' => $smsTitle,
                                'message' => json_encode([
                                    'text' => $personalizedMessage,
                                    'type' => 'change_notification',
                                ]),
                                'status' => 'sent',
                                'sent_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            Log::info("Change notification SMS stored successfully (backup)", [
                                'notification_id' => $notificationId,
                                'user_id' => $user->id,
                                'title' => $smsTitle
                            ]);
                        } catch (\Exception $e) {
                            // Ignore duplicate key errors (if send() already stored it)
                            if (strpos($e->getMessage(), 'Duplicate') === false) {
                                Log::error("Failed to store SMS notification in ChangeNotificationService", [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                    'user_id' => $user->id
                                ]);
                            }
                        }
                    } else {
                        $failCount++;
                        Log::warning("Failed to send SMS to user {$user->id}: {$result['message']}");
                    }
                }
                
                Log::info("SMS sent in background: {$successCount} successful, {$failCount} failed");
            } catch (\Exception $e) {
                Log::error("Error sending SMS in background: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    /**
     * Send rental rate change notification
     * @param object $stall Stall object with id and table_number
     * @param float $oldDailyRate Old daily rate
     * @param float $newDailyRate New daily rate
     * @param float|null $oldMonthlyRate Old monthly rate (optional)
     * @param float|null $newMonthlyRate New monthly rate (optional)
     * @param string|null $effectivityDate Effectivity date in Y-m-d format (optional, defaults to today)
     */
    public function sendRentalRateChangeNotification($stall, $oldDailyRate, $newDailyRate, $oldMonthlyRate = null, $newMonthlyRate = null, $effectivityDate = null)
    {
        try {
            $recipients = $this->getRecipientsForRentalRate($stall);
            
            // Use provided effectivity date or default to today
            $effectiveDate = $effectivityDate ? Carbon::parse($effectivityDate) : Carbon::now();
            
            // Log the effectivity date being used
            Log::info('Building rental rate change message', [
                'stall' => $stall->table_number ?? 'unknown',
                'effectivity_date_param' => $effectivityDate,
                'effective_date_parsed' => $effectiveDate->format('Y-m-d'),
                'effective_date_formatted' => $effectiveDate->format('F d, Y')
            ]);
            
            $message = $this->buildRentalRateChangeMessage($stall, $oldDailyRate, $newDailyRate, $oldMonthlyRate, $newMonthlyRate, $effectiveDate);
            
            // Log the built message to verify the date is correct
            Log::info('Rental rate change message built', [
                'stall' => $stall->table_number ?? 'unknown',
                'message_preview' => substr($message, 0, 200),
                'contains_effectivity' => strpos($message, 'Epektibo sa:') !== false
            ]);
            
            // Create in-app notifications (runs in background)
            $this->createInAppNotifications($recipients, "Rental Rate Change: Stall {$stall->table_number}", $message);
            
            // Regenerate bills for current month (runs in background)
            $this->regenerateCurrentMonthBills();
            
            // Send SMS in background
            // Store admin user ID and title before shutdown function
            $adminUser = \App\Models\User::whereHas('role', function($query) {
                $query->where('name', 'Admin');
            })->first();
            $adminUserId = $adminUser ? $adminUser->id : null;
            $smsTitle = 'Rental Rate Change Notification';
            
            register_shutdown_function(function() use ($recipients, $message, $newMonthlyRate, $adminUserId, $smsTitle) {
                try {
                    // Reconnect to database in case connection was closed
                    DB::reconnect();
                    
                    $successCount = 0;
                    $failCount = 0;
                    
                    // Check if today is on or before the 15th for discount eligibility
                    $today = Carbon::today();
                    $isEligibleForDiscount = $today->day <= 15;
                    
                    // Get discount rate for Rent from billing settings
                    $rentBillingSetting = BillingSetting::where('utility_type', 'Rent')->first();
                    $discountRate = $rentBillingSetting ? (float)$rentBillingSetting->discount_rate : 0;
                    
                    foreach ($recipients as $user) {
                        $contactNumber = $user->getSemaphoreReadyContactNumber();
                        if (!$contactNumber) {
                            $failCount++;
                            continue;
                        }
                        
                        $personalizedMessage = $message;
                        
                        // Get original bill amount (base monthly rate)
                        $originalBillAmount = $this->getCurrentMonthBill($user, 'Rent', null, $newMonthlyRate);
                        
                        if ($originalBillAmount) {
                            $personalizedMessage .= "\n\nBayadan sa bulan na ini: ₱" . number_format($originalBillAmount, 2);
                            
                            // Calculate and add discounted amount if eligible (on or before 15th) and discount rate exists
                            if ($isEligibleForDiscount && $discountRate > 0) {
                                // Discount is calculated on the original amount: originalAmount * discount_rate
                                $discountedAmount = $originalBillAmount * $discountRate;
                                $finalAmount = $originalBillAmount - $discountedAmount;
                                $personalizedMessage .= "\nDiscounted na bayadan: " . number_format($originalBillAmount, 2) . " - ₱" . number_format($discountedAmount, 2) . " = ₱" . number_format($finalAmount, 2) . " (the difference)";
                            }
                        }
                        $personalizedMessage .= "\n\n- Virac Public Market";
                        
                        // Store SMS message immediately (synchronous)
                        $result = $this->smsService->send($contactNumber, $personalizedMessage, false, [
                            'store' => true,
                            'title' => $smsTitle,
                            'recipient_id' => $user->id,
                            'type' => 'rental_rate_change'
                        ]);
                        
                        if ($result['success']) {
                            $successCount++;
                            
                            // Also store directly as backup
                            try {
                                $notificationId = DB::table('notifications')->insertGetId([
                                    'recipient_id' => $user->id,
                                    'sender_id' => $adminUserId,
                                    'channel' => 'sms',
                                    'title' => $smsTitle,
                                    'message' => json_encode([
                                        'text' => $personalizedMessage,
                                        'type' => 'rental_rate_change',
                                    ]),
                                    'status' => 'sent',
                                    'sent_at' => now(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                
                                Log::info("Rental rate change SMS stored successfully (backup)", [
                                    'notification_id' => $notificationId,
                                    'user_id' => $user->id
                                ]);
                            } catch (\Exception $e) {
                                // Ignore duplicate key errors
                                if (strpos($e->getMessage(), 'Duplicate') === false) {
                                    Log::error("Failed to store rental rate SMS notification", [
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString(),
                                        'user_id' => $user->id
                                    ]);
                                }
                            }
                        } else {
                            $failCount++;
                            Log::warning("Failed to send rental rate change SMS to user {$user->id}: {$result['message']}");
                        }
                    }
                    
                    Log::info("Rental rate change SMS sent: {$successCount} successful, {$failCount} failed");
                } catch (\Exception $e) {
                    Log::error("Error sending rental rate change SMS: " . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            });
            
            return ['success' => true, 'sent' => $recipients->count(), 'failed' => 0, 'processing' => 'background'];
        } catch (\Exception $e) {
            Log::error("Error sending rental rate change notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send schedule change notification
     * @param string $scheduleType Type of schedule (Due Date, Disconnection, Meter Reading)
     * @param string $utilityType Water, Electricity, or Rent
     * @param int|string $oldDay Old day value
     * @param int|string $newDay New day value
     * @param string|null $effectivityDate Effectivity date in Y-m-d format (optional, defaults to today)
     */
    public function sendScheduleChangeNotification($scheduleType, $utilityType, $oldDay, $newDay, $effectivityDate = null)
    {
        try {
            $recipients = $this->getRecipientsForSchedule($scheduleType, $utilityType);
            
            // Use provided effectivity date or default to today
            $effectiveDate = $effectivityDate ? Carbon::parse($effectivityDate) : Carbon::now();
            
            $message = $this->buildScheduleChangeMessage($scheduleType, $utilityType, $oldDay, $newDay, $effectiveDate);
            
            // Create in-app notifications (runs in background)
            $this->createInAppNotifications($recipients, "Schedule Change: {$scheduleType}", $message);
            
            // Regenerate bills for current month if disconnection date or due date changed
            // This ensures outstanding balances reflect the new dates
            if (str_contains($scheduleType, 'Disconnection') || str_contains($scheduleType, 'Due Date')) {
                $this->regenerateCurrentMonthBills();
            }
            
            // Send SMS in background (sendSmsInBackground will add "- Virac Public Market")
            $this->sendSmsInBackground($recipients, $message);
            
            return ['success' => true, 'sent' => $recipients->count(), 'failed' => 0, 'processing' => 'background'];
        } catch (\Exception $e) {
            Log::error("Error sending schedule change notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send billing setting change notification
     * @param string $utilityType Water, Electricity, or Rent
     * @param string $settingName Setting name (surcharge_rate, penalty_rate, etc.)
     * @param float $oldValue Old value (as decimal, e.g., 0.05 for 5%)
     * @param float $newValue New value (as decimal, e.g., 0.07 for 7%)
     * @param string|null $effectivityDate Effectivity date in Y-m-d format (optional, defaults to today)
     */
    public function sendBillingSettingChangeNotification($utilityType, $settingName, $oldValue, $newValue, $effectivityDate = null)
    {
        try {
            $recipients = $this->getRecipientsForBillingSetting($utilityType);
            
            // Use provided effectivity date or default to today
            $effectiveDate = $effectivityDate ? Carbon::parse($effectivityDate) : Carbon::now();
            
            $message = $this->buildBillingSettingChangeMessage($utilityType, $settingName, $oldValue, $newValue, $effectiveDate);
            
            // Create in-app notifications (runs in background)
            $this->createInAppNotifications($recipients, "Billing Setting Change: {$utilityType}", $message);
            
            // Regenerate bills for current month (runs in background)
            $this->regenerateCurrentMonthBills();
            
            // Send SMS in background
            $this->sendSmsInBackground($recipients, $message);
            
            return ['success' => true, 'sent' => $recipients->count(), 'failed' => 0, 'processing' => 'background'];
        } catch (\Exception $e) {
            Log::error("Error sending billing setting change notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get recipients for utility rate changes
     */
    private function getRecipientsForUtilityRate($utilityType)
    {
        $recipients = collect();
        
        // Get all vendors using this utility
        if ($utilityType === 'Water') {
            $vendors = User::vendors()
                ->active()
                ->whereNotNull('contact_number')
                ->whereHas('stall.section', function($query) {
                    $query->whereIn('name', ['Wet Section', 'Wet']);
                })
                ->get();
        } else {
            // Electricity - all vendors
            $vendors = User::vendors()
                ->active()
                ->whereNotNull('contact_number')
                ->whereHas('stall')
                ->get();
        }
        
        $recipients = $recipients->merge($vendors);
        
        // Add staff
        $staff = User::whereHas('role', function($query) {
            $query->where('name', 'Staff');
        })
            ->whereNotNull('contact_number')
            ->get();
        $recipients = $recipients->merge($staff);
        
        // Add meter-reader-clerk for electricity
        if ($utilityType === 'Electricity') {
            $meterReaderClerks = User::whereHas('role', function($query) {
                $query->where('name', 'Meter Reader Clerk');
            })
                ->whereNotNull('contact_number')
                ->get();
            $recipients = $recipients->merge($meterReaderClerks);
        }
        
        return $recipients->unique('id');
    }

    /**
     * Get recipients for rental rate changes
     */
    private function getRecipientsForRentalRate($stall)
    {
        $recipients = collect();

        // Get the vendor who owns this stall
        // Handle both Eloquent models and stdClass objects
        if (is_object($stall) && (method_exists($stall, 'getAttribute') || isset($stall->vendor))) {
            $vendor = method_exists($stall, 'getAttribute') 
                ? $stall->vendor 
                : (isset($stall->vendor) ? $stall->vendor : null);
            
            if ($vendor) {
                $recipients->push($vendor);
            }
        } elseif (is_object($stall) && isset($stall->id)) {
            // If we have a stall ID but no vendor relationship, try to find the vendor
            try {
                $stallModel = \App\Models\Stall::with('vendor')->find($stall->id);
                if ($stallModel && $stallModel->vendor) {
                    $recipients->push($stallModel->vendor);
                }
            } catch (\Exception $e) {
                Log::warning("Could not load vendor for stall {$stall->id}: " . $e->getMessage());
            }
        }

        // Add staff
        $staff = User::whereHas('role', function($query) {
            $query->where('name', 'Staff');
        })
            ->whereNotNull('contact_number')
            ->get();
        $recipients = $recipients->merge($staff);

        return $recipients->unique('id');
    }

    /**
     * Get recipients for schedule changes
     */
    private function getRecipientsForSchedule($scheduleType, $utilityType)
    {
        $recipients = collect();
        
        // Get vendors affected by this utility type
        if ($utilityType === 'Water') {
            $vendors = User::vendors()
                ->active()
                ->whereNotNull('contact_number')
                ->whereHas('stall.section', function($query) {
                    $query->whereIn('name', ['Wet Section', 'Wet']);
                })
                ->get();
        } else {
            // All vendors for other utilities
            $vendors = User::vendors()
                ->active()
                ->whereNotNull('contact_number')
                ->whereHas('stall')
                ->get();
        }
        
        $recipients = $recipients->merge($vendors);
        
        // Add staff
        $staff = User::whereHas('role', function($query) {
            $query->where('name', 'Staff');
        })
            ->whereNotNull('contact_number')
            ->get();
        $recipients = $recipients->merge($staff);
        
        // Add meter-reader-clerk for meter reading schedule, electricity rate, and disconnection date
        if (str_contains($scheduleType, 'Meter Reading') || 
            str_contains($scheduleType, 'Disconnection') ||
            $utilityType === 'Electricity') {
            $meterReaderClerks = User::whereHas('role', function($query) {
                $query->where('name', 'Meter Reader Clerk');
            })
                ->whereNotNull('contact_number')
                ->get();
            $recipients = $recipients->merge($meterReaderClerks);
        }
        
        return $recipients->unique('id');
    }

    /**
     * Get recipients for billing setting changes
     */
    private function getRecipientsForBillingSetting($utilityType)
    {
        $recipients = collect();
        
        // Get vendors affected by this utility type
        if ($utilityType === 'Water') {
            $vendors = User::vendors()
                ->active()
                ->whereNotNull('contact_number')
                ->whereHas('stall.section', function($query) {
                    $query->whereIn('name', ['Wet Section', 'Wet']);
                })
                ->get();
        } else {
            // All vendors for other utilities
            $vendors = User::vendors()
                ->active()
                ->whereNotNull('contact_number')
                ->whereHas('stall')
                ->get();
        }
        
        $recipients = $recipients->merge($vendors);
        
        // Add staff
        $staff = User::whereHas('role', function($query) {
            $query->where('name', 'Staff');
        })
            ->whereNotNull('contact_number')
            ->get();
        $recipients = $recipients->merge($staff);
        
        return $recipients->unique('id');
    }

    /**
     * Build rate change message
     */
    private function buildRateChangeMessage($utilityType, $oldRate, $newRate, $oldMonthlyRate = null, $newMonthlyRate = null, $effectivityDate = null)
    {
        $unit = $utilityType === 'Electricity' ? 'kWh' : 'day';
        $message = "RATE CHANGE: {$utilityType} rate inupdate.\n";
        $message .= "Bagong rate: ₱" . number_format($newRate, 2) . "/{$unit}";
        
        // Use provided effectivity date or default to today
        $effectiveDate = $effectivityDate ?: Carbon::now();
        $message .= "\nEpektibo sa: " . $effectiveDate->format('F d, Y');
        
        return $message;
    }

    /**
     * Build rental rate change message
     */
    private function buildRentalRateChangeMessage($stall, $oldDailyRate, $newDailyRate, $oldMonthlyRate = null, $newMonthlyRate = null, $effectivityDate = null)
    {
        $message = "RENTAL RATE CHANGE: Stall {$stall->table_number} rate inupdate.\n";
        $message .= "Bagong rate: ₱" . number_format($newDailyRate, 2) . "/day";
        
        // Use provided effectivity date or default to today
        // $effectivityDate should already be a Carbon instance from sendRentalRateChangeNotification
        if ($effectivityDate instanceof Carbon) {
            $effectiveDate = $effectivityDate;
        } elseif ($effectivityDate) {
            // If it's a string, parse it
            $effectiveDate = Carbon::parse($effectivityDate);
        } else {
            // Default to today
            $effectiveDate = Carbon::now();
        }
        
        $message .= "\nEpektibo sa: " . $effectiveDate->format('F d, Y');
        
        // Log to verify correct date is used
        Log::info('buildRentalRateChangeMessage - effectivity date used', [
            'stall' => $stall->table_number ?? 'unknown',
            'effectivityDate_param_type' => gettype($effectivityDate),
            'effectivityDate_param_value' => $effectivityDate instanceof Carbon ? $effectivityDate->format('Y-m-d') : $effectivityDate,
            'effectiveDate_final' => $effectiveDate->format('F d, Y')
        ]);
        
        return $message;
    }

    /**
     * Build schedule change message
     */
    private function buildScheduleChangeMessage($scheduleType, $utilityType, $oldDay, $newDay, $effectivityDate = null)
    {
        // Determine the type of schedule change
        if (str_contains($scheduleType, 'Disconnection')) {
            $message = "BAGONG DISCONNECTION DATE ISKEDYUL: {$utilityType} disconnection date inupdate.\n";
            $message .= "BAGONG DISCONNECTION DATE: Ika-{$newDay} aldaw nin bulan";
        } elseif (str_contains($scheduleType, 'Due Date')) {
            $message = "BAGONG DUE DATE ISKEDYUL: {$utilityType} due date inupdate.\n";
            $message .= "Bagong due date: Ika-{$newDay} aldaw nin bulan";
        } elseif (str_contains($scheduleType, 'Meter Reading')) {
            $message = "BAGONG METER READING ISKEDYL: {$utilityType} meter reading schedule updated.\n";
            $message .= "BAGONG ISKEDYL: Ika-{$newDay} aldaw nin bulan";
        } else {
            // Fallback for unknown schedule types
            $scheduleName = str_replace(['Due Date - ', 'Disconnection - ', 'Meter Reading - '], '', $scheduleType);
            $message = "SCHEDULE CHANGE: {$scheduleName} for {$utilityType} updated.\n";
            $message .= "New schedule: Day {$newDay} of each month";
        }
        
        // Use provided effectivity date or default to today
        $effectiveDate = $effectivityDate ?: Carbon::now();
        $message .= "\nEPEKTIBO: " . $effectiveDate->format('F d, Y');
        
        return $message;
    }

    /**
     * Build billing setting change message
     */
    private function buildBillingSettingChangeMessage($utilityType, $settingName, $oldValue, $newValue, $effectivityDate = null)
    {
        // Map setting names to user-friendly labels
        $settingLabels = [
            'surcharge_rate' => 'Surcharge Rate',
            'monthly_interest_rate' => 'Monthly Interest Rate',
            'penalty_rate' => 'Penalty Rate',
            'discount_rate' => 'Discount Rate',
        ];
        
        $settingDisplay = $settingLabels[$settingName] ?? ucwords(str_replace('_', ' ', $settingName));
        $message = "BAGONG BILLING SETTING : {$settingDisplay} para sa {$utilityType} inupdate.\n";
        $message .= "Lumang value: " . number_format($oldValue * 100, 2) . "%\n";
        $message .= "Bagong value: " . number_format($newValue * 100, 2) . "%";
        
        // Use provided effectivity date or default to today
        $effectiveDate = $effectivityDate ?: Carbon::now();
        $message .= "\nEpektibo: " . $effectiveDate->format('F d, Y');
        
        return $message;
    }

    /**
     * Get current month bill amount for a vendor with new rate
     */
    private function getCurrentMonthBill($user, $utilityType, $newRate = null, $newMonthlyRate = null)
    {
        if (!$user->stall) {
            return null;
        }
        
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthStart = Carbon::now()->startOfMonth();
        
        if ($utilityType === 'Rent') {
            // For rent, use the new monthly rate if provided
            if ($newMonthlyRate !== null) {
                return (float) $newMonthlyRate;
            }
            
            // Otherwise get from current bill
            $bill = Billing::where('stall_id', $user->stall->id)
                ->where('utility_type', 'Rent')
                ->where('period_start', $currentMonthStart->toDateString())
                ->first();
            
            return $bill ? (float) $bill->amount : null;
        } else {
            // For utilities, calculate with new rate
            if ($utilityType === 'Water') {
                // For Water: days_in_current_month × new_rate
                if ($newRate !== null) {
                    $daysInCurrentMonth = Carbon::now()->daysInMonth;
                    return (float) ($daysInCurrentMonth * $newRate);
                }
            } else if ($utilityType === 'Electricity') {
                // For Electricity: Try to get current month bill first, then previous month
                $currentMonthStart = Carbon::now()->startOfMonth();
                $bill = Billing::where('stall_id', $user->stall->id)
                    ->where('utility_type', 'Electricity')
                    ->where('period_start', $currentMonthStart->toDateString())
                    ->first();
                
                // If no current month bill, try previous month
                if (!$bill) {
                    $bill = Billing::where('stall_id', $user->stall->id)
                        ->where('utility_type', 'Electricity')
                        ->whereYear('period_start', Carbon::now()->year)
                        ->whereMonth('period_start', Carbon::now()->subMonth()->month)
                        ->first();
                }
                
                if ($bill && $newRate !== null) {
                    // Get consumption from bill
                    $consumption = $bill->consumption ?? ($bill->current_reading - $bill->previous_reading);
                    if ($consumption > 0) {
                        return (float) ($consumption * $newRate);
                    }
                }
            }
            
            return null;
        }
    }

    /**
     * Create in-app notifications for recipients
     * Runs in background to avoid blocking HTTP response
     */
    private function createInAppNotifications($recipients, $title, $message)
    {
        // Use register_shutdown_function to run after HTTP response is sent
        register_shutdown_function(function() use ($recipients, $title, $message) {
            try {
                // Get admin user - check if role is a column or relationship
                $adminUser = User::whereHas('role', function($query) {
                    $query->where('name', 'Admin');
                })->first();
                
                if (!$adminUser) {
                    // Fallback: try direct column if role is a column
                    $adminUser = User::where('role', 'Admin')->first();
                }
                
                $senderId = $adminUser ? $adminUser->id : null;
                $now = now();

                // Prepare notification data
                $notificationData = [];
                foreach ($recipients as $user) {
                    $notificationData[] = [
                        'recipient_id' => $user->id,
                        'sender_id' => $senderId,
                        'channel' => 'in_app',
                        'title' => $title,
                        'message' => json_encode([
                            'text' => $message,
                            'type' => 'rate_change',
                        ]),
                        'status' => 'sent',
                        'sent_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Insert notifications in batches for better performance
                if (!empty($notificationData)) {
                    // Insert in chunks of 100 for better performance
                    $chunks = array_chunk($notificationData, 100);
                    foreach ($chunks as $chunk) {
                        DB::table('notifications')->insert($chunk);
                    }
                    Log::info("Created {$recipients->count()} in-app notifications for change");
                }
            } catch (\Exception $e) {
                Log::error("Error creating in-app notifications: " . $e->getMessage());
            }
        });
    }

    /**
     * Regenerate bills for the current month when any billing-related change is effective today
     * This ensures Outstanding Balance reflects:
     * - Updated rates (utility and rental)
     * - Updated due dates and disconnection dates
     * - Updated billing settings (discounts, penalties, surcharges, interest)
     * Runs in background to avoid blocking HTTP response
     */
    private function regenerateCurrentMonthBills()
    {
        // Use register_shutdown_function to run after HTTP response is sent
        register_shutdown_function(function() {
            try {
                $today = Carbon::today();
                $currentMonthStart = $today->copy()->startOfMonth();
                $currentMonthEnd = $today->copy()->endOfMonth();
                
                Log::info("Regenerating bills for current month to update Outstanding Balance (background)...");
                
                // Get billing IDs for current month before deletion
                $billingIds = DB::table('billing')
                    ->where('period_start', '>=', $currentMonthStart->toDateString())
                    ->where('period_start', '<=', $currentMonthEnd->toDateString())
                    ->pluck('id')
                    ->toArray();
                
                // Delete associated payments first (to maintain data integrity)
                if (!empty($billingIds)) {
                    DB::table('payments')
                        ->whereIn('billing_id', $billingIds)
                        ->delete();
                    Log::info("Deleted " . count($billingIds) . " payments associated with current month bills");
                }
                
                // Delete existing bills for current month
                DB::table('billing')
                    ->where('period_start', '>=', $currentMonthStart->toDateString())
                    ->where('period_start', '<=', $currentMonthEnd->toDateString())
                    ->delete();
                
                Log::info("Deleted existing bills for current month");
                
                // Regenerate bills using the artisan command
                Artisan::call('billing:generate', ['date' => $today->format('Y-m-d')]);
                
                Log::info("Bills regenerated successfully");
                
            } catch (\Exception $e) {
                Log::error("Error regenerating bills in background: " . $e->getMessage());
            }
        });
    }

    /**
     * Get SMS title based on message content
     */
    private function getSmsTitleForChange(string $message): string
    {
        if (str_contains($message, 'RATE CHANGE') || str_contains($message, 'Rate Update')) {
            return 'Rate Change Notification';
        } elseif (str_contains($message, 'DUE DATE CHANGE') || str_contains($message, 'DISCONNECTION')) {
            return 'Schedule Change Notification';
        } elseif (str_contains($message, 'BILLING SETTING CHANGE')) {
            return 'Billing Setting Change Notification';
        } elseif (str_contains($message, 'RENTAL RATE')) {
            return 'Rental Rate Change Notification';
        }
        
        return 'Policy Change Notification';
    }
}

