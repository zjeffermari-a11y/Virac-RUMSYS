<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Support\Facades\Log;

class AnnouncementService
{
    /**
     * Generate a draft announcement when utility rates change
     */
    public function createRateChangeAnnouncement(string $utilityType, float $oldRate, float $newRate, float $oldMonthlyRate = null, float $newMonthlyRate = null, ?string $effectivityDate = null): ?Announcement
    {
        $rateChange = $newRate - $oldRate;
        $rateChangePercent = $oldRate > 0 ? (($rateChange / $oldRate) * 100) : 0;
        $direction = $rateChange > 0 ? 'increased' : ($rateChange < 0 ? 'decreased' : 'unchanged');
        
        $title = "{$utilityType} Rate Update";
        
        $content = "Dear Vendors and Staff,\n\n";
        $content .= "This is to inform you that the {$utilityType} rate has been {$direction}.\n\n";
        $content .= "Previous Rate: ₱" . number_format($oldRate, 2) . " per " . ($utilityType === 'Electricity' ? 'kWh' : 'day') . "\n";
        $content .= "New Rate: ₱" . number_format($newRate, 2) . " per " . ($utilityType === 'Electricity' ? 'kWh' : 'day') . "\n";
        
        if ($oldMonthlyRate !== null && $newMonthlyRate !== null) {
            $content .= "\nMonthly Rate:\n";
            $content .= "Previous: ₱" . number_format($oldMonthlyRate, 2) . "\n";
            $content .= "New: ₱" . number_format($newMonthlyRate, 2) . "\n";
        }
        
        if ($rateChangePercent != 0) {
            $content .= "\nChange: " . ($rateChangePercent > 0 ? '+' : '') . number_format($rateChangePercent, 2) . "%\n";
        }

        if ($effectivityDate) {
            $content .= "\nEffective Date: " . date('F j, Y', strtotime($effectivityDate)) . "\n";
        }
        
        $content .= "\nThis change will be reflected in your next billing statement.\n\n";
        $content .= "Thank you for your understanding.\n\n";
        $content .= "- Virac Public Market Administration";

        return Announcement::create([
            'title' => $title,
            'content' => $content,
            'announcement_type' => 'rate_change',
            'related_utility' => $utilityType,
            'related_section' => $utilityType === 'Water' ? 'Wet Section' : null, // Water only for wet section
            'is_active' => false, // Draft - needs to be activated manually
        ]);
    }

    /**
     * Generate a draft announcement when billing settings change
     */
    public function createBillingSettingChangeAnnouncement(string $utilityType, string $settingName, float $oldValue, float $newValue): ?Announcement
    {
        $settingLabels = [
            'discount_rate' => 'Discount Rate',
            'surcharge_rate' => 'Surcharge Rate',
            'monthly_interest_rate' => 'Monthly Interest Rate',
            'penalty_rate' => 'Penalty Rate',
        ];

        $label = $settingLabels[$settingName] ?? ucwords(str_replace('_', ' ', $settingName));
        $oldPercent = $oldValue * 100;
        $newPercent = $newValue * 100;
        $change = $newPercent - $oldPercent;
        $direction = $change > 0 ? 'increased' : ($change < 0 ? 'decreased' : 'unchanged');

        $title = "{$utilityType} {$label} Update";
        
        $content = "Dear Vendors and Staff,\n\n";
        $content .= "This is to inform you that the {$utilityType} {$label} has been {$direction}.\n\n";
        $content .= "Previous {$label}: " . number_format($oldPercent, 2) . "%\n";
        $content .= "New {$label}: " . number_format($newPercent, 2) . "%\n";
        
        if ($change != 0) {
            $content .= "\nChange: " . ($change > 0 ? '+' : '') . number_format($change, 2) . " percentage points\n";
        }
        
        $content .= "\nThis change will be reflected in your next billing statement.\n\n";
        $content .= "Thank you for your understanding.\n\n";
        $content .= "- Virac Public Market Administration";

        return Announcement::create([
            'title' => $title,
            'content' => $content,
            'announcement_type' => 'billing_setting_change',
            'related_utility' => $utilityType,
            'related_section' => $utilityType === 'Water' ? 'Wet Section' : null,
            'is_active' => false, // Draft - needs to be activated manually
        ]);
    }

