<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Schedule;
use App\Services\AuditLogger;
use App\Services\ChangeNotificationService;
use Carbon\Carbon;

class EffectivityDateController extends Controller
{
    /**
     * Get all pending changes with future effectivity dates
     * Only accessible by Admin (enforced by middleware)
     */
    public function getPendingChanges()
    {

        $today = Carbon::today();
        $pendingChanges = [];

        // 0. Get ALL pending Rental Rate changes (stored in audit_trails)
        $hasDetailsColumn = DB::getSchemaBuilder()->hasColumn('audit_trails', 'details');
        if ($hasDetailsColumn) {
            // Get all rental rate audit entries
            $rentalRateAudits = DB::table('audit_trails')
                ->where('module', 'Rental Rates')
                ->whereIn('action', ['Updated Rental Rate', 'Updated Rental Rates'])
                ->whereNotNull('details')
                ->select('id', 'action', 'module', 'details', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($rentalRateAudits as $audit) {
                $details = json_decode($audit->details, true);
                
                // Skip if details can't be parsed
                if (!$details || !is_array($details)) {
                    continue;
                }
                
                // Get effectivity_date from top level or from changes array
                $effectivityDate = null;
                if (isset($details['effectivity_date']) && !empty($details['effectivity_date'])) {
                    $effectivityDate = $details['effectivity_date'];
                } elseif (isset($details['changes']) && is_array($details['changes']) && count($details['changes']) > 0) {
                    // For batch updates, get effectivity_date from first change
                    if (isset($details['changes'][0]['effectivity_date'])) {
                        $effectivityDate = $details['changes'][0]['effectivity_date'];
                    }
                }
                
                if (!$effectivityDate) {
                    continue;
                }
                
                try {
                    $parsedDate = Carbon::parse($effectivityDate);
                    // Only include if effectivity date is in the future (>= today)
                    if ($parsedDate->gte($today)) {
                        // Check if this is a batch update with multiple changes
                        if (isset($details['changes']) && is_array($details['changes']) && count($details['changes']) > 0) {
                            // For batch updates, create an entry for each change
                            foreach ($details['changes'] as $change) {
                                // Use effectivity_date from change or fallback to top level
                                $changeEffectivityDate = $change['effectivity_date'] ?? $effectivityDate;
                                
                                // Only add if this specific change has rate information
                                if (isset($change['old_daily_rate']) && isset($change['new_daily_rate'])) {
                                    $pendingChanges[] = [
                                        'id' => $audit->id . '_' . ($change['id'] ?? ''),
                                        'change_type' => 'rental_rate',
                                        'category' => 'Rental Rates',
                                        'item_name' => $change['table_number'] ?? 'N/A',
                                        'description' => "Stall {$change['table_number']}: ₱{$change['old_daily_rate']}/day → ₱{$change['new_daily_rate']}/day",
                                        'effectivity_date' => $changeEffectivityDate,
                                        'changed_at' => $audit->created_at,
                                        'history_table' => 'audit_trails',
                                        'history_id' => $audit->id,
                                    ];
                                }
                            }
                        } else {
                            // Single update - use top-level details
                            if (isset($details['old_daily_rate']) && isset($details['new_daily_rate'])) {
                                $pendingChanges[] = [
                                    'id' => $audit->id,
                                    'change_type' => 'rental_rate',
                                    'category' => 'Rental Rates',
                                    'item_name' => $details['table_number'] ?? 'N/A',
                                    'description' => "Stall {$details['table_number']}: ₱{$details['old_daily_rate']}/day → ₱{$details['new_daily_rate']}/day",
                                    'effectivity_date' => $effectivityDate,
                                    'changed_at' => $audit->created_at,
                                    'history_table' => 'audit_trails',
                                    'history_id' => $audit->id,
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Log error for debugging but continue
                    Log::debug('Error parsing rental rate effectivity date', [
                        'audit_id' => $audit->id,
                        'effectivity_date' => $effectivityDate ?? 'missing',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
        }

        // 1. Get pending Utility Rate changes (latest per utility type with future dates)
        $hasRateEffectivityDate = DB::getSchemaBuilder()->hasColumn('rate_histories', 'effectivity_date');
        if ($hasRateEffectivityDate) {
            // Get the latest pending change for each utility type (regardless of whether applied)
            $utilityTypes = ['Electricity', 'Water'];
            foreach ($utilityTypes as $utilityType) {
                // Get the most recent change with future effectivity date
                $latestChange = DB::table('rate_histories as rh')
                    ->join('rates as r', 'rh.rate_id', '=', 'r.id')
                    ->where('r.utility_type', $utilityType)
                    ->whereNotNull('rh.effectivity_date')
                    ->whereDate('rh.effectivity_date', '>=', $today)
                    ->select(
                        'rh.id',
                        DB::raw("'utility_rate' as change_type"),
                        'r.utility_type as item_name',
                        'rh.old_rate',
                        'rh.new_rate',
                        'rh.effectivity_date',
                        'rh.changed_at'
                    )
                    ->orderBy('rh.changed_at', 'desc')
                    ->first();

                if ($latestChange) {
                    $pendingChanges[] = [
                        'id' => $latestChange->id,
                        'change_type' => $latestChange->change_type,
                        'category' => 'Utility Rates',
                        'item_name' => $latestChange->item_name,
                        'description' => "Rate change: ₱{$latestChange->old_rate} → ₱{$latestChange->new_rate}",
                        'effectivity_date' => $latestChange->effectivity_date,
                        'changed_at' => $latestChange->changed_at,
                        'history_table' => 'rate_histories',
                        'history_id' => $latestChange->id,
                    ];
                }
            }
        }

        // 2. Get pending Schedule changes (Due Date & Disconnection - only latest of each type)
        $hasScheduleEffectivityDate = DB::getSchemaBuilder()->hasColumn('schedule_histories', 'effectivity_date');
        if ($hasScheduleEffectivityDate) {
            // Get latest Due Date change (any utility type)
            $latestDueDate = DB::table('schedule_histories as sh')
                ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
                ->where('s.schedule_type', 'like', 'Due Date%')
                ->whereNotNull('sh.effectivity_date')
                ->whereDate('sh.effectivity_date', '>=', $today)
                ->select(
                    'sh.id',
                    's.schedule_type',
                    'sh.field_changed',
                    'sh.old_value',
                    'sh.new_value',
                    'sh.effectivity_date',
                    'sh.changed_at'
                )
                ->orderBy('sh.changed_at', 'desc')
                ->first();

            if ($latestDueDate) {
                $pendingChanges[] = [
                    'id' => $latestDueDate->id,
                    'change_type' => 'schedule',
                    'category' => 'Due Date & Disconnection',
                    'item_name' => $latestDueDate->schedule_type,
                    'description' => "{$latestDueDate->field_changed}: {$latestDueDate->old_value} → {$latestDueDate->new_value}",
                    'effectivity_date' => $latestDueDate->effectivity_date,
                    'changed_at' => $latestDueDate->changed_at,
                    'history_table' => 'schedule_histories',
                    'history_id' => $latestDueDate->id,
                ];
            }

            // Get latest Disconnection change (any utility type)
            $latestDisconnection = DB::table('schedule_histories as sh')
                ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
                ->where('s.schedule_type', 'like', 'Disconnection%')
                ->whereNotNull('sh.effectivity_date')
                ->whereDate('sh.effectivity_date', '>=', $today)
                ->select(
                    'sh.id',
                    's.schedule_type',
                    'sh.field_changed',
                    'sh.old_value',
                    'sh.new_value',
                    'sh.effectivity_date',
                    'sh.changed_at'
                )
                ->orderBy('sh.changed_at', 'desc')
                ->first();

            if ($latestDisconnection) {
                $pendingChanges[] = [
                    'id' => $latestDisconnection->id,
                    'change_type' => 'schedule',
                    'category' => 'Due Date & Disconnection',
                    'item_name' => $latestDisconnection->schedule_type,
                    'description' => "{$latestDisconnection->field_changed}: {$latestDisconnection->old_value} → {$latestDisconnection->new_value}",
                    'effectivity_date' => $latestDisconnection->effectivity_date,
                    'changed_at' => $latestDisconnection->changed_at,
                    'history_table' => 'schedule_histories',
                    'history_id' => $latestDisconnection->id,
                ];
            }

            // Get latest Meter Reading schedule change
            $latestMeterReading = DB::table('schedule_histories as sh')
                ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
                ->where('s.schedule_type', 'Meter Reading')
                ->whereNotNull('sh.effectivity_date')
                ->whereDate('sh.effectivity_date', '>=', $today)
                ->select(
                    'sh.id',
                    's.schedule_type',
                    'sh.field_changed',
                    'sh.old_value',
                    'sh.new_value',
                    'sh.effectivity_date',
                    'sh.changed_at'
                )
                ->orderBy('sh.changed_at', 'desc')
                ->first();

            if ($latestMeterReading) {
                $pendingChanges[] = [
                    'id' => $latestMeterReading->id,
                    'change_type' => 'schedule',
                    'category' => 'Meter Reading Schedule',
                    'item_name' => $latestMeterReading->schedule_type,
                    'description' => "{$latestMeterReading->field_changed}: {$latestMeterReading->old_value} → {$latestMeterReading->new_value}",
                    'effectivity_date' => $latestMeterReading->effectivity_date,
                    'changed_at' => $latestMeterReading->changed_at,
                    'history_table' => 'schedule_histories',
                    'history_id' => $latestMeterReading->id,
                ];
            }
        }

        // 3. Get pending Billing Settings changes (Discount Rate, Surcharge Rate, Monthly Interest Rate for Electricity - 3 total)
        $hasBillingSettingEffectivityDate = DB::getSchemaBuilder()->hasColumn('billing_setting_histories', 'effectivity_date');
        if ($hasBillingSettingEffectivityDate) {
            // 1. Get latest Discount Rate change (any utility type)
            $latestDiscount = DB::table('billing_setting_histories as bsh')
                ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
                ->where('bsh.field_changed', 'discount_rate')
                ->whereNotNull('bsh.effectivity_date')
                ->whereDate('bsh.effectivity_date', '>=', $today)
                ->select(
                    'bsh.id',
                    'bs.utility_type',
                    'bsh.field_changed',
                    'bsh.old_value',
                    'bsh.new_value',
                    'bsh.effectivity_date',
                    'bsh.changed_at'
                )
                ->orderBy('bsh.changed_at', 'desc')
                ->first();

            if ($latestDiscount) {
                $pendingChanges[] = [
                    'id' => $latestDiscount->id,
                    'change_type' => 'billing_setting',
                    'category' => 'Billing Settings',
                    'item_name' => "{$latestDiscount->utility_type} - Discount Rate",
                    'description' => "Discount Rate: {$latestDiscount->old_value} → {$latestDiscount->new_value}",
                    'effectivity_date' => $latestDiscount->effectivity_date,
                    'changed_at' => $latestDiscount->changed_at,
                    'history_table' => 'billing_setting_histories',
                    'history_id' => $latestDiscount->id,
                ];
            }

            // 2. Get latest Surcharge Rate change (any utility type)
            $latestSurcharge = DB::table('billing_setting_histories as bsh')
                ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
                ->where('bsh.field_changed', 'surcharge_rate')
                ->whereNotNull('bsh.effectivity_date')
                ->whereDate('bsh.effectivity_date', '>=', $today)
                ->select(
                    'bsh.id',
                    'bs.utility_type',
                    'bsh.field_changed',
                    'bsh.old_value',
                    'bsh.new_value',
                    'bsh.effectivity_date',
                    'bsh.changed_at'
                )
                ->orderBy('bsh.changed_at', 'desc')
                ->first();

            if ($latestSurcharge) {
                $pendingChanges[] = [
                    'id' => $latestSurcharge->id,
                    'change_type' => 'billing_setting',
                    'category' => 'Billing Settings',
                    'item_name' => "{$latestSurcharge->utility_type} - Surcharge Rate",
                    'description' => "Surcharge Rate: {$latestSurcharge->old_value} → {$latestSurcharge->new_value}",
                    'effectivity_date' => $latestSurcharge->effectivity_date,
                    'changed_at' => $latestSurcharge->changed_at,
                    'history_table' => 'billing_setting_histories',
                    'history_id' => $latestSurcharge->id,
                ];
            }

            // 3. Get latest Monthly Interest Rate change for Electricity
            $latestMonthlyInterest = DB::table('billing_setting_histories as bsh')
                ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
                ->where('bsh.field_changed', 'monthly_interest_rate')
                ->where('bs.utility_type', 'Electricity')
                ->whereNotNull('bsh.effectivity_date')
                ->whereDate('bsh.effectivity_date', '>=', $today)
                ->select(
                    'bsh.id',
                    'bs.utility_type',
                    'bsh.field_changed',
                    'bsh.old_value',
                    'bsh.new_value',
                    'bsh.effectivity_date',
                    'bsh.changed_at'
                )
                ->orderBy('bsh.changed_at', 'desc')
                ->first();

            if ($latestMonthlyInterest) {
                $pendingChanges[] = [
                    'id' => $latestMonthlyInterest->id,
                    'change_type' => 'billing_setting',
                    'category' => 'Billing Settings',
                    'item_name' => "Electricity - Monthly Interest Rate",
                    'description' => "Monthly Interest Rate: {$latestMonthlyInterest->old_value} → {$latestMonthlyInterest->new_value}",
                    'effectivity_date' => $latestMonthlyInterest->effectivity_date,
                    'changed_at' => $latestMonthlyInterest->changed_at,
                    'history_table' => 'billing_setting_histories',
                    'history_id' => $latestMonthlyInterest->id,
                ];
            }
        }

        // Deduplicate: Keep only the latest change for each unique item (category + item_name)
        // Group by unique key and keep the most recent one (based on changed_at)
        $deduplicated = [];
        foreach ($pendingChanges as $change) {
            $uniqueKey = $change['category'] . '|' . $change['item_name'];
            
            // If we haven't seen this item before, or this change is more recent, keep it
            if (!isset($deduplicated[$uniqueKey]) || 
                Carbon::parse($change['changed_at'])->gt(Carbon::parse($deduplicated[$uniqueKey]['changed_at']))) {
                $deduplicated[$uniqueKey] = $change;
            }
        }
        
        // Convert back to array (remove keys)
        $pendingChanges = array_values($deduplicated);

        // Sort all pending changes by effectivity date
        usort($pendingChanges, function ($a, $b) {
            return strcmp($a['effectivity_date'], $b['effectivity_date']);
        });

        // Format dates
        foreach ($pendingChanges as &$change) {
            $change['effectivity_date'] = Carbon::parse($change['effectivity_date'])->format('Y-m-d');
            $change['changed_at'] = Carbon::parse($change['changed_at'])->format('Y-m-d H:i:s');
        }

        return response()->json([
            'pending_changes' => $pendingChanges,
            'total' => count($pendingChanges)
        ]);
    }

    /**
     * Update effectivity date for a pending change
     * Only accessible by Admin (enforced by middleware)
     */
    public function updateEffectivityDate(Request $request, ChangeNotificationService $notificationService)
    {

        $validator = Validator::make($request->all(), [
            'history_table' => 'required|string|in:rate_histories,schedule_histories,billing_setting_histories,audit_trails',
            'history_id' => 'required|integer',
            'new_effectivity_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            $historyTable = $request->history_table;
            $historyId = $request->history_id;
            $newEffectivityDate = Carbon::parse($request->new_effectivity_date)->format('Y-m-d');
            $today = Carbon::today();
            $currentMonthStart = $today->copy()->startOfMonth();
            $currentMonthEnd = $today->copy()->endOfMonth();

            // Get the current record
            $currentRecord = DB::table($historyTable)->where('id', $historyId)->first();
            if (!$currentRecord) {
                return response()->json(['message' => 'Record not found.'], 404);
            }

            $oldEffectivityDate = null;

            // Handle audit_trails differently (effectivity_date is in JSON details)
            if ($historyTable === 'audit_trails') {
                $hasDetailsColumn = DB::getSchemaBuilder()->hasColumn('audit_trails', 'details');
                if (!$hasDetailsColumn) {
                    return response()->json(['message' => 'Details column does not exist in audit_trails.'], 400);
                }

                $details = json_decode($currentRecord->details, true) ?? [];
                $oldEffectivityDate = $details['effectivity_date'] ?? null;

                // Update the effectivity_date in the JSON details
                $details['effectivity_date'] = $newEffectivityDate;
                DB::table($historyTable)
                    ->where('id', $historyId)
                    ->update(['details' => json_encode($details)]);
            } else {
                // For other tables, check if effectivity_date column exists
                $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn($historyTable, 'effectivity_date');
                if (!$hasEffectivityDate) {
                    return response()->json(['message' => 'Effectivity date column does not exist in this table.'], 400);
                }

                $oldEffectivityDate = $currentRecord->effectivity_date;

                // Update the effectivity date only - no bill regeneration
                DB::table($historyTable)
                    ->where('id', $historyId)
                    ->update(['effectivity_date' => $newEffectivityDate]);
            }

            // Check if the new effectivity date is today or in the past - if so, apply the change to main tables
            $shouldApplyChange = Carbon::parse($newEffectivityDate)->lte($today);
            
            if ($shouldApplyChange) {
                // Apply the change to main tables
                if ($historyTable === 'rate_histories') {
                    // Update the rate in the main rates table
                    DB::table('rates')
                        ->where('id', $currentRecord->rate_id)
                        ->update([
                            'rate' => $currentRecord->new_rate,
                            'updated_at' => now()
                        ]);
                } elseif ($historyTable === 'schedule_histories') {
                    // Update the schedule in the main schedules table
                    $schedule = DB::table('schedules')->where('id', $currentRecord->schedule_id)->first();
                    if ($schedule) {
                        DB::table('schedules')
                            ->where('id', $currentRecord->schedule_id)
                            ->update([
                                'description' => $currentRecord->new_value,
                                'updated_at' => now()
                            ]);
                    }
                } elseif ($historyTable === 'billing_setting_histories') {
                    // Update the billing setting in the main billing_settings table
                    $fieldName = str_replace(' ', '_', strtolower($currentRecord->field_changed));
                    DB::table('billing_settings')
                        ->where('id', $currentRecord->billing_setting_id)
                        ->update([
                            $fieldName => $currentRecord->new_value / 100, // Convert from percentage
                            'updated_at' => now()
                        ]);
                } elseif ($historyTable === 'audit_trails' && $currentRecord->module === 'Rental Rates') {
                    // Update rental rates in the main stalls table
                    $details = json_decode($currentRecord->details, true) ?? [];
                    if (isset($details['changes']) && is_array($details['changes'])) {
                        foreach ($details['changes'] as $change) {
                            if (isset($change['id'])) {
                                $updateData = [];
                                if (isset($change['new_daily_rate'])) {
                                    $updateData['daily_rate'] = $change['new_daily_rate'];
                                }
                                if (isset($change['new_monthly_rate'])) {
                                    $updateData['monthly_rate'] = $change['new_monthly_rate'];
                                }
                                if (isset($change['table_number'])) {
                                    $updateData['table_number'] = $change['table_number'];
                                }
                                if (!empty($updateData)) {
                                    $updateData['updated_at'] = now();
                                    DB::table('stalls')
                                        ->where('id', $change['id'])
                                        ->update($updateData);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            // Send SMS notification about the change (only when effectivity date is adjusted)
            try {
                if ($historyTable === 'rate_histories') {
                    // Get rate info
                    $rate = DB::table('rates')->where('id', $currentRecord->rate_id)->first();
                    if ($rate) {
                        $notificationService->sendRateChangeNotification(
                            $rate->utility_type,
                            $currentRecord->old_rate,
                            $currentRecord->new_rate,
                            null, // monthly rates not stored in rate_histories
                            null
                        );
                    }
                } elseif ($historyTable === 'schedule_histories') {
                    // Get schedule info
                    $schedule = DB::table('schedules')->where('id', $currentRecord->schedule_id)->first();
                    if ($schedule) {
                        // Handle Meter Reading schedule differently
                        if ($schedule->schedule_type === 'Meter Reading') {
                            $notificationService->sendScheduleChangeNotification(
                                'Meter Reading',
                                'Electricity',
                                $currentRecord->old_value,
                                $currentRecord->new_value
                            );
                        } else {
                            $utilityType = str_replace(['Due Date - ', 'Disconnection - ', 'Meter Reading - '], '', $schedule->schedule_type);
                            $notificationService->sendScheduleChangeNotification(
                                $schedule->schedule_type,
                                $utilityType,
                                $currentRecord->old_value,
                                $currentRecord->new_value
                            );
                        }
                    }
                } elseif ($historyTable === 'billing_setting_histories') {
                    // Get billing setting info
                    $billingSetting = DB::table('billing_settings')->where('id', $currentRecord->billing_setting_id)->first();
                    if ($billingSetting) {
                        $notificationService->sendBillingSettingChangeNotification(
                            $billingSetting->utility_type,
                            str_replace(' ', '_', strtolower($currentRecord->field_changed)),
                            $currentRecord->old_value / 100, // Convert from percentage
                            $currentRecord->new_value / 100
                        );
                    }
                } elseif ($historyTable === 'audit_trails' && $currentRecord->module === 'Rental Rates') {
                    // Get rental rate info from details JSON
                    $details = json_decode($currentRecord->details, true) ?? [];
                    if (isset($details['changes']) && is_array($details['changes'])) {
                        // Batch update - send notification for each stall
                        foreach ($details['changes'] as $change) {
                            if (isset($change['id'])) {
                                $stall = DB::table('stalls')->where('id', $change['id'])->first();
                                if ($stall) {
                                    // Create a simple object for the notification service
                                    $stallModel = (object) [
                                        'id' => $stall->id,
                                        'table_number' => $stall->table_number,
                                        'section' => DB::table('sections')->where('id', $stall->section_id)->value('name') ?? 'N/A'
                                    ];
                                    $notificationService->sendRentalRateChangeNotification(
                                        $stallModel,
                                        $change['old_daily_rate'] ?? 0,
                                        $change['new_daily_rate'] ?? 0,
                                        $change['old_monthly_rate'] ?? null,
                                        $change['new_monthly_rate'] ?? null
                                    );
                                }
                            }
                        }
                    } elseif (isset($details['stall_id'])) {
                        // Single stall update
                        $stall = DB::table('stalls')->where('id', $details['stall_id'])->first();
                        if ($stall) {
                            $stallModel = (object) [
                                'id' => $stall->id,
                                'table_number' => $stall->table_number,
                                'section' => DB::table('sections')->where('id', $stall->section_id)->value('name') ?? 'N/A'
                            ];
                            $notificationService->sendRentalRateChangeNotification(
                                $stallModel,
                                $details['old_daily_rate'] ?? 0,
                                $details['new_daily_rate'] ?? 0,
                                $details['old_monthly_rate'] ?? null,
                                $details['new_monthly_rate'] ?? null
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error sending notification after effectivity date update: " . $e->getMessage());
                // Don't fail the request if SMS fails
            }

            $message = $shouldApplyChange 
                ? 'Effectivity date updated, changes applied, and notifications sent.'
                : 'Effectivity date updated successfully. Notifications will be sent when the effectivity date arrives.';

            return response()->json([
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating effectivity date: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bill generation schedules
     */
    public function getBillGenerationSchedules()
    {
        try {
            // Check if schedule_day column exists
            $hasScheduleDay = DB::getSchemaBuilder()->hasColumn('schedules', 'schedule_day');
            
            $selectFields = ['id', 'schedule_type', 'description'];
            if ($hasScheduleDay) {
                $selectFields[] = 'schedule_day';
            }
            
            $schedules = DB::table('schedules')
                ->whereIn('schedule_type', ['Bill Generation', 'Apply Pending Changes'])
                ->select($selectFields)
                ->get()
                ->map(function ($schedule) use ($hasScheduleDay) {
                    return [
                        'id' => $schedule->id,
                        'type' => $schedule->schedule_type,
                        'time' => $schedule->description ?? ($schedule->schedule_type === 'Bill Generation' ? '07:00' : '06:00'),
                        'day' => ($hasScheduleDay && isset($schedule->schedule_day)) ? $schedule->schedule_day : ($schedule->schedule_type === 'Bill Generation' ? 1 : null),
                    ];
                });

            // If schedules don't exist, return defaults
            $result = [
                'billGeneration' => $schedules->firstWhere('type', 'Bill Generation') ?? [
                    'id' => null,
                    'type' => 'Bill Generation',
                    'time' => '07:00',
                    'day' => 1,
                ],
                'applyPendingChanges' => $schedules->firstWhere('type', 'Apply Pending Changes') ?? [
                    'id' => null,
                    'type' => 'Apply Pending Changes',
                    'time' => '06:00',
                    'day' => null,
                ],
            ];

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error loading bill generation schedules: ' . $e->getMessage());
            // Return defaults on error
            return response()->json([
                'billGeneration' => [
                    'id' => null,
                    'type' => 'Bill Generation',
                    'time' => '07:00',
                    'day' => 1,
                ],
                'applyPendingChanges' => [
                    'id' => null,
                    'type' => 'Apply Pending Changes',
                    'time' => '06:00',
                    'day' => null,
                ],
            ]);
        }
    }

    /**
     * Update bill generation schedules
     */
    public function updateBillGenerationSchedules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'billGeneration.day' => 'required|integer|min:1|max:31',
            'billGeneration.time' => 'required|date_format:H:i',
            'applyPendingChanges.time' => 'required|date_format:H:i',
            'effectivityDate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $validator->errors()], 400);
        }

        try {
            DB::transaction(function () use ($request) {
                $effectivityDate = isset($request->effectivityDate) && $request->effectivityDate
                    ? Carbon::parse($request->effectivityDate)->format('Y-m-d')
                    : Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');

                // Update Bill Generation schedule
                $billGenData = $request->input('billGeneration');
                $existingBillGen = Schedule::where('schedule_type', 'Bill Generation')->first();
                $oldBillGenDay = $existingBillGen ? $existingBillGen->schedule_day : 1;
                $oldBillGenTime = $existingBillGen ? $existingBillGen->description : '07:00';

                $billGenSchedule = Schedule::updateOrCreate(
                    ['schedule_type' => 'Bill Generation'],
                    [
                        'description' => $billGenData['time'],
                        'schedule_day' => $billGenData['day'],
                        'schedule_date' => $existingBillGen->schedule_date ?? now()->toDateString(),
                    ]
                );

                // Log changes to history
                if ($oldBillGenDay != $billGenData['day'] || $oldBillGenTime != $billGenData['time']) {
                    if ($oldBillGenDay != $billGenData['day']) {
                        DB::table('schedule_histories')->insert([
                            'schedule_id' => $billGenSchedule->id,
                            'field_changed' => 'Bill Generation - Day',
                            'old_value' => (string)($oldBillGenDay ?? 1),
                            'new_value' => (string)$billGenData['day'],
                            'changed_by' => Auth::id() ?? 1,
                            'effectivity_date' => $effectivityDate,
                        ]);
                    }
                    if ($oldBillGenTime != $billGenData['time']) {
                        DB::table('schedule_histories')->insert([
                            'schedule_id' => $billGenSchedule->id,
                            'field_changed' => 'Bill Generation - Time',
                            'old_value' => $oldBillGenTime,
                            'new_value' => $billGenData['time'],
                            'changed_by' => Auth::id() ?? 1,
                            'effectivity_date' => $effectivityDate,
                        ]);
                    }
                }

                // Update Apply Pending Changes schedule
                $applyPendingData = $request->input('applyPendingChanges');
                $existingApplyPending = Schedule::where('schedule_type', 'Apply Pending Changes')->first();
                $oldApplyPendingTime = $existingApplyPending ? $existingApplyPending->description : '06:00';

                $applyPendingSchedule = Schedule::updateOrCreate(
                    ['schedule_type' => 'Apply Pending Changes'],
                    [
                        'description' => $applyPendingData['time'],
                        'schedule_date' => $existingApplyPending->schedule_date ?? now()->toDateString(),
                    ]
                );

                // Log changes to history
                if ($oldApplyPendingTime != $applyPendingData['time']) {
                    DB::table('schedule_histories')->insert([
                        'schedule_id' => $applyPendingSchedule->id,
                        'field_changed' => 'Apply Pending Changes - Time',
                        'old_value' => $oldApplyPendingTime,
                        'new_value' => $applyPendingData['time'],
                        'changed_by' => Auth::id() ?? 1,
                        'effectivity_date' => $effectivityDate,
                    ]);
                }

                AuditLogger::log(
                    'Updated Bill Generation Schedules',
                    'Schedules',
                    'Success',
                    [
                        'bill_generation' => $billGenData,
                        'apply_pending_changes' => $applyPendingData,
                        'effectivity_date' => $effectivityDate,
                    ]
                );

                // Clear caches
                Cache::forget('sms_schedules');
            });

            return response()->json(['message' => 'Bill generation schedules updated successfully!']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to check rental rate audit entries
     */
    public function debugRentalRates()
    {
        $hasDetailsColumn = DB::getSchemaBuilder()->hasColumn('audit_trails', 'details');
        if (!$hasDetailsColumn) {
            return response()->json(['error' => 'details column does not exist']);
        }

        $audits = DB::table('audit_trails')
            ->where('module', 'Rental Rates')
            ->whereIn('action', ['Updated Rental Rate', 'Updated Rental Rates'])
            ->whereNotNull('details')
            ->select('id', 'action', 'module', 'details', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($audit) {
                $details = json_decode($audit->details, true);
                return [
                    'id' => $audit->id,
                    'action' => $audit->action,
                    'created_at' => $audit->created_at,
                    'has_effectivity_date' => isset($details['effectivity_date']),
                    'effectivity_date' => $details['effectivity_date'] ?? null,
                    'table_number' => $details['table_number'] ?? null,
                    'old_daily_rate' => $details['old_daily_rate'] ?? null,
                    'new_daily_rate' => $details['new_daily_rate'] ?? null,
                    'all_keys' => $details ? array_keys($details) : [],
                ];
            });

        return response()->json([
            'total_found' => $audits->count(),
            'audits' => $audits,
            'today' => Carbon::today()->format('Y-m-d'),
        ]);
    }
}

