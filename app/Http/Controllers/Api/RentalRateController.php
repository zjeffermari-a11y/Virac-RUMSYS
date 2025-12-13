<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Stall; // It's better to use the model
use App\Services\AuditLogger;
use App\Services\ChangeNotificationService;

class RentalRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Stall::query()
            ->join('sections as sec', 'stalls.section_id', '=', 'sec.id')
            ->select(
                'stalls.id',
                'sec.name as section',
                'stalls.table_number as tableNumber',
                'stalls.daily_rate as dailyRate',
                'stalls.monthly_rate as monthlyRate',
                'stalls.area' // ✅ FIX #1: Added 'area' to the data being fetched
            );

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('stalls.table_number', 'LIKE', "%{$searchTerm}%");
        }

        if ($request->filled('section')) {
            $sectionName = $request->input('section');
            $query->where('sec.name', $sectionName);
        }

        if ($request->filled('section')) {
            $rates = $query->orderBy('stalls.id')->paginate(15);
        } else {
            $allRates = $query->orderBy('stalls.id')->get();
            return response()->json(['data' => $allRates]);
        }

        return response()->json($rates);
    }

    /**
     * Get the next available table number for a given section.
     */
    public function getNextTableNumber($sectionName)
    {
        $section = DB::table('sections')->where('name', $sectionName)->first();

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $maxTableNumber = DB::table('stalls')
            ->where('section_id', $section->id)
            ->max(DB::raw("CAST(REGEXP_SUBSTR(table_number, '[0-9]+$') AS UNSIGNED)"));

        return response()->json(['next_table_number' => ((int)$maxTableNumber) + 1]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'section' => 'required|string|exists:sections,name',
            'tableNumber' => 'required|string|max:255',
            'dailyRate' => 'nullable|numeric|min:0',
            // 'monthlyRate' => 'nullable|numeric|min:0', // <-- REMOVE THIS LINE
            'area' => 'nullable|numeric|min:0',
        ]);

        $section = DB::table('sections')->where('name', $validatedData['section'])->first();

        $stallId = DB::table('stalls')->insertGetId([
            'section_id' => $section->id,
            'table_number' => $validatedData['tableNumber'],
            'daily_rate' => $validatedData['dailyRate'] ?? 0.00,
            // 'monthly_rate' is now calculated by the DB, so we remove it here
            'area' => $validatedData['area'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLogger::log(
            'Created Stall',
            'Rental Rates',
            'Success',
            ['stall_id' => $stallId, 'section' => $validatedData['section'], 'table_number' => $validatedData['tableNumber']]
        );

        return response()->json(['message' => 'Stall created successfully', 'id' => $stallId], 201);
    }

    /**
     * Update multiple stall rates at once.
     */
// In RentalRateController.php -> batchUpdate()
    public function batchUpdate(Request $request, ChangeNotificationService $notificationService)
    {
        $validatedData = $request->validate([
            'stalls' => 'required|array',
            'stalls.*.id' => 'required|integer|exists:stalls,id',
            'stalls.*.tableNumber' => 'required|string',
            'stalls.*.dailyRate' => 'required|numeric',
            // 'stalls.*.monthlyRate' => 'required|numeric', // <-- REMOVE THIS LINE
            'stalls.*.area' => 'nullable|numeric|min:0',
            'effectivityDate' => 'nullable|date',
            'effectiveToday' => 'nullable|boolean',
        ]);

        // First, detect changes
        $changes = [];
        foreach ($validatedData['stalls'] as $stallData) {
            $stall = Stall::with('section')->find($stallData['id']);
            if (!$stall) continue;

            $oldDailyRate = (float) $stall->daily_rate;
            $oldMonthlyRate = (float) $stall->monthly_rate;
            $newDailyRate = (float) $stallData['dailyRate'];
            $newMonthlyRate = isset($stallData['monthlyRate']) ? (float) $stallData['monthlyRate'] : ($newDailyRate * 30);
            
            $epsilon = 0.01;
            $dailyRateChanged = abs($oldDailyRate - $newDailyRate) > $epsilon;
            $monthlyRateChanged = abs($oldMonthlyRate - $newMonthlyRate) > $epsilon;

            if ($dailyRateChanged || $monthlyRateChanged) {
                $changes[] = [
                    'stall_id' => $stall->id,
                    'table_number' => $stall->table_number,
                    'old_daily_rate' => $oldDailyRate,
                    'new_daily_rate' => $newDailyRate,
                    'old_monthly_rate' => $oldMonthlyRate,
                    'new_monthly_rate' => $newMonthlyRate,
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
                'changeType' => 'rental_rate_batch',
                'changeData' => $changes,
                'requiresConfirmation' => true,
            ]);
        }

        DB::transaction(function () use ($validatedData, $effectiveToday, $notificationService) {
            // Default to 1st of next month since bills are generated monthly on the 1st
            $effectivityDate = isset($validatedData['effectivityDate']) && $validatedData['effectivityDate']
                ? \Carbon\Carbon::parse($validatedData['effectivityDate'])->format('Y-m-d')
                : \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
            $auditDetails = [
                'count' => count($validatedData['stalls']),
                'effectivity_date' => $effectivityDate,
                'changes' => []
            ];
            
            foreach ($validatedData['stalls'] as $stallData) {
                $stall = Stall::with('section')->find($stallData['id']);
                if ($stall) {
                    // Capture old values BEFORE update
                    $oldDailyRate = (float) $stall->daily_rate;
                    $oldMonthlyRate = (float) $stall->monthly_rate;
                    $oldTableNumber = $stall->table_number;
                    $newDailyRate = (float) $stallData['dailyRate'];
                    
                    // Calculate new monthly rate (typically daily_rate * 30, but check if it's stored)
                    $newMonthlyRate = isset($stallData['monthlyRate']) ? (float) $stallData['monthlyRate'] : ($newDailyRate * 30);
                    
                    $stall->update([
                        'table_number' => $stallData['tableNumber'],
                        'daily_rate' => $newDailyRate,
                        'area' => $stallData['area'],
                    ]);
                    
                    // Refresh to get updated monthly_rate if it's calculated by DB
                    $stall->refresh();
                    $newMonthlyRate = (float) $stall->monthly_rate;
                    
                    // Create draft announcement if rates changed
                    // Use small epsilon for floating point comparison
                    $epsilon = 0.01;
                    $dailyRateChanged = abs($oldDailyRate - $newDailyRate) > $epsilon;
                    $monthlyRateChanged = abs($oldMonthlyRate - $newMonthlyRate) > $epsilon;
                    $tableNumberChanged = $oldTableNumber !== $stallData['tableNumber'];
                    
                    // Only store change details for audit log if something actually changed
                    if ($dailyRateChanged || $monthlyRateChanged || $tableNumberChanged) {
                        $auditDetails['changes'][] = [
                            'id' => $stall->id,
                            'table_number' => $stallData['tableNumber'],
                            'old_table_number' => $oldTableNumber,
                            'section' => $stall->section->name ?? 'N/A',
                            'old_daily_rate' => $oldDailyRate,
                            'new_daily_rate' => $newDailyRate,
                            'old_monthly_rate' => $oldMonthlyRate,
                            'new_monthly_rate' => $newMonthlyRate,
                            'effectivity_date' => $effectivityDate,
                        ];
                    }
                    
                    if ($dailyRateChanged || $monthlyRateChanged) {
                        // Send SMS if effective today (run in background)
                        if ($effectiveToday) {
                            register_shutdown_function(function() use ($notificationService, $stall, $oldDailyRate, $newDailyRate, $oldMonthlyRate, $newMonthlyRate) {
                                $notificationService->sendRentalRateChangeNotification(
                                $stall,
                                $oldDailyRate,
                                $newDailyRate,
                                $oldMonthlyRate,
                                $newMonthlyRate
                            );
                            });
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::debug('No rate change detected for stall', [
                            'stall_id' => $stall->id,
                            'old_daily' => $oldDailyRate,
                            'new_daily' => $newDailyRate,
                            'old_monthly' => $oldMonthlyRate,
                            'new_monthly' => $newMonthlyRate,
                        ]);
                    }
                }
            }
            
            // Only log if there were actual changes
            if (count($auditDetails['changes']) > 0) {
                AuditLogger::log(
                    'Updated Rental Rates',
                    'Rental Rates',
                    'Success',
                    $auditDetails
                );
            }
        });

        if ($effectiveToday) {
            return response()->json(['message' => 'Rental rates updated and notifications sent!']);
        } else {
            return response()->json([
                'message' => 'Please adjust effectivity date in Effectivity Date Management',
                'redirect' => true,
                'redirectUrl' => '/superadmin#effectivityDateManagementSection',
            ]);
        }
    }


    /**
     * Update a specific stall's rental rate.
     */
    public function update(Request $request, $stall, ChangeNotificationService $notificationService)
    {
        $validatedData = $request->validate([
            'tableNumber' => 'sometimes|string',
            'dailyRate' => 'sometimes|numeric|min:0',
            'area' => 'sometimes|numeric|min:0',
            'effectivityDate' => 'nullable|date',
            'effectiveToday' => 'nullable|boolean',
        ]);

            $stallModel = Stall::with('section')->find($stall);
            
            if (!$stallModel) {
                throw new \Exception('Stall not found.');
            }

            $oldDailyRate = (float) $stallModel->daily_rate;
            $oldMonthlyRate = (float) $stallModel->monthly_rate;
            
        // Check if rate will change
        $newDailyRate = isset($validatedData['dailyRate']) ? (float) $validatedData['dailyRate'] : $oldDailyRate;
        $rateChanged = $oldDailyRate !== $newDailyRate;

        if (!$rateChanged && !isset($validatedData['tableNumber']) && !isset($validatedData['area'])) {
            // No change, just update if other fields changed
            $stallModel->update(array_filter($validatedData));
            return response()->json(['message' => 'Rental rate updated successfully!']);
        }

        // Rate changed - check if we need to show modal
        $effectiveToday = $request->input('effectiveToday');
        
        if ($rateChanged && $effectiveToday === null) {
            // Return change info for modal
            $stallModel->refresh();
            $newMonthlyRate = (float) $stallModel->monthly_rate;
            return response()->json([
                'changeDetected' => true,
                'changeType' => 'rental_rate',
                'changeData' => [
                    'stall_id' => $stallModel->id,
                    'table_number' => $stallModel->table_number,
                    'old_daily_rate' => $oldDailyRate,
                    'new_daily_rate' => $newDailyRate,
                    'old_monthly_rate' => $oldMonthlyRate,
                    'new_monthly_rate' => $newMonthlyRate,
                ],
                'requiresConfirmation' => true,
            ]);
        }

        // Process based on effectiveToday
        DB::transaction(function () use ($validatedData, $stallModel, $oldDailyRate, $newDailyRate, $oldMonthlyRate, $effectiveToday, $notificationService) {
            $stallModel->update(array_filter($validatedData));
            $stallModel->refresh();
            
            $newMonthlyRate = (float) $stallModel->monthly_rate;
            
            if ($effectiveToday) {
                // Effective today - update immediately, send SMS
                $effectivityDate = \Carbon\Carbon::now()->format('Y-m-d');
                
                // Send SMS notification and regenerate bills in background
                register_shutdown_function(function() use ($notificationService, $stallModel, $oldDailyRate, $newDailyRate, $oldMonthlyRate, $newMonthlyRate) {
                    $notificationService->sendRentalRateChangeNotification(
                        $stallModel,
                        $oldDailyRate,
                        $newDailyRate,
                        $oldMonthlyRate,
                        $newMonthlyRate
                    );
                });
            } else {
                // Not effective today - save with default future date
                $effectivityDate = \Carbon\Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
            }
            
            // Store audit details
            $auditDetails = [
                'stall_id' => $stallModel->id,
                'table_number' => $stallModel->table_number,
                'section' => $stallModel->section->name ?? 'N/A',
                'old_daily_rate' => $oldDailyRate,
                'new_daily_rate' => $newDailyRate,
                'old_monthly_rate' => $oldMonthlyRate,
                'new_monthly_rate' => $newMonthlyRate,
                'effectivity_date' => $effectivityDate,
            ];
            
            AuditLogger::log(
                'Updated Rental Rate',
                'Rental Rates',
                'Success',
                $auditDetails
            );
        });

        if ($effectiveToday) {
            return response()->json(['message' => 'Rental rate updated and notifications sent!']);
        } else {
            return response()->json([
                'message' => 'Please adjust effectivity date in Effectivity Date Management',
                'redirect' => true,
                'redirectUrl' => '/superadmin#effectivityDateManagementSection',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stall = DB::table('stalls')->where('id', $id)->first();
        if ($stall) {
            AuditLogger::log(
                'Deleted Stall',
                'Rental Rates',
                'Success',
                ['stall_id' => $id, 'table_number' => $stall->table_number]
            );
            DB::table('stalls')->where('id', $id)->delete();
        }
        return response()->json(null, 204);
    }

    /**
     * Get the history of rental rate changes.
     */
    public function history(Request $request)
    {
        $page = $request->input('page', 1);

        // Get rental rate changes from audit trails
        // Check if details column exists, if not, select without it
        $hasDetailsColumn = DB::getSchemaBuilder()->hasColumn('audit_trails', 'details');
        
        $selectFields = ['id', 'action', 'created_at as changed_at'];
        if ($hasDetailsColumn) {
            $selectFields[] = 'details';
        }
        
        // Debug: Log what we're querying
        \Log::info('Rental Rate History Query', [
            'hasDetailsColumn' => $hasDetailsColumn,
            'selectFields' => $selectFields
        ]);
        
        // Query audit trails for rental rate changes
        // Try to match the exact action names used in AuditLogger::log calls
        $historyData = DB::table('audit_trails')
            ->where('module', 'Rental Rates')
            ->where(function ($query) {
                $query->where('action', 'Updated Rental Rate')
                      ->orWhere('action', 'Updated Rental Rates')
                      ->orWhere('action', 'Created Stall')
                      ->orWhere('action', 'Deleted Stall');
            })
            ->select($selectFields)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Debug: Log what we found
        \Log::info('Rental Rate History Query Results', [
            'total_found' => $historyData->total(),
            'count' => $historyData->count(),
            'has_details_column' => $hasDetailsColumn,
            'first_record' => $historyData->first() ? [
                'id' => $historyData->first()->id ?? null,
                'action' => $historyData->first()->action ?? null,
                'has_details' => isset($historyData->first()->details),
                'details_preview' => isset($historyData->first()->details) ? substr($historyData->first()->details, 0, 100) : null,
            ] : null
        ]);
        
        // If no results, try a broader query to see what actions exist
        if ($historyData->total() === 0) {
            $sampleActions = DB::table('audit_trails')
                ->where('module', 'Rental Rates')
                ->select('action')
                ->distinct()
                ->pluck('action');
            
            $allModules = DB::table('audit_trails')
                ->select('module')
                ->distinct()
                ->pluck('module');
            
            $totalRentalRatesRecords = DB::table('audit_trails')
                ->where('module', 'Rental Rates')
                ->count();
            
            \Log::info('No rental rate history found. Debug info:', [
                'actions_for_rental_rates' => $sampleActions->toArray(),
                'all_modules' => $allModules->toArray(),
                'total_audit_records' => DB::table('audit_trails')->count(),
                'total_rental_rates_records' => $totalRentalRatesRecords,
                'sample_actions_all_modules' => DB::table('audit_trails')
                    ->select('module', 'action')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return $item->module . ' - ' . $item->action;
                    })
                    ->take(20)
                    ->toArray()
            ]);
        }
        
        // Debug: Log what we found (already logged above)

        // Transform the data to match the expected format
        // For batch updates, we need to expand each change into a separate entry
        $expandedHistory = collect();
        
        $historyData->getCollection()->each(function ($item) use ($hasDetailsColumn, $expandedHistory) {
            $details = [];
            if ($hasDetailsColumn && isset($item->details) && !empty($item->details)) {
                $details = json_decode($item->details, true) ?? [];
            }
            
            // Check if this is a batch update with multiple changes
            if (isset($details['changes']) && is_array($details['changes']) && count($details['changes']) > 0) {
                // Expand each change into a separate history entry
                foreach ($details['changes'] as $change) {
                    $expandedHistory->push((object) [
                        'id' => $item->id . '_' . ($change['id'] ?? ''),
                        'action' => $item->action,
                        'table_number' => $change['table_number'] ?? 'N/A',
                        'section' => $change['section'] ?? 'N/A',
                        'stall_id' => $change['id'] ?? null,
                        'old_daily_rate' => isset($change['old_daily_rate']) ? (float) $change['old_daily_rate'] : null,
                        'new_daily_rate' => isset($change['new_daily_rate']) ? (float) $change['new_daily_rate'] : null,
                        'old_monthly_rate' => isset($change['old_monthly_rate']) ? (float) $change['old_monthly_rate'] : null,
                        'new_monthly_rate' => isset($change['new_monthly_rate']) ? (float) $change['new_monthly_rate'] : null,
                        'effectivity_date' => $change['effectivity_date'] ?? $details['effectivity_date'] ?? null,
                        'changed_at' => (new \DateTime($item->changed_at))->format(\DateTime::ATOM),
                    ]);
                }
            } else {
                // Single update - extract from details
                $tableNumber = $details['table_number'] ?? 'N/A';
                $section = $details['section'] ?? '';
                $stallId = $details['stall_id'] ?? null;
                $oldDailyRate = isset($details['old_daily_rate']) ? (float) $details['old_daily_rate'] : null;
                $newDailyRate = isset($details['new_daily_rate']) ? (float) $details['new_daily_rate'] : null;
                $oldMonthlyRate = isset($details['old_monthly_rate']) ? (float) $details['old_monthly_rate'] : null;
                $newMonthlyRate = isset($details['new_monthly_rate']) ? (float) $details['new_monthly_rate'] : null;
                
                // If we don't have rate info but have stall_id, try to get it from the database
                if (($oldDailyRate === null || $newDailyRate === null) && isset($details['stall_id'])) {
                    $stall = DB::table('stalls')
                        ->join('sections', 'stalls.section_id', '=', 'sections.id')
                        ->where('stalls.id', $details['stall_id'])
                        ->select('stalls.table_number', 'sections.name as section')
                        ->first();
                    
                    if ($stall) {
                        $tableNumber = $stall->table_number;
                        $section = $stall->section;
                    }
                }
                
                // Only add to history if we have at least some meaningful data
                // Skip entries that have no rate information at all (old or new)
                if ($oldDailyRate !== null || $newDailyRate !== null || $oldMonthlyRate !== null || $newMonthlyRate !== null || $tableNumber !== 'N/A') {
                    $expandedHistory->push((object) [
                        'id' => $item->id,
                        'action' => $item->action,
                        'table_number' => $tableNumber,
                        'section' => $section,
                        'stall_id' => $stallId,
                        'old_daily_rate' => $oldDailyRate,
                        'new_daily_rate' => $newDailyRate,
                        'old_monthly_rate' => $oldMonthlyRate,
                        'new_monthly_rate' => $newMonthlyRate,
                        'effectivity_date' => $details['effectivity_date'] ?? null,
                        'changed_at' => (new \DateTime($item->changed_at))->format(\DateTime::ATOM),
                    ]);
                } else {
                    // Log skipped entries for debugging
                    \Log::debug('Skipped rental rate history entry (no rate data)', [
                        'audit_id' => $item->id,
                        'action' => $item->action,
                        'has_details' => !empty($details),
                        'details_keys' => array_keys($details)
                    ]);
                }
            }
        });
        
        // Replace the collection with expanded history
        // Note: We need to manually paginate the expanded collection
        $totalExpanded = $expandedHistory->count();
        $perPage = 20;
        $currentPage = request()->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedItems = $expandedHistory->slice($offset, $perPage)->values();
        
        // Create a custom paginator-like response
        $hasMore = $currentPage < ceil($totalExpanded / $perPage);
        $response = [
            'data' => $paginatedItems->toArray(), // Convert collection to array
            'current_page' => (int) $currentPage,
            'per_page' => $perPage,
            'total' => $totalExpanded,
            'last_page' => (int) ceil($totalExpanded / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalExpanded),
            'has_more' => $hasMore,
            'next_page_url' => $hasMore 
                ? url("/api/rental-rates/history?page=" . ($currentPage + 1)) 
                : null,
            'prev_page_url' => $currentPage > 1 
                ? url("/api/rental-rates/history?page=" . ($currentPage - 1)) 
                : null,
        ];
        
        // Debug: Log the final result
        \Log::info('Rental Rate History Final Result', [
            'total_audit_records' => $historyData->total(),
            'total_expanded' => $totalExpanded,
            'count_returned' => count($paginatedItems),
            'current_page' => $currentPage,
            'has_more' => $hasMore,
            'first_item_sample' => count($paginatedItems) > 0 ? [
                'action' => $paginatedItems->first()->action ?? 'N/A',
                'table_number' => $paginatedItems->first()->table_number ?? 'N/A',
                'old_daily_rate' => $paginatedItems->first()->old_daily_rate ?? null,
                'new_daily_rate' => $paginatedItems->first()->new_daily_rate ?? null,
            ] : 'No items to display'
        ]);

        return response()->json($response);
    }
}
