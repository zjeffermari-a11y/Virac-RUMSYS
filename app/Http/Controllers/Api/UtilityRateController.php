<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\AuditLogger;
use App\Services\ChangeNotificationService;

class UtilityRateController extends Controller
{
    /**
     * Fetch Electricity and Water rates from the database.
     * This method corresponds to the GET /api/utility-rates endpoint.
     */
    public function index(Request $request = null)
    {
        try {
            $formattedRates = Cache::remember('utility_rates', 3600, function () {
                // This closure only runs if 'utility_rates' is not in the cache.
                $rates = DB::table('rates')
                            ->whereIn('utility_type', ['Electricity', 'Water'])
                            ->select('id', 'utility_type', 'rate', 'monthly_rate')
                            ->get();

                \Log::info('Utility rates query result count: ' . $rates->count());
                
                if ($rates->isEmpty()) {
                    \Log::warning('No utility rates found in database! Attempting to create default rates...');
                    
                    // Create default rates if they don't exist
                    $electricityExists = DB::table('rates')->where('utility_type', 'Electricity')->exists();
                    $waterExists = DB::table('rates')->where('utility_type', 'Water')->exists();
                    
                    if (!$electricityExists) {
                        DB::table('rates')->insert([
                            'utility_type' => 'Electricity',
                            'section_id' => null,
                            'rate' => 31.00,
                            'monthly_rate' => 0.00,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        \Log::info('Created default Electricity rate');
                    }
                    
                    if (!$waterExists) {
                        DB::table('rates')->insert([
                            'utility_type' => 'Water',
                            'section_id' => null,
                            'rate' => 6.00,
                            'monthly_rate' => 0.00,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        \Log::info('Created default Water rate');
                    }
                    
                    // Re-fetch rates after creation
                    $rates = DB::table('rates')
                                ->whereIn('utility_type', ['Electricity', 'Water'])
                                ->select('id', 'utility_type', 'rate', 'monthly_rate')
                                ->get();
                }

                $formatted = $rates->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'utility'     => $item->utility_type,
                        'rate'        => (float) $item->rate,
                        'monthlyRate' => (float) $item->monthly_rate,
                        'unit'        => ($item->utility_type === 'Electricity') ? 'kWh' : 'day'
                    ];
                })->values()->all(); // Convert to array and re-index

                \Log::info('Formatted utility rates count: ' . count($formatted));
                return $formatted;
            });

            // Ensure we always return an array
            if (!is_array($formattedRates)) {
                \Log::warning('Formatted rates is not an array: ' . gettype($formattedRates));
                $formattedRates = [];
            }

            \Log::info('Returning utility rates count: ' . count($formattedRates));
            
            // Always return JSON response - getData(true) will extract the data correctly
            return response()->json($formattedRates);
        } catch (\Exception $e) {
            \Log::error("Error fetching utility rates: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            if ($request && $request->wantsJson()) {
                return response()->json([], 500);
            }
            return response()->json(['data' => []], 500);
        }
    }
    /**
     * Update a specific utility rate in the database.
     * This method corresponds to the PUT /api/utility-rates/{id} endpoint.
     */
    public function update(Request $request, $id, ChangeNotificationService $notificationService)
    {
    $validator = Validator::make($request->all(), [
        'rate'        => 'required|numeric|min:0',
        'monthlyRate' => 'required|numeric|min:0',
        'effectivityDate' => 'nullable|date',
        'effectiveToday' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Invalid data provided.'], 400);
    }

    try {
        $rate = DB::table('rates')->where('id', $id)->first();

        if (!$rate) {
            throw new \Exception('Rate not found.');
        }

        $oldRateValue = (float) $rate->rate;
        $oldMonthlyRateValue = (float) $rate->monthly_rate;
        $newRateValue = (float) $request->input('rate');
        $newMonthlyRateValue = (float) $request->input('monthlyRate');

        // Check if rate changed
        $rateChanged = $oldRateValue !== $newRateValue || $oldMonthlyRateValue !== $newMonthlyRateValue;

        if (!$rateChanged) {
            // No change, just update without notification
            DB::table('rates')->where('id', $id)->update([
                'rate'         => $newRateValue,
                'monthly_rate' => $newMonthlyRateValue,
                'updated_at'   => now(),
            ]);
            Cache::forget('utility_rates');
            return response()->json(['message' => 'Utility rate updated successfully!']);
        }

        // Rate changed - check if we need to show modal
        $effectiveToday = $request->input('effectiveToday');
        
        if ($effectiveToday === null) {
            // Return change info for modal
            return response()->json([
                'changeDetected' => true,
                'changeType' => 'utility_rate',
                'changeData' => [
                    'rate_id' => $id,
                    'utility_type' => $rate->utility_type,
                    'old_rate' => $oldRateValue,
                    'new_rate' => $newRateValue,
                    'old_monthly_rate' => $oldMonthlyRateValue,
                    'new_monthly_rate' => $newMonthlyRateValue,
                ],
                'requiresConfirmation' => true,
            ]);
        }

        // Process based on effectiveToday
        DB::transaction(function () use ($request, $id, $oldRateValue, $newRateValue, $oldMonthlyRateValue, $newMonthlyRateValue, $rate, $effectiveToday, $notificationService) {
            $loggedInUserId = Auth::id() ?? 1;

            if ($effectiveToday) {
                // Effective today - save with today's date, update main table, send SMS
                $effectivityDate = \Carbon\Carbon::now()->format('Y-m-d');
                
                // Update main table immediately
                DB::table('rates')->where('id', $id)->update([
                    'rate'         => $newRateValue,
                    'monthly_rate' => $newMonthlyRateValue,
                    'updated_at'   => now(),
                ]);

                // Save to history
                DB::table('rate_histories')->insert([
                    'rate_id'    => $id,
                    'old_rate'   => $oldRateValue,
                    'new_rate'   => $newRateValue,
                    'changed_by' => $loggedInUserId,
                    'changed_at' => now(),
                    'effectivity_date' => $effectivityDate,
                ]);

                AuditLogger::log(
                    'Updated Utility Rate',
                    'Utility Rates',
                    'Success',
                    ['rate_id' => $id, 'old_rate' => $oldRateValue, 'new_rate' => $newRateValue, 'old_monthly_rate' => $oldMonthlyRateValue, 'new_monthly_rate' => $newMonthlyRateValue, 'effectivity_date' => $effectivityDate]
                );

                // Send SMS notification and regenerate bills in background
                $utilityType = $rate->utility_type; // Store in variable for closure
                register_shutdown_function(function() use ($notificationService, $utilityType, $oldRateValue, $newRateValue, $oldMonthlyRateValue, $newMonthlyRateValue) {
                    $notificationService->sendRateChangeNotification(
                        $utilityType,
                        $oldRateValue,
                        $newRateValue,
                        $oldMonthlyRateValue,
                        $newMonthlyRateValue
                    );
                });
            } else {
                // Not effective today - save to history with default future date (appears in Effectivity Date Management)
                // Don't update main table, don't send SMS yet
                $effectivityDate = \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
                
                // Save to history only and capture the history_id
                $historyId = DB::table('rate_histories')->insertGetId([
                    'rate_id'    => $id,
                    'old_rate'   => $oldRateValue,
                    'new_rate'   => $newRateValue,
                    'changed_by' => $loggedInUserId,
                    'changed_at' => now(),
                    'effectivity_date' => $effectivityDate,
                ]);

                AuditLogger::log(
                    'Updated Utility Rate',
                    'Utility Rates',
                    'Success',
                    ['rate_id' => $id, 'old_rate' => $oldRateValue, 'new_rate' => $newRateValue, 'old_monthly_rate' => $oldMonthlyRateValue, 'new_monthly_rate' => $newMonthlyRateValue, 'effectivity_date' => $effectivityDate]
                );
                
                // Store history_id for redirect
                $pendingChangeId = $historyId;
            }
        });

        Cache::forget('utility_rates');
        Cache::forget('utility_rate_history');

        if ($effectiveToday) {
            return response()->json(['message' => 'Utility rate updated and notifications sent!']);
        } else {
            return response()->json([
                'message' => 'Please adjust effectivity date in Effectivity Date Management',
                'redirect' => true,
                'redirectUrl' => '/superadmin#effectivityDateManagementSection',
                'pendingChange' => [
                    'history_table' => 'rate_histories',
                    'history_id' => $pendingChangeId ?? null,
                ],
            ]);
        }

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 404);
    }
    }

    public function batchUpdate(Request $request, ChangeNotificationService $notificationService)
    {
        $validator = Validator::make($request->all(), [
            'rates'        => 'required|array',
            'rates.*.id'   => 'required|integer|exists:rates,id',
            'rates.*.rate' => 'required|numeric|min:0',
            'rates.*.effectivityDate' => 'nullable|date',
            'effectiveToday' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $validator->errors()], 400);
        }

        try {
            // First, detect changes
            $changes = [];
            foreach ($request->input('rates') as $rateData) {
                $currentRate = DB::table('rates')->where('id', $rateData['id'])->first();
                if (!$currentRate) continue;

                $oldRateValue = (float) $currentRate->rate;
                $newRateValue = (float) $rateData['rate'];
                $oldMonthlyRateValue = (float) $currentRate->monthly_rate;
                $newMonthlyRateValue = isset($rateData['monthlyRate']) ? (float) $rateData['monthlyRate'] : $oldMonthlyRateValue;

                if ($oldRateValue !== $newRateValue || $oldMonthlyRateValue !== $newMonthlyRateValue) {
                    $changes[] = [
                        'rate_id' => $rateData['id'],
                        'utility_type' => $currentRate->utility_type,
                        'old_rate' => $oldRateValue,
                        'new_rate' => $newRateValue,
                        'old_monthly_rate' => $oldMonthlyRateValue,
                        'new_monthly_rate' => $newMonthlyRateValue,
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
                    'changeType' => 'utility_rate_batch',
                    'changeData' => $changes,
                    'requiresConfirmation' => true,
                ]);
            }

            // Process based on effectiveToday
            DB::transaction(function () use ($request, $effectiveToday, $notificationService) {
                $rates = $request->input('rates');
                $loggedInUserId = Auth::id() ?? 1;

                foreach ($rates as $rateData) {
                    // Get the current rate to check if the value has changed
                    $currentRate = DB::table('rates')->where('id', $rateData['id'])->first();
                    if (!$currentRate) {
                        continue;
                    }

                    $oldRateValue = (float) $currentRate->rate;
                    $newRateValue = (float) $rateData['rate'];
                    $oldMonthlyRateValue = (float) $currentRate->monthly_rate;
                    $newMonthlyRateValue = isset($rateData['monthlyRate']) ? (float) $rateData['monthlyRate'] : $oldMonthlyRateValue;
                    
                    // Only update and log if the rate is different
                    if ($oldRateValue !== $newRateValue || $oldMonthlyRateValue !== $newMonthlyRateValue) {
                        if ($effectiveToday) {
                            // Effective today - update main table immediately, send SMS
                            $effectivityDate = \Carbon\Carbon::now()->format('Y-m-d');
                            
                            // Update the rate in the main table
                            $updateData = [
                                'rate'       => $newRateValue,
                                'updated_at' => now(),
                            ];
                            if (isset($rateData['monthlyRate'])) {
                                $updateData['monthly_rate'] = $newMonthlyRateValue;
                            }
                            DB::table('rates')
                                ->where('id', $rateData['id'])
                                ->update($updateData);

                            // Send SMS notification and regenerate bills in background
                            $utilityType = $currentRate->utility_type; // Store in variable for closure
                            register_shutdown_function(function() use ($notificationService, $utilityType, $oldRateValue, $newRateValue, $oldMonthlyRateValue, $newMonthlyRateValue) {
                                $notificationService->sendRateChangeNotification(
                                    $utilityType,
                                    $oldRateValue,
                                    $newRateValue,
                                    $oldMonthlyRateValue,
                                    $newMonthlyRateValue
                                );
                            });
                        } else {
                            // Not effective today - save to history with default future date
                            $effectivityDate = \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
                        }

                        // Add a new entry to the history table
                        DB::table('rate_histories')->insert([
                            'rate_id'    => $rateData['id'],
                            'old_rate'   => $oldRateValue,
                            'new_rate'   => $newRateValue,
                            'changed_by' => $loggedInUserId,
                            'changed_at' => now(),
                            'effectivity_date' => $effectivityDate,
                        ]);
                    }
                }

                $auditDetails = ['count' => count($rates), 'changes' => []];
                foreach ($rates as $rateData) {
                    if (isset($rateData['effectivityDate'])) {
                        $auditDetails['changes'][] = [
                            'rate_id' => $rateData['id'],
                            'effectivity_date' => $rateData['effectivityDate']
                        ];
                    }
                }
                AuditLogger::log(
                    'Batch Updated Utility Rates',
                    'Utility Rates',
                    'Success',
                    $auditDetails
                );
            });

            // Clear caches for both rates and history to reflect changes
            Cache::forget('utility_rates');
            Cache::forget('utility_rate_history');

            if ($effectiveToday) {
                return response()->json(['message' => 'Utility rates updated and notifications sent!']);
            } else {
                return response()->json([
                    'message' => 'Please adjust effectivity date in Effectivity Date Management',
                    'redirect' => true,
                    'redirectUrl' => '/superadmin#effectivityDateManagementSection',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the update.'], 500);
        }
    }

    public function history(Request $request)
    {
        // ✅ START OF FIX: Removed Cache::remember wrapper
        $page = $request->input('page', 1); // Still need page for pagination

        $historyData = DB::table('rate_histories as rh')
            ->join('rates as r', 'rh.rate_id', '=', 'r.id')
            ->join('users as u', 'rh.changed_by', '=', 'u.id')
            ->whereIn('r.utility_type', ['Electricity', 'Water'])
            ->select('rh.old_rate', 'rh.new_rate', 'rh.changed_at', 'rh.effectivity_date', 'r.utility_type')
            ->orderBy('rh.changed_at', 'desc')
            ->paginate(10); // Still paginate the results

        // Format the date after fetching
        $historyData->getCollection()->transform(function ($item) {
            // Ensure changed_at is treated as DateTime object for consistent formatting
            $item->changed_at = (new \DateTime($item->changed_at))->format(\DateTime::ATOM);
            if ($item->effectivity_date) {
                $item->effectivity_date = (new \DateTime($item->effectivity_date))->format('Y-m-d');
            }
            return $item;
        });

        // ✅ END OF FIX: Return the fetched data directly
        return response()->json($historyData);
    }
}