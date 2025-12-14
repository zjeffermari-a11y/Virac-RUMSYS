<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Schedule;
use Illuminate\Support\Facades\Cache;
use App\Services\AuditLogger;
use App\Services\ChangeNotificationService;

class ScheduleController extends Controller
{
    public function show()
    {
        $schedule = Cache::remember('meter_reading_schedule', 3600, function () {
            return DB::table('schedules')
                ->where('schedule_type', 'Meter Reading')
                ->first();
        });

        if (!$schedule) {
            return response()->json(['message' => 'Meter Reading schedule not found.'], 404);
        }

        return response()->json($schedule);
    }

    /**
     * Update the meter reading schedule.
     */
    public function update(Request $request, $scheduleId, ChangeNotificationService $notificationService)
    {
        $validator = Validator::make($request->all(), [
            'day' => 'required|integer|min:1|max:31',
            'effectivityDate' => 'nullable|date',
            'effectiveToday' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid day provided.'], 400);
        }

        try {
            $schedule = DB::table('schedules')->where('id', $scheduleId)->first();
            if (!$schedule) {
                throw new \Exception('Schedule not found.');
            }

            $oldDay = $schedule->description;
            $newDay = $request->input('day');

            // Check if day has actually changed
            if ($oldDay == $newDay) {
                return response()->json(['message' => 'No changes detected.']);
            }

            // Check if we need to show modal
            $effectiveToday = $request->input('effectiveToday');
            
            if ($effectiveToday === null) {
                // Return change info for modal
                return response()->json([
                    'changeDetected' => true,
                    'changeType' => 'meter_reading_schedule',
                    'changeData' => [
                        'old_day' => $oldDay,
                        'new_day' => $newDay,
                    ],
                    'requiresConfirmation' => true,
                ]);
            }

            $pendingChangeId = null;

            DB::transaction(function () use ($request, $scheduleId, $effectiveToday, $notificationService, &$pendingChangeId, $oldDay, $newDay) {
                // Determine effectivity date based on effectiveToday
                $effectivityDate = $effectiveToday
                    ? \Carbon\Carbon::now()->format('Y-m-d')
                    : (isset($request->effectivityDate) && $request->effectivityDate
                        ? \Carbon\Carbon::parse($request->effectivityDate)->format('Y-m-d')
                        : \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d'));

                // Only proceed if the day has actually changed
                if ($oldDay != $newDay) {
                    if ($effectiveToday) {
                        // Effective today - update main table immediately and send SMS
                        DB::table('schedules')->where('id', $scheduleId)->update([
                            'description' => $newDay,
                            'updated_at' => now(),
                        ]);

                        // Send SMS notification in background
                        register_shutdown_function(function() use ($notificationService, $oldDay, $newDay) {
                            $notificationService->sendScheduleChangeNotification(
                                'Meter Reading',
                                'Electricity',
                                $oldDay,
                                $newDay
                            );
                        });
                    } else {
                        // Not effective today - save to history with future date, don't update main table, don't send SMS yet
                        // Don't update main table yet
                    }

                    // Create a history log for the change
                    $historyData = [
                        'schedule_id' => $scheduleId,
                        'field_changed' => 'Meter Reading',
                        'old_value' => $oldDay,
                        'new_value' => $newDay,
                        'changed_by' => Auth::id() ?? 1,
                        'changed_at' => now(),
                    ];
                    
                    // Only add effectivity_date if the column exists
                    $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn('schedule_histories', 'effectivity_date');
                    if ($hasEffectivityDate) {
                        $historyData['effectivity_date'] = $effectivityDate;
                    }
                    
                    $historyId = DB::table('schedule_histories')->insertGetId($historyData);
                    
                    // Store history_id for redirect if not effective today
                    if (!$effectiveToday) {
                        $pendingChangeId = $historyId;
                    }

                    // Extract utility type from schedule type (e.g., "Meter Reading - Electricity" -> "Electricity")
                    $utilityType = str_replace('Meter Reading - ', '', $schedule->schedule_type);
                    
                    // Create specific action name
                    $actionName = "Updated {$utilityType} Meter Reading Schedule";
                    
                    AuditLogger::log(
                        $actionName,
                        'Schedules',
                        'Success',
                        ['schedule_id' => $scheduleId, 'utility_type' => $utilityType, 'old_day' => $oldDay, 'new_day' => $newDay, 'effectivity_date' => $effectivityDate]
                    );
                }
            });

            Cache::forget('meter_reading_schedule');
            Cache::forget('schedule_history');

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        if ($effectiveToday) {
            return response()->json(['message' => 'Meter reading schedule updated and notifications sent!']);
        } else {
            return response()->json([
                'message' => 'Please adjust effectivity date in Effectivity Date Management',
                'redirect' => true,
                'redirectUrl' => '/superadmin#effectivityDateManagementSection',
                'pendingChange' => [
                    'history_table' => 'schedule_histories',
                    'history_id' => $pendingChangeId,
                ],
            ]);
        }
    }

    /**
     * Display the history of schedule changes.
     */
    public function history()
    {
        return Cache::remember('schedule_history', 3600, function () {
            // Check if effectivity_date column exists
            $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn('schedule_histories', 'effectivity_date');
            
            $selectFields = ['sh.old_value', 'sh.new_value', 'sh.changed_at'];
            if ($hasEffectivityDate) {
                $selectFields[] = 'sh.effectivity_date';
            }
            
            $history = DB::table('schedule_histories as sh')
                ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
                ->join('users as u', 'sh.changed_by', '=', 'u.id')
                ->where('s.schedule_type', 'Meter Reading')
                ->select($selectFields)
                ->orderBy('sh.changed_at', 'desc')
                ->paginate(10);

            $history->getCollection()->transform(function ($item) use ($hasEffectivityDate) {
                $item->changed_at = (new \DateTime($item->changed_at))->format(\DateTime::ATOM);
                if ($hasEffectivityDate && isset($item->effectivity_date) && $item->effectivity_date) {
                    $item->effectivity_date = (new \DateTime($item->effectivity_date))->format('Y-m-d');
                } else {
                    $item->effectivity_date = null;
                }
                return $item;
            });

            return response()->json($history);
        });
    }

     /**
     * Get the Due Date and Disconnection schedules.
     */
    public function getBillingDates()
    {
        return Cache::remember('billing_date_schedules', 3600, function () {
            $scheduleTypes = [
                'Due Date - Electricity', 'Disconnection - Electricity',
                'Due Date - Water', 'Disconnection - Water',
                'Due Date - Rent', 'Disconnection - Rent'
            ];
            
            $schedules = DB::table('schedules')
                ->whereIn('schedule_type', $scheduleTypes)
                ->get()
                ->map(function ($schedule) {
                    // Ensure description is converted to string if it's numeric
                    if (isset($schedule->description) && is_numeric($schedule->description)) {
                        $schedule->description = (string) $schedule->description;
                    }
                    return $schedule;
                });

            return response()->json($schedules);
        });
    }

    public function updateBillingDates(Request $request, ChangeNotificationService $notificationService)
    {
        $validator = Validator::make($request->all(), [
            'schedules' => 'required|array|min:1',
            'schedules.*.type' => 'required|string',
            'schedules.*.day' => 'required|string|max:20', // Increased to accommodate "End of the month" (18 chars)
            'effectivityDate' => 'nullable|date',
            'effectiveToday' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $validator->errors()], 400);
        }

        try {
            // First, detect changes
            $changes = [];
            foreach ($request->input('schedules') as $scheduleData) {
                $type = $scheduleData['type'];
                $newDay = $scheduleData['day'];
                $existingSchedule = Schedule::where('schedule_type', $type)->first();
                $oldDay = $existingSchedule ? $existingSchedule->description : 'Not Set';
                
                if ($oldDay != $newDay) {
                    $utilityType = str_replace(['Due Date - ', 'Disconnection - ', 'Meter Reading - '], '', $type);
                    $changes[] = [
                        'type' => $type,
                        'utility_type' => $utilityType,
                        'old_day' => $oldDay,
                        'new_day' => $newDay,
                    ];
                }
            }

            if (empty($changes)) {
                return response()->json(['message' => 'No changes detected.']);
            }

            // Check if we need to show modal
            $effectiveToday = $request->input('effectiveToday');
            
            if ($effectiveToday === null) {
                // Return change info for modal
                return response()->json([
                    'changeDetected' => true,
                    'changeType' => 'schedule',
                    'changeData' => $changes,
                    'requiresConfirmation' => true,
                ]);
            }

            // Process based on effectiveToday
            $pendingChangeId = null;
            DB::transaction(function () use ($request, $effectiveToday, $notificationService, $changes, &$pendingChangeId) {
                // Default to 1st of next month since bills are generated monthly on the 1st
                $effectivityDate = $effectiveToday 
                    ? \Carbon\Carbon::now()->format('Y-m-d')
                    : (isset($request->effectivityDate) && $request->effectivityDate
                        ? \Carbon\Carbon::parse($request->effectivityDate)->format('Y-m-d')
                        : \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d'));
                
                foreach ($request->input('schedules') as $scheduleData) {
                    $type = $scheduleData['type'];
                    $newDay = $scheduleData['day'];

                    $existingSchedule = Schedule::where('schedule_type', $type)->first();
                    $oldDay = $existingSchedule ? $existingSchedule->description : 'Not Set';

                    if ($oldDay != $newDay) {
                        $schedule = Schedule::updateOrCreate(
                            ['schedule_type' => $type],
                            [
                                'description' => $newDay,
                                'schedule_date' => $existingSchedule->schedule_date ?? now()->toDateString()
                            ]
                        );

                        $scheduleHistoryId = DB::table('schedule_histories')->insertGetId([
                            'schedule_id' => $schedule->id,
                            'field_changed' => $schedule->schedule_type, 
                            'old_value' => $oldDay,
                            'new_value' => $newDay,
                            'changed_by' => Auth::id() ?? 1,
                            'effectivity_date' => $effectivityDate,
                        ]);
                        
                        // Store the first history ID for redirect (if not effective today)
                        if (!$effectiveToday && !isset($pendingChangeId)) {
                            $pendingChangeId = $scheduleHistoryId;
                        }

                        // Extract utility type and schedule type for specific action name
                        $utilityType = str_replace(['Due Date - ', 'Disconnection - ', 'Meter Reading - '], '', $type);
                        $scheduleType = '';
                        if (strpos($type, 'Due Date') !== false) {
                            $scheduleType = 'Due Date';
                        } elseif (strpos($type, 'Disconnection') !== false) {
                            $scheduleType = 'Disconnection';
                        } elseif (strpos($type, 'Meter Reading') !== false) {
                            $scheduleType = 'Meter Reading';
                        }
                        
                        // Create specific action name
                        $actionName = "Updated {$utilityType} {$scheduleType} Schedule";

                        AuditLogger::log(
                            $actionName,
                            'Schedules',
                            'Success',
                            ['type' => $type, 'utility_type' => $utilityType, 'schedule_type' => $scheduleType, 'old_value' => $oldDay, 'new_value' => $newDay, 'effectivity_date' => $effectivityDate]
                        );

                        // Send SMS if effective today (run in background)
                        if ($effectiveToday) {
                            register_shutdown_function(function() use ($notificationService, $type, $oldDay, $newDay) {
                                $utilityType = str_replace(['Due Date - ', 'Disconnection - ', 'Meter Reading - '], '', $type);
                                $notificationService->sendScheduleChangeNotification(
                                    $type,
                                    $utilityType,
                                    $oldDay,
                                    $newDay
                                );
                            });
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the update: ' . $e->getMessage()], 500);
        }

        Cache::forget('billing_date_schedules');
        Cache::forget('billing_dates_history');

        if ($effectiveToday) {
            return response()->json(['message' => 'Schedules updated and notifications sent!']);
        } else {
                return response()->json([
                    'message' => 'Please adjust effectivity date in Effectivity Date Management',
                    'redirect' => true,
                    'redirectUrl' => '/superadmin#effectivityDateManagementSection',
                    'pendingChange' => [
                        'history_table' => 'schedule_histories',
                        'history_id' => $pendingChangeId,
                    ],
                ]);
        }
    }

    /**
     * Get the history for Due Date and Disconnection schedules.
     */
    public function getBillingDatesHistory(Request $request)
    {
        $page = $request->input('page', 1); // Still need page for pagination

        // Check if effectivity_date column exists
        $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn('schedule_histories', 'effectivity_date');
        
        $selectFields = [
            'sh.old_value',
            'sh.new_value',
            'sh.changed_at',
            'sh.field_changed as item_changed' // Keep the alias for frontend consistency
        ];
        
        if ($hasEffectivityDate) {
            $selectFields[] = 'sh.effectivity_date';
        }

        $historyData = DB::table('schedule_histories as sh')
            ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
            ->join('users as u', 'sh.changed_by', '=', 'u.id')
            ->where(function ($query) {
                $query->where('s.schedule_type', 'like', 'Due Date - %')
                      ->orWhere('s.schedule_type', 'like', 'Disconnection - %');
            })
            ->select($selectFields)
            ->orderBy('sh.changed_at', 'desc')
            ->paginate(10); // Still paginate

        // Format date after fetching
        $historyData->getCollection()->transform(function ($item) use ($hasEffectivityDate) {
            $item->changed_at = (new \DateTime($item->changed_at))->format(\DateTime::ATOM);
            if ($hasEffectivityDate && isset($item->effectivity_date) && $item->effectivity_date) {
                $item->effectivity_date = (new \DateTime($item->effectivity_date))->format('Y-m-d');
            } else {
                $item->effectivity_date = null;
            }
            return $item;
        });

        // ✅ END OF FIX: Return fetched data directly
        return response()->json($historyData);
    }

    public function getSmsSchedules()
    {
        return Cache::remember('sms_schedules', 3600, function () {
            $scheduleTypes = [
                'SMS - Billing Statements',
                'SMS - Payment Reminders',
                'SMS - Overdue Alerts'
            ];
            $schedules = DB::table('schedules')
                ->whereIn('schedule_type', $scheduleTypes)
                ->select('id', 'schedule_type', 'description', 'schedule_day', 'sms_days')
                ->get()
                ->map(function ($schedule) {
                    // Decode JSON if it exists
                    if ($schedule->sms_days) {
                        $schedule->sms_days = json_decode($schedule->sms_days, true);
                    }
                    return $schedule;
                });

            return response()->json($schedules);
        });
    }

    /**
     * Update the SMS sending schedules.
     */
    public function updateSmsSchedules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedules' => 'required|array|min:1',
            'schedules.*.type' => 'required|string',
            'schedules.*.time' => 'required|date_format:H:i',
            'schedules.*.day' => 'nullable|integer|min:1|max:31', // For Billing Statements (day of month)
            'schedules.*.days' => 'nullable|array', // For Payment Reminders and Overdue Alerts (array of days)
            'schedules.*.days.*' => 'integer|min:0|max:365',
            'effectivityDate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $validator->errors()], 400);
        }

        try {
            DB::transaction(function () use ($request) {
                // Default to 1st of next month since bills are generated monthly on the 1st
                $effectivityDate = isset($request->effectivityDate) && $request->effectivityDate
                    ? \Carbon\Carbon::parse($request->effectivityDate)->format('Y-m-d')
                    : \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
                foreach ($request->input('schedules') as $scheduleData) {
                    $type = $scheduleData['type'];
                    $newTime = $scheduleData['time'];
                    $newDay = isset($scheduleData['day']) ? (int)$scheduleData['day'] : null;
                    $newDays = isset($scheduleData['days']) && is_array($scheduleData['days']) 
                        ? array_map('intval', $scheduleData['days']) 
                        : null;

                    $existingSchedule = Schedule::where('schedule_type', $type)->first();
                    $oldTime = $existingSchedule ? $existingSchedule->description : 'Not Set';
                    $oldDay = $existingSchedule ? $existingSchedule->schedule_day : null;
                    $oldDays = $existingSchedule && $existingSchedule->sms_days 
                        ? $existingSchedule->sms_days 
                        : null;

                    $updateData = ['description' => $newTime];
                    $timeChanged = $oldTime != $newTime;
                    $dayChanged = false;
                    $daysChanged = false;

                    // Handle Billing Statements (uses schedule_day)
                    if ($type === 'SMS - Billing Statements') {
                        if ($newDay !== null) {
                            $updateData['schedule_day'] = $newDay;
                            $dayChanged = $oldDay != $newDay;
                        }
                    }
                    // Handle Payment Reminders and Overdue Alerts (uses sms_days array)
                    else {
                        if ($newDays !== null) {
                            // Sort days array
                            sort($newDays);
                            $updateData['sms_days'] = $newDays;
                            $daysChanged = json_encode($oldDays) !== json_encode($newDays);
                        }
                    }

                    if ($timeChanged || $dayChanged || $daysChanged) {
                        $schedule = Schedule::updateOrCreate(
                            ['schedule_type' => $type],
                            $updateData
                        );

                        // Log time change
                        if ($timeChanged) {
                            DB::table('schedule_histories')->insert([
                                'schedule_id' => $schedule->id,
                                'field_changed' => $schedule->schedule_type . ' - Time',
                                'old_value' => $oldTime,
                                'new_value' => $newTime,
                                'changed_by' => Auth::id() ?? 1,
                                'effectivity_date' => $effectivityDate,
                            ]);
                        }

                        // Log day change (for Billing Statements)
                        if ($dayChanged) {
                            DB::table('schedule_histories')->insert([
                                'schedule_id' => $schedule->id,
                                'field_changed' => $schedule->schedule_type . ' - Day',
                                'old_value' => $oldDay ?? 'Not Set',
                                'new_value' => (string)$newDay,
                                'changed_by' => Auth::id() ?? 1,
                                'effectivity_date' => $effectivityDate,
                            ]);
                        }

                        // Log days change (for Payment Reminders and Overdue Alerts)
                        if ($daysChanged) {
                            $oldDaysStr = $oldDays ? implode(', ', $oldDays) : 'Not Set';
                            $newDaysStr = implode(', ', $newDays);
                            
                            DB::table('schedule_histories')->insert([
                                'schedule_id' => $schedule->id,
                                'field_changed' => $schedule->schedule_type . ' - Days',
                                'old_value' => $oldDaysStr,
                                'new_value' => $newDaysStr,
                                'changed_by' => Auth::id() ?? 1,
                                'effectivity_date' => $effectivityDate,
                            ]);
                        }

                        // Create specific action name based on schedule type
                        $actionName = '';
                        if ($type === 'SMS - Billing Statements') {
                            $actionName = 'Updated Billing Statement SMS Schedule';
                        } elseif ($type === 'SMS - Payment Reminders') {
                            $actionName = 'Updated Payment Reminder SMS Schedule';
                        } elseif ($type === 'SMS - Overdue Alerts') {
                            $actionName = 'Updated Overdue Alert SMS Schedule';
                        } else {
                            $actionName = 'Updated SMS Schedule';
                        }

                        AuditLogger::log(
                            $actionName,
                            'Schedules',
                            'Success',
                            [
                                'type' => $type,
                                'old_time' => $oldTime,
                                'new_time' => $newTime,
                                'old_day' => $oldDay,
                                'new_day' => $newDay,
                                'old_days' => $oldDays,
                                'new_days' => $newDays,
                                'effectivity_date' => $effectivityDate
                            ]
                        );
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the update: ' . $e->getMessage()], 500);
        }

        Cache::forget('sms_schedules');
        Cache::forget('sms_schedule_history');

        return response()->json(['message' => 'SMS schedules updated successfully!']);
    }

    /**
     * Get the history for SMS schedules.
     */
    public function getSmsScheduleHistory()
    {
        return Cache::remember('sms_schedule_history', 3600, function () {
            // Check if effectivity_date column exists
            $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn('schedule_histories', 'effectivity_date');
            
            $selectFields = [
                'sh.old_value',
                'sh.new_value',
                'sh.changed_at',
                'sh.field_changed as item_changed'
            ];
            
            if ($hasEffectivityDate) {
                $selectFields[] = 'sh.effectivity_date';
            }
            
            $history = DB::table('schedule_histories as sh')
                ->join('schedules as s', 'sh.schedule_id', '=', 's.id')
                ->join('users as u', 'sh.changed_by', '=', 'u.id')
                ->where('s.schedule_type', 'like', 'SMS - %')
                ->select($selectFields)
                ->orderBy('sh.changed_at', 'desc')
                ->paginate(10);

            $history->getCollection()->transform(function ($item) use ($hasEffectivityDate) {
                $item->changed_at = (new \DateTime($item->changed_at))->format(\DateTime::ATOM);
                if ($hasEffectivityDate && isset($item->effectivity_date) && $item->effectivity_date) {
                    $item->effectivity_date = (new \DateTime($item->effectivity_date))->format('Y-m-d');
                } else {
                    $item->effectivity_date = null;
                }
                return $item;
            });

            return response()->json($history);
        });
    }
}