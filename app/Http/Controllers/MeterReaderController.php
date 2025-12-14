<?php

namespace App\Http\Controllers;

use App\Models\ReadingEditRequest;
use App\Models\Stall;
use App\Models\UtilityReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MeterReaderController extends Controller
{
    public function index(Request $request)
    {
        // --- START OF LOGIC CHANGE ---
        // Determine the active billing period based on the new rule.
        $today = Carbon::today();
        $lastDayOfCurrentMonth = $today->copy()->endOfMonth()->day;
        
        if ($today->day >= $lastDayOfCurrentMonth) {
            // If today is on or after the last day, the CURRENT month is the active reading period.
            // e.g., on Oct 31, the active period is October.
            $billingPeriodMonth = $today->copy();
        } else {
            // Otherwise, the PREVIOUS month is still the active reading period.
            // e.g., on Oct 30, the active period is September.
            $billingPeriodMonth = $today->copy()->subMonthNoOverflow();
        }
        // --- END OF LOGIC CHANGE ---

        DB::transaction(function () use ($billingPeriodMonth) {
            // Only get stalls with vendors
            $allStalls = Stall::whereHas('vendor')->pluck('id');
            $stallsWithReading = UtilityReading::whereIn('stall_id', $allStalls)
                ->whereYear('reading_date', $billingPeriodMonth->year)
                ->whereMonth('reading_date', $billingPeriodMonth->month)
                ->pluck('stall_id');
            $stallsNeedingReading = $allStalls->diff($stallsWithReading);

            if ($stallsNeedingReading->isNotEmpty()) {
                $lastReadings = UtilityReading::whereIn('stall_id', $stallsNeedingReading)
                    ->select('stall_id', DB::raw('current_reading as previous_reading'))
                    ->whereIn('id', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                              ->from('utility_readings')
                              ->groupBy('stall_id');
                    })
                    ->get()
                    ->keyBy('stall_id');

                $readingsToCreate = [];
                foreach ($stallsNeedingReading as $stallId) {
                    $previousReading = $lastReadings->get($stallId)->previous_reading ?? 0;
                    $currentReading = 0;
                    $consumption = $currentReading - $previousReading;
                    
                    $readingsToCreate[] = [
                        'stall_id' => $stallId,
                        'utility_type' => 'Electricity',
                        'reading_date' => $billingPeriodMonth->endOfMonth()->toDateString(),
                        'previous_reading' => $previousReading,
                        'current_reading' => $currentReading,
                        'consumption' => $consumption,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                UtilityReading::insert($readingsToCreate);
            }
        });

        // --- START OF TASK DISPLAY FIX ---
        $upcomingTasks = [];
        // Generate the list starting from the calculated active period.
        for ($i = 0; $i < 4; $i++) {
            $dateForTask = $billingPeriodMonth->copy()->addMonthsNoOverflow($i);
            $upcomingTasks[] = [
                'month_name' => $dateForTask->format('F'),
                'day' => $dateForTask->endOfMonth()->day,
                'is_active' => ($i === 0), // Only the first task in the list is active.
            ];
        }
        // --- END OF TASK DISPLAY FIX ---

        // Fixed N+1 and used lean selects
        // Get only stalls with assigned vendors (frontend handles pagination)
        $stalls = Stall::select('id', 'table_number', 'section_id')
            ->whereHas('vendor') // Only stalls with vendors
            ->with(['section:id,name', 'utilityReadings' => function ($query) use ($billingPeriodMonth) {
            $query->whereYear('reading_date', $billingPeriodMonth->year)
                  ->whereMonth('reading_date', $billingPeriodMonth->month)
                  ->with('editRequests');
        }])->orderBy('section_id')->get();

        $stallData = $stalls->map(function ($stall) use ($billingPeriodMonth) {
            $latestReading = $stall->utilityReadings->first();
            $status = 'pending';

            if ($latestReading) {
                $editRequest = $latestReading->editRequests->first();
                if ($editRequest) {
                    $status = 'request_' . $editRequest->status;
                } elseif ($latestReading->current_reading > 0) {
                    $status = 'submitted';
                }
            }
            $currentReading = $latestReading->current_reading ?? 0;
            $previousReading = $latestReading->previous_reading ?? 0;

            return [
                'stallId' => $stall->id,
                'utility_reading_id' => $latestReading->id ?? null,
                'section' => optional($stall->section)->name,
                'stallNumber' => $stall->table_number,
                'currentReading' => $currentReading,
                'previousReading' => $previousReading,
                'status' => $status,
                'consumption' => $currentReading - $previousReading,
                'reading_date' => $latestReading->reading_date ?? $billingPeriodMonth->endOfMonth()->toDateString(),
            ];
        });

        $editRequests = ReadingEditRequest::with(['utilityReading.stall'])
            ->where('requested_by', Auth::id())
            ->latest()
            ->get()
            ->map(function ($request) {
                // Handle cases where relationships might be missing
                if (!$request->utilityReading) {
                    \Log::warning("Edit request {$request->id} has no utilityReading relationship");
                    return null;
                }
                
                if (!$request->utilityReading->stall) {
                    \Log::warning("Edit request {$request->id} has utilityReading but no stall relationship");
                    return null;
                }
                
                return [
                    'requestId' => $request->id,
                    'stallNumber' => $request->utilityReading->stall->table_number,
                    'requestDate' => Carbon::parse($request->created_at)->toDateString(),
                    'reason' => $request->reason ?? '',
                    'status' => ucfirst($request->status ?? 'pending'),
                    'processed_at' => $request->processed_at ? Carbon::parse($request->processed_at)->toDateString() : null,
                ];
            })
            ->filter()
            ->values(); // Re-index array to ensure proper JSON encoding

        $billingMonthName = $billingPeriodMonth->format('F');

        $archiveMonths = UtilityReading::select(
            DB::raw("DATE_FORMAT(reading_date, '%M %Y') as month"),
            DB::raw("DATE_FORMAT(reading_date, '%m-%Y') as month_value"),
            DB::raw("EXTRACT(YEAR FROM reading_date) as year"),
            DB::raw("EXTRACT(MONTH FROM reading_date) as month_num")
        )
        ->where('reading_date', '<', Carbon::today()->startOfMonth())
        ->groupBy('month', 'month_value', 'year', 'month_num')
        ->orderBy('year', 'desc')
        ->orderBy('month_num', 'desc')
        ->get();

        $unreadNotificationsCount = DB::table('notifications')
        ->where('recipient_id', Auth::id())
        ->where('status', 'pending')
        ->count();

        return view('meter_portal.meter', [
            'meterReadings' => $stallData,
            'editRequests' => $editRequests,
            'scheduleDay' => DB::table('schedules')->where('schedule_type', 'Meter Reading')->value('description'),
            'upcomingTasks' => $upcomingTasks,
            'billingMonthName' => $billingMonthName,
            'archiveMonths' => $archiveMonths,
            'unreadNotificationsCount' => $unreadNotificationsCount,
        ]);
    }

    public function getStatuses(Request $request)
    {
        $validated = $request->validate([
            'reading_ids' => 'required|array',
            'reading_ids.*' => 'integer|exists:utility_readings,id',
        ]);
        $editRequests = ReadingEditRequest::whereIn('reading_id', $validated['reading_ids'])->get()->keyBy('reading_id');
        return response()->json($editRequests);
    }

    public function getSchedule()
    {
        $scheduleDay = DB::table('schedules')->where('schedule_type', 'Meter Reading')->value('description');
        return response()->json(['day' => $scheduleDay]);
    }

    public function archives(Request $request)
    {
        try {
            $query = UtilityReading::query()
                ->select('utility_readings.*')
                ->with('stall.section:id,name') // Lean eager load
                ->join('stalls', 'utility_readings.stall_id', '=', 'stalls.id')
                ->join('sections', 'stalls.section_id', '=', 'sections.id')
                ->where('utility_readings.reading_date', '<', Carbon::today()->startOfMonth())
                ->orderBy('utility_readings.reading_date', 'desc')
                ->orderBy(DB::raw("CAST(REGEXP_SUBSTR(stalls.table_number, '[0-9]+$') AS UNSIGNED)"), 'asc')
                ->orderBy('stalls.table_number', 'asc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('stalls.table_number', 'like', "%{$search}%");
            }

            $section = $request->input('section');
            if ($section) {
                $query->where('sections.name', $section);
            }

            if ($request->filled('month')) {
                $monthYear = explode('-', $request->input('month'));
                if (count($monthYear) == 2) {
                    $query->whereYear('utility_readings.reading_date', $monthYear[1])
                          ->whereMonth('utility_readings.reading_date', $monthYear[0]);
                }
            }

            $paginatedReadings = $query->paginate(75);

            return response()->json($paginatedReadings);

        } catch (\Exception $e) {
            Log::error('Error in MeterReaderController@archives API: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving archives.'], 500);
        }
    }
}