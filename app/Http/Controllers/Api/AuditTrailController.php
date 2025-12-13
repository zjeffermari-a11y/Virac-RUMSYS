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
        $query = DB::table('audit_trails as at')
            ->join('users as u', 'at.user_id', '=', 'u.id')
            ->join('roles as r', 'at.role_id', '=', 'r.id')
            ->select(
                'at.created_at as date_time',
                'u.name as user_name',
                'r.name as user_role',
                'at.action',
                'at.module',
                'at.result',
                'at.details'
            );

        // Search by user name or action
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('u.name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('at.action', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('at.role_id', $request->input('role'));
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween(DB::raw('DATE(at.created_at)'), [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }

        $logs = $query->orderBy('at.created_at', 'desc')->paginate(25);

        // MODIFIED: Transform the date to an ISO-8601 string that JavaScript understands.
        $logs->getCollection()->transform(function ($item) {
            // Assume the DB stores in UTC, format it to a string with timezone info.
            $item->date_time = (new \DateTime($item->date_time, new \DateTimeZone('Asia/Manila')))->format(\DateTime::ATOM);
            
            // Parse details JSON to extract effectivity_date for rate changes, schedules, and billing settings
            if ($item->details && in_array($item->module, ['Rental Rates', 'Utility Rates', 'Schedules', 'Billing Settings'])) {
                $details = json_decode($item->details, true);
                if (is_array($details)) {
                    // Extract effectivity_date from details
                    if (isset($details['effectivity_date'])) {
                        $item->effectivity_date = $details['effectivity_date'];
                    } elseif (isset($details['changes']) && is_array($details['changes'])) {
                        // For batch updates, get effectivity_date from first change or top level
                        if (isset($details['changes'][0]['effectivity_date'])) {
                            $item->effectivity_date = $details['changes'][0]['effectivity_date'];
                        } elseif (isset($details['effectivity_date'])) {
                            $item->effectivity_date = $details['effectivity_date'];
                        }
                    }
                }
            }
            
            return $item;
        });

        return response()->json($logs);
    }
}