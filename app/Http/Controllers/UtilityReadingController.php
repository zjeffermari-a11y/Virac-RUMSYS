<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UtilityReading;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Billing;
use App\Models\Rate;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogger;

class UtilityReadingController extends Controller
{

public function storeBulk(Request $request)
{
    $validated = $request->validate([
        'readings' => 'required|array',
        'readings.*.id' => 'required|integer|exists:utility_readings,id',
        'readings.*.value' => 'required|numeric|min:0',
        'readings.*.previous' => 'nullable|numeric|min:0',
    ]);

    $savedReadings = [];
    
    // Fetch necessary rates and schedules once to be efficient
    $electricityRate = Rate::where('utility_type', 'Electricity')->value('rate');
    $schedules = DB::table('schedules')->get()->keyBy('schedule_type');

    try {
        DB::beginTransaction();

        foreach ($validated['readings'] as $readingData) {
            // Eager load the edit requests associated with this reading
            $reading = UtilityReading::with('editRequests')->find($readingData['id']);
            
            if ($reading) {
                $updateData = ['current_reading' => $readingData['value']];
                $finalPreviousReading = (float)$reading->previous_reading;

                if (isset($readingData['previous'])) {
                    $updateData['previous_reading'] = $readingData['previous'];
                    $finalPreviousReading = (float)$readingData['previous'];
                }

                $oldCurrentReading = $reading->current_reading;
                $oldPreviousReading = $reading->previous_reading;
                
                $reading->update($updateData);

                // Log the reading update
                AuditLogger::log(
                    'Updated Utility Reading',
                    'Utility Readings',
                    'Success',
                    [
                        'reading_id' => $reading->id,
                        'stall_id' => $reading->stall_id,
                        'utility_type' => $reading->utility_type,
                        'old_current_reading' => $oldCurrentReading,
                        'new_current_reading' => $reading->current_reading,
                        'old_previous_reading' => $oldPreviousReading,
                        'new_previous_reading' => $reading->previous_reading ?? $oldPreviousReading,
                    ]
                );
                
                $approvedRequest = $reading->editRequests->where('status', 'approved')->first();
                if ($approvedRequest) {
                    $approvedRequest->delete();
                }

                // --- REAL-TIME BILLING LOGIC ---
                if ($electricityRate) {
                    $consumption = $reading->current_reading - $finalPreviousReading;
                    if ($consumption < 0) {
                        $consumption = 0; // Or log an error for review
                    }
                    $amount = $consumption * $electricityRate;
                    $readingDate = Carbon::parse($reading->reading_date);

                    $periodStart = $readingDate->copy()->startOfMonth();
                    $periodEnd = $readingDate->copy()->endOfMonth();
                    
                    $dueDatePeriod = $readingDate->copy()->addMonth();

                    $dueDateKey = "Due Date - Electricity";
                    $disconnectionKey = "Disconnection - Electricity";
                    $dueDay = $schedules->get($dueDateKey)?->description;
                    $disconnectionDay = $schedules->get($disconnectionKey)?->description;

                    Billing::updateOrCreate(
                        [
                            'stall_id' => $reading->stall_id,
                            'utility_type' => 'Electricity',
                            'period_start' => $periodStart->toDateString(),
                        ],
                        [
                            'period_end' => $periodEnd->toDateString(),
                            'amount' => $amount,
                            'previous_reading' => $finalPreviousReading,
                            'current_reading' => $reading->current_reading,
                            'consumption' => $consumption,
                            'rate' => $electricityRate,
                            'due_date' => is_numeric($dueDay) ? $dueDatePeriod->copy()->day((int)$dueDay)->toDateString() : null,
                            'disconnection_date' => is_numeric($disconnectionDay) ? $dueDatePeriod->copy()->day((int)$disconnectionDay)->toDateString() : null,
                            'status' => 'unpaid'
                        ]
                    );
                }
                
                $savedReadings[] = [
                    'utility_reading_id' => $reading->id,
                    'currentReading' => (float)$readingData['value'],
                    'previousReading' => $finalPreviousReading,
                    'status' => 'submitted',
                ];
            }
        }

        DB::commit();

        try {
            $meterReader = Auth::user();
            $admin = User::whereHas('role', fn ($q) => $q->where('name', 'Admin'))->first();

            if ($admin && $meterReader && count($savedReadings) > 0) {
                $notificationTitle = 'New Meter Readings Submitted';
                $notificationMessage = "{$meterReader->name} has submitted " . count($savedReadings) . " new meter readings.";

                // In-App Notification for Admin
                DB::table('notifications')->insert([
                    'recipient_id' => $admin->id,
                    'sender_id' => $meterReader->id,
                    'channel' => 'in_app',
                    'title' => $notificationTitle,
                    'message' => json_encode(['text' => $notificationMessage]),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // SMS Notification for Admin
                $adminContact = $admin->getSemaphoreReadyContactNumber();
                if ($adminContact) {
                    $smsService = new SmsService();
                    $smsService->send($adminContact, "RUMSYS: " . $notificationMessage);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification for bulk reading submission: ' . $e->getMessage());
            // Do not fail the main request, just log the error.
        }

        AuditLogger::log(
            'Submitted Meter Readings',
            'Utility Readings',
            'Success',
            ['count' => count($savedReadings), 'readings' => $savedReadings]
        );

        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to save readings.', 'error' => $e->getMessage()], 500);
    }

    return response()->json($savedReadings);
    }
}