    /**
     * Generate a draft announcement when due date or disconnection date changes
     */
    public function createDateScheduleChangeAnnouncement(string $scheduleType, string $utilityType, string $oldDay, string $newDay): ?Announcement
    {
        $isDueDate = str_contains($scheduleType, 'Due Date');
        $label = $isDueDate ? 'Due Date' : 'Disconnection Date';
        
        $title = "{$utilityType} {$label} Update";
        
        $content = "Dear Vendors and Staff,\n\n";
        $content .= "This is to inform you that the {$utilityType} {$label} has been changed.\n\n";
        $content .= "Previous {$label}: Day " . $oldDay . " of each month\n";
        $content .= "New {$label}: Day " . $newDay . " of each month\n";
        
        if ($isDueDate) {
            $content .= "\nPlease ensure your payments are made on or before the new due date to avoid penalties.\n";
        } else {
            $content .= "\nPlease ensure your payments are made before the new disconnection date to avoid service interruption.\n";
        }
        
        $content .= "\nThis change will take effect in the next billing cycle.\n\n";
        $content .= "Thank you for your understanding.\n\n";
        $content .= "- Virac Public Market Administration";

        return Announcement::create([
            'title' => $title,
            'content' => $content,
            'announcement_type' => 'date_schedule_change',
            'related_utility' => $utilityType,
            'related_section' => $utilityType === 'Water' ? 'Wet Section' : null,
            'is_active' => false, // Draft - needs to be activated manually
        ]);
    }

    /**
     * Generate a draft announcement when rental rate changes for a specific stall
     */
    public function createRentalRateChangeAnnouncement(\App\Models\Stall $stall, float $oldDailyRate, float $newDailyRate, float $oldMonthlyRate = null, float $newMonthlyRate = null): ?Announcement
    {
        $dailyRateChange = $newDailyRate - $oldDailyRate;
        $dailyRateChangePercent = $oldDailyRate > 0 ? (($dailyRateChange / $oldDailyRate) * 100) : 0;
        $direction = $dailyRateChange > 0 ? 'increased' : ($dailyRateChange < 0 ? 'decreased' : 'unchanged');
        
        $stallIdentifier = $stall->table_number ?? "Stall #{$stall->id}";
        $sectionName = $stall->section ? $stall->section->name : 'Unknown Section';
        
        $title = "Rental Rate Update - {$stallIdentifier}";
        
        $content = "Dear Vendor,\n\n";
        $content .= "This is to inform you that the rental rate for {$stallIdentifier} ({$sectionName}) has been {$direction}.\n\n";
        $content .= "Daily Rate:\n";
        $content .= "Previous: ₱" . number_format($oldDailyRate, 2) . "\n";
        $content .= "New: ₱" . number_format($newDailyRate, 2) . "\n";
        
        if ($oldMonthlyRate !== null && $newMonthlyRate !== null) {
            $content .= "\nMonthly Rate:\n";
            $content .= "Previous: ₱" . number_format($oldMonthlyRate, 2) . "\n";
            $content .= "New: ₱" . number_format($newMonthlyRate, 2) . "\n";
        }
        
        if ($dailyRateChangePercent != 0) {
            $content .= "\nChange: " . ($dailyRateChangePercent > 0 ? '+' : '') . number_format($dailyRateChangePercent, 2) . "%\n";
        }
        
        $content .= "\nThis change will be reflected in your next billing statement.\n\n";
        $content .= "Thank you for your understanding.\n\n";
        $content .= "- Virac Public Market Administration";

        try {
            // Check if the migration has been run by checking if the column exists
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('announcements');
            $hasNewColumns = in_array('related_stall_id', $columns);
            
            $announcementData = [
                'title' => $title,
                'content' => $content,
                'is_active' => false, // Draft - needs to be activated manually
            ];
            
            // Only add new fields if migration has been run
            if ($hasNewColumns) {
                $announcementData['announcement_type'] = 'rental_rate_change';
                $announcementData['related_utility'] = 'Rent';
                $announcementData['related_section'] = $stall->section ? $stall->section->name : null;
                $announcementData['related_stall_id'] = $stall->id;
            } else {
                Log::warning('Announcement migration not run - creating announcement without new fields. Please run: php artisan migrate');
            }
            
            $announcement = Announcement::create($announcementData);
            
            Log::info('Rental rate announcement created', [
                'announcement_id' => $announcement->id,
                'stall_id' => $stall->id,
                'has_new_fields' => $hasNewColumns,
            ]);
            
            return $announcement;
        } catch (\Exception $e) {
            Log::error('Failed to create rental rate announcement', [
                'stall_id' => $stall->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

