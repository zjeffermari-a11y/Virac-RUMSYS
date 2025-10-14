<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
class UtilityRateController extends Controller
{
    /**
     * Fetch Electricity and Water rates from the database.
     * This method corresponds to the GET /api/utility-rates endpoint.
     */
    public function index()
    {
        $formattedRates = Cache::remember('utility_rates', 3600, function () {
            // This closure only runs if 'utility_rates' is not in the cache.
            $rates = DB::table('rates')
                        ->whereIn('utility_type', ['Electricity', 'Water'])
                        ->select('id', 'utility_type', 'rate', 'monthly_rate')
                        ->get();

            return $rates->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'utility'     => $item->utility_type,
                    'rate'        => (float) $item->rate,
                    'monthlyRate' => (float) $item->monthly_rate,
                    'unit'        => ($item->utility_type === 'Electricity') ? 'kWh' : 'day'
                ];
            });
        });

        return response()->json($formattedRates);
    }
    /**
     * Update a specific utility rate in the database.
     * This method corresponds to the PUT /api/utility-rates/{id} endpoint.
     */
    public function update(Request $request, $id)
    {
    $validator = Validator::make($request->all(), [
        'rate'        => 'required|numeric|min:0',
        'monthlyRate' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Invalid data provided.'], 400);
    }

    try {
        DB::transaction(function () use ($request, $id) {
            $rate = DB::table('rates')->where('id', $id)->first();

            if (!$rate) {
                throw new \Exception('Rate not found.');
            }

            $oldRateValue = (float) $rate->rate;
            // ADDED: Get the old monthly rate for comparison.
            $oldMonthlyRateValue = (float) $rate->monthly_rate;

            $newRateValue = (float) $request->input('rate');
            $newMonthlyRateValue = (float) $request->input('monthlyRate');
            $loggedInUserId = Auth::id() ?? 1;

            DB::table('rates')->where('id', $id)->update([
                'rate'         => $newRateValue,
                'monthly_rate' => $newMonthlyRateValue,
                'updated_at'   => now(),
            ]);

            // MODIFIED: Update the condition to check if EITHER the rate OR the monthly rate has changed.
            if ($oldRateValue !== $newRateValue || $oldMonthlyRateValue !== $newMonthlyRateValue) {
                DB::table('rate_histories')->insert([
                    'rate_id'    => $id,
                    'old_rate'   => $oldRateValue,
                    'new_rate'   => $newRateValue,
                    'changed_by' => $loggedInUserId,
                    'changed_at' => now(),
                ]);
            }
        });

        Cache::forget('utility_rates');
        Cache::forget('utility_rate_history');

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 404);
    }

        return response()->json(['message' => 'Utility rate updated successfully!']);
    }

    public function batchUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rates'        => 'required|array',
            'rates.*.id'   => 'required|integer|exists:rates,id',
            'rates.*.rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided.', 'errors' => $validator->errors()], 400);
        }

        try {
            DB::transaction(function () use ($request) {
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

                    // Only update and log if the rate is different
                    if ($oldRateValue !== $newRateValue) {
                        // Update the rate in the main table
                        DB::table('rates')
                            ->where('id', $rateData['id'])
                            ->update([
                                'rate'       => $newRateValue,
                                'updated_at' => now(),
                            ]);

                        // Add a new entry to the history table
                        DB::table('rate_histories')->insert([
                            'rate_id'    => $rateData['id'],
                            'old_rate'   => $oldRateValue,
                            'new_rate'   => $newRateValue,
                            'changed_by' => $loggedInUserId,
                            'changed_at' => now(),
                        ]);
                    }
                }
            });

            // Clear caches for both rates and history to reflect changes
            Cache::forget('utility_rates');
            Cache::forget('utility_rate_history');

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the update.'], 500);
        }

        return response()->json(['message' => 'Utility rates updated successfully!']);
    }

    public function history(Request $request)
    {
        $page = $request->input('page', 1);
        $cacheKey = 'utility_rate_history_page_' . $page;

        // 4. Cache the history for 60 minutes
        $history = Cache::remember($cacheKey, 3600, function () {
            $historyData = DB::table('rate_histories as rh')
                ->join('rates as r', 'rh.rate_id', '=', 'r.id')
                ->join('users as u', 'rh.changed_by', '=', 'u.id')
                ->whereIn('r.utility_type', ['Electricity', 'Water'])
                ->select('rh.old_rate', 'rh.new_rate', 'rh.changed_at', 'r.utility_type')
                ->orderBy('rh.changed_at', 'desc')
                ->paginate(10);
            
            $historyData->getCollection()->transform(function ($item) {
                $item->changed_at = (new \DateTime($item->changed_at))->format(\DateTime::ATOM);
                return $item;
            });
            
            return $historyData;
        });

        return response()->json($history);
    }
}