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

        // Note: Rental rates are stored in audit_trails, not rate_histories
        // They can be added later if needed by parsing audit_trails details JSON

        // 1. Get pending Utility Rate changes
        $hasRateEffectivityDate = DB::getSchemaBuilder()->hasColumn('rate_histories', 'effectivity_date');
        if ($hasRateEffectivityDate) {
            $utilityRateChanges = DB::table('rate_histories as rh')
                ->join('rates as r', 'rh.rate_id', '=', 'r.id')
                ->whereIn('r.utility_type', ['Electricity', 'Water'])
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
                ->orderBy('rh.effectivity_date', 'asc')
                ->get();

            foreach ($utilityRateChanges as $change) {
                $pendingChanges[] = [
                    'id' => $change->id,
                    'change_type' => $change->change_type,
                    'category' => 'Utility Rates',
                    'item_name' => $change->item_name,
                    'description' => "Rate change: ₱{$change->old_rate} → ₱{$change->new_rate}",
                    'effectivity_date' => $change->effectivity_date,
                    'changed_at' => $change->changed_at,
                    'history_table' => 'rate_histories',
                    'history_id' => $change->id,
                ];
            }
        }

        // 2. Get pending Schedule changes (Meter Reading, Due Date, Disconnection)
        $hasScheduleEffectivityDate = DB::getSchemaBuilder()->hasColumn('schedule_histories', 'effectivity_date');
        if ($hasScheduleEffectivityDate) {
            $scheduleChanges = DB::table('schedule_histories as sh')
                ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
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
                ->orderBy('sh.effectivity_date', 'asc')
                ->get();

            foreach ($scheduleChanges as $change) {
                $category = 'Schedules';
                if (str_contains($change->schedule_type, 'Due Date') || str_contains($change->schedule_type, 'Disconnection')) {
                    $category = 'Due Date & Disconnection';
                } elseif (str_contains($change->schedule_type, 'SMS')) {
                    $category = 'SMS Schedules';
                } elseif (str_contains($change->schedule_type, 'Meter Reading')) {
                    $category = 'Meter Reading Schedule';
                }

                $pendingChanges[] = [
                    'id' => $change->id,
                    'change_type' => 'schedule',
                    'category' => $category,
                    'item_name' => $change->schedule_type,
                    'description' => "{$change->field_changed}: {$change->old_value} → {$change->new_value}",
                    'effectivity_date' => $change->effectivity_date,
                    'changed_at' => $change->changed_at,
                    'history_table' => 'schedule_histories',
                    'history_id' => $change->id,
                ];
            }
        }

        // 3. Get pending Billing Settings changes
        $hasBillingSettingEffectivityDate = DB::getSchemaBuilder()->hasColumn('billing_setting_histories', 'effectivity_date');
        if ($hasBillingSettingEffectivityDate) {
            $billingSettingChanges = DB::table('billing_setting_histories as bsh')
                ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
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
                ->orderBy('bsh.effectivity_date', 'asc')
                ->get();

            foreach ($billingSettingChanges as $change) {
                $pendingChanges[] = [
                    'id' => $change->id,
                    'change_type' => 'billing_setting',
                    'category' => 'Billing Settings',
                    'item_name' => "{$change->utility_type} - {$change->field_changed}",
                    'description' => "{$change->field_changed}: {$change->old_value} → {$change->new_value}",
                    'effectivity_date' => $change->effectivity_date,
                    'changed_at' => $change->changed_at,
                    'history_table' => 'billing_setting_histories',
                    'history_id' => $change->id,
                ];
            }
        }

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
            'history_table' => 'required|string|in:rate_histories,schedule_histories,billing_setting_histories',
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

            // Check if effectivity_date column exists
            $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn($historyTable, 'effectivity_date');
            if (!$hasEffectivityDate) {
                return response()->json(['message' => 'Effectivity date column does not exist in this table.'], 400);
            }

            // Get the current effectivity date
            $currentRecord = DB::table($historyTable)->where('id', $historyId)->first();
            if (!$currentRecord) {
                return response()->json(['message' => 'Record not found.'], 404);
            }

            $oldEffectivityDate = $currentRecord->effectivity_date;

            // Update the effectivity date only - no bill regeneration
            DB::table($historyTable)
                ->where('id', $historyId)
                ->update(['effectivity_date' => $newEffectivityDate]);

            DB::commit();

            // Send SMS notification about the change
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
                        $utilityType = str_replace(['Due Date - ', 'Disconnection - ', 'Meter Reading - '], '', $schedule->schedule_type);
                        $notificationService->sendScheduleChangeNotification(
                            $schedule->schedule_type,
                            $utilityType,
                            $currentRecord->old_value,
                            $currentRecord->new_value
                        );
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
                }
            } catch (\Exception $e) {
                Log::error("Error sending notification after effectivity date update: " . $e->getMessage());
                // Don't fail the request if SMS fails
            }

            return response()->json([
                'message' => 'Effectivity date updated successfully and notifications sent.',
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
        $schedules = DB::table('schedules')
            ->whereIn('schedule_type', ['Bill Generation', 'Apply Pending Changes'])
            ->select('id', 'schedule_type', 'description', 'schedule_day')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'type' => $schedule->schedule_type,
                    'time' => $schedule->description ?? ($schedule->schedule_type === 'Bill Generation' ? '07:00' : '06:00'),
                    'day' => $schedule->schedule_day ?? ($schedule->schedule_type === 'Bill Generation' ? 1 : null),
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
}

