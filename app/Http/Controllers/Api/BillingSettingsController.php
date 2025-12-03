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
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.id' => 'required|integer|exists:billing_settings,id',
            'settings.*.discount_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.*.surcharge_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.*.monthly_interest_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.*.penalty_rate' => 'sometimes|numeric|min:0|max:1',
            'user_id' => 'required|integer|exists:users,id', // Add validation for user_id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = Auth::user();

            DB::transaction(function () use ($request, $user) {
                $allChanges = [];
                foreach ($request->settings as $settingData) {
                    $setting = BillingSetting::find($settingData['id']);
                    if (!$setting) continue;

                    $changes = [];
                    // Define the fields we want to track for changes
                    $fieldsToCompare = [
                        'discount_rate', 'surcharge_rate',
                        'monthly_interest_rate', 'penalty_rate'
                    ];

                    foreach ($fieldsToCompare as $field) {
                        // Check if the field exists in the submitted data and is different from the DB value
                        if (isset($settingData[$field]) && $setting->$field != $settingData[$field]) {
                            $changes[] = [
                                'billing_setting_id' => $setting->id,
                                'changed_by' => $request->user_id, // Use user_id from the request
                                'field_changed' => str_replace('_', ' ', ucwords($field, '_')), // Format for readability
                                'old_value' => $setting->$field * 100, // Store as percentage
                                'new_value' => $settingData[$field] * 100, // Store as percentage
                                'changed_at' => now(),
                            ];
                            $setting->$field = $settingData[$field];
                        }
                    }

                    if (!empty($changes)) {
                        $setting->save();
                        DB::table('billing_setting_histories')->insert($changes);
                        $allChanges = array_merge($allChanges, $changes);
                    }
                }

                if (!empty($allChanges)) {
                    AuditLogger::log(
                        'Updated Billing Settings',
                        'Billing Settings',
                        'Success',
                        ['changes' => $allChanges]
                    );
                }
            });

            Cache::forget('billing_settings');

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
        $history = DB::table('billing_setting_histories as bsh')
            ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
            ->join('users', 'bsh.changed_by', '=', 'users.id')
            ->select(
                'bs.utility_type',
                'bsh.field_changed',
                'bsh.old_value',
                'bsh.new_value',
                'bsh.changed_at'
            )
            ->orderBy('bsh.changed_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }
}