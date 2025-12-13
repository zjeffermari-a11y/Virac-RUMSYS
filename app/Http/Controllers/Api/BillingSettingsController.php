<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillingSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\AuditTrail;
use App\Services\AuditLogger;
use App\Services\ChangeNotificationService;

class BillingSettingsController extends Controller
{
    /**
     * Display all billing settings.
     */
    public function index()
    {
        // Fetch all settings and key them by utility_type for easy frontend access
        $settings = BillingSetting::all()->keyBy('utility_type');
        return response()->json($settings);
    }

    /**
     * Update billing settings in a batch.
     */
    public function update(Request $request, ChangeNotificationService $notificationService)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.id' => 'required|integer|exists:billing_settings,id',
            'settings.*.discount_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.*.surcharge_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.*.monthly_interest_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.*.penalty_rate' => 'sometimes|numeric|min:0|max:1',
            'user_id' => 'required|integer|exists:users,id', // Add validation for user_id
            'effectivityDate' => 'nullable|date',
            'effectiveToday' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // First, detect changes
            $changes = [];
            foreach ($request->settings as $settingData) {
                $setting = BillingSetting::find($settingData['id']);
                if (!$setting) continue;

                $fieldsToCompare = [
                    'discount_rate', 'surcharge_rate',
                    'monthly_interest_rate', 'penalty_rate'
                ];

                foreach ($fieldsToCompare as $field) {
                    if (isset($settingData[$field]) && $setting->$field != $settingData[$field]) {
                        $changes[] = [
                            'billing_setting_id' => $setting->id,
                            'utility_type' => $setting->utility_type,
                            'field_changed' => str_replace('_', ' ', ucwords($field, '_')),
                            'old_value' => $setting->$field,
                            'new_value' => $settingData[$field],
                        ];
                    }
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
                    'changeType' => 'billing_setting',
                    'changeData' => $changes,
                    'requiresConfirmation' => true,
                ]);
            }

            // Process based on effectiveToday
            $user = Auth::user();

            DB::transaction(function () use ($request, $user, $effectiveToday, $notificationService, $changes) {
                // Default to 1st of next month since bills are generated monthly on the 1st
                $effectivityDate = $effectiveToday
                    ? \Carbon\Carbon::now()->format('Y-m-d')
                    : (isset($request->effectivityDate) && $request->effectivityDate
                        ? \Carbon\Carbon::parse($request->effectivityDate)->format('Y-m-d')
                        : \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d'));
                
                $allChanges = [];
                foreach ($request->settings as $settingData) {
                    $setting = BillingSetting::find($settingData['id']);
                    if (!$setting) continue;

                    $settingChanges = [];
                    $fieldsToCompare = [
                        'discount_rate', 'surcharge_rate',
                        'monthly_interest_rate', 'penalty_rate'
                    ];

                    foreach ($fieldsToCompare as $field) {
                        if (isset($settingData[$field]) && $setting->$field != $settingData[$field]) {
                            $oldValue = $setting->$field;
                            $newValue = $settingData[$field];
                            
                            $settingChanges[] = [
                                'billing_setting_id' => $setting->id,
                                'changed_by' => $request->user_id,
                                'field_changed' => str_replace('_', ' ', ucwords($field, '_')),
                                'old_value' => $oldValue * 100,
                                'new_value' => $newValue * 100,
                                'changed_at' => now(),
                                'effectivity_date' => $effectivityDate,
                            ];
                            $setting->$field = $newValue;

                            // Send SMS if effective today (run in background)
                            if ($effectiveToday) {
                                register_shutdown_function(function() use ($notificationService, $setting, $field, $oldValue, $newValue) {
                                    $notificationService->sendBillingSettingChangeNotification(
                                    $setting->utility_type,
                                    $field,
                                    $oldValue,
                                    $newValue
                                );
                                });
                            }
                        }
                    }

                    if (!empty($settingChanges)) {
                        $setting->save();
                        DB::table('billing_setting_histories')->insert($settingChanges);
                        $allChanges = array_merge($allChanges, $settingChanges);
                    }
                }
                
                Cache::forget('billing_settings');

                if (!empty($allChanges)) {
                    AuditLogger::log(
                        'Updated Billing Settings',
                        'Billing Settings',
                        'Success',
                        ['changes' => $allChanges, 'effectivity_date' => $effectivityDate]
                    );
                }
            });

            Cache::forget('billing_settings');

            if ($effectiveToday) {
                return response()->json(['message' => 'Billing settings updated and notifications sent!']);
            } else {
                return response()->json([
                    'message' => 'Please adjust effectivity date in Effectivity Date Management',
                    'redirect' => true,
                    'redirectUrl' => '/superadmin#effectivityDateManagementSection',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating settings.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Settings updated successfully!']);
    }

    /**
     * Display the history of setting changes.
     */
    public function history()
    {
        // Check if effectivity_date column exists
        $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn('billing_setting_histories', 'effectivity_date');
        
        $selectFields = [
                'bs.utility_type',
                'bsh.field_changed',
                'bsh.old_value',
                'bsh.new_value',
                'bsh.changed_at'
        ];
        
        if ($hasEffectivityDate) {
            $selectFields[] = 'bsh.effectivity_date';
        }
        
        $history = DB::table('billing_setting_histories as bsh')
            ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
            ->join('users', 'bsh.changed_by', '=', 'users.id')
            ->select($selectFields)
            ->orderBy('bsh.changed_at', 'desc')
            ->paginate(20);

        // Format dates
        $history->getCollection()->transform(function ($item) use ($hasEffectivityDate) {
            if ($hasEffectivityDate && isset($item->effectivity_date) && $item->effectivity_date) {
                $item->effectivity_date = (new \DateTime($item->effectivity_date))->format('Y-m-d');
            } else {
                $item->effectivity_date = null;
            }
            return $item;
        });

        return response()->json($history);
    }
}