<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    /**
     * Display a paginated list of audit trail records.
     */
  public function index(Request $request)
    {
        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Authentication required'
                ], 401);
            }
            
            // Check if details column exists
            $hasDetailsColumn = DB::getSchemaBuilder()->hasColumn('audit_trails', 'details');
            
            // Build select fields - use LEFT JOINs to handle orphaned records gracefully
            $selectFields = [
                'at.id',
                    'at.created_at as date_time',
                'at.user_id',
                'at.role_id',
                    'at.action',
                    'at.module',
                    'at.result',
                DB::raw('COALESCE(u.name, \'[Deleted User]\') as user_name'),
                DB::raw('COALESCE(r.name, \'[Deleted Role]\') as user_role')
            ];
            
            // Only include details if column exists
            if ($hasDetailsColumn) {
                $selectFields[] = 'at.details';
            }
            
            // Use database-agnostic query builder
            $query = DB::table('audit_trails as at')
                ->leftJoin('users as u', 'at.user_id', '=', 'u.id')
                ->leftJoin('roles as r', 'at.role_id', '=', 'r.id')
                ->select($selectFields);
            
            // Log query for debugging (only in non-production)
            if (config('app.debug')) {
                \Log::debug('Audit trail query', [
                    'driver' => DB::getDriverName(),
                    'has_details_column' => $hasDetailsColumn,
                ]);
            }

        // Search by user name or action
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('u.name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('at.action', 'LIKE', "%{$searchTerm}%")
                  ->orWhere(DB::raw('COALESCE(u.name, \'[Deleted User]\')'), 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('at.role_id', $request->input('role'));
        }

        // Filter by date range - use database-agnostic approach with Laravel's date methods
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->input('end_date'))->endOfDay();
            
            // Use whereBetween with full datetime to avoid database-specific DATE() functions
            $query->whereBetween('at.created_at', [
                $startDate->toDateTimeString(),
                $endDate->toDateTimeString()
            ]);
        }

        $logs = $query->orderBy('at.created_at', 'desc')->paginate(25);

        // MODIFIED: Transform the date to an ISO-8601 string that JavaScript understands.
        $logs->getCollection()->transform(function ($item) use ($hasDetailsColumn) {
            try {
                // Assume the DB stores in UTC, format it to a string with timezone info.
                if ($item->date_time) {
                    $item->date_time = (new \DateTime($item->date_time, new \DateTimeZone('Asia/Manila')))->format(\DateTime::ATOM);
                }
            } catch (\Exception $e) {
                \Log::warning('Error formatting audit trail date', [
                    'date_time' => $item->date_time ?? null,
                    'error' => $e->getMessage()
                ]);
                $item->date_time = now()->format(\DateTime::ATOM);
            }
            
            // Parse details JSON to extract effectivity_date for rate changes, schedules, and billing settings
            // Only if details column exists and has value
            if ($hasDetailsColumn && isset($item->details) && $item->details && in_array($item->module, ['Rental Rates', 'Utility Rates', 'Schedules', 'Billing Settings', 'Announcements', 'Notification Templates', 'Effectivity Date Management', 'Utility Readings', 'Payments', 'User Settings'])) {
                try {
                    $details = json_decode($item->details, true);
                    if (is_array($details)) {
                        // Extract effectivity_date from details
                        $effectivityDate = null;
                        if (isset($details['effectivity_date'])) {
                            $effectivityDate = $details['effectivity_date'];
                        } elseif (isset($details['changes']) && is_array($details['changes'])) {
                            // For batch updates, get effectivity_date from first change or top level
                            if (isset($details['changes'][0]['effectivity_date'])) {
                                $effectivityDate = $details['changes'][0]['effectivity_date'];
                            } elseif (isset($details['effectivity_date'])) {
                                $effectivityDate = $details['effectivity_date'];
                            }
                        }
                        
                        // Format effectivity date: always show the actual date
                        if ($effectivityDate) {
                            try {
                                $date = \Carbon\Carbon::parse($effectivityDate);
                                $item->effectivity_date = $date->format('M d, Y');
                            } catch (\Exception $e) {
                                $item->effectivity_date = $effectivityDate;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing audit trail details', [
                        'details' => $item->details ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return $item;
        });

        return response()->json($logs);
        } catch (\Exception $e) {
            \Log::error('Error fetching audit trails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Return a more user-friendly error message
            return response()->json([
                'error' => 'Failed to fetch audit trails',
                'message' => 'An error occurred while loading audit trail data. Please try again or contact support if the issue persists.',
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 25,
                'total' => 0
            ], 500);
        }
    }
}