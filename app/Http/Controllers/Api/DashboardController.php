<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\Section;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function getAllDashboardData(Request $request)
    {
        // --- Fetch Data for Filters ---
        $years = Billing::select(DB::raw('EXTRACT(YEAR FROM period_start) as year'))
            ->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years->push(Carbon::now()->year);
        }
        $sections = Section::all();

        // --- Prepare Data Structures ---
        $allData = [
            'kpis' => null,
            'vendorDistribution' => null,
            'collectionTrends' => [],
            'utilityConsumption' => [],
            'vendorPulse' => [
                'topPerformers' => [],
                'needsSupport' => [],
            ],
        ];

        // --- Populate Data ---
        $allData['kpis'] = $this->getKpis()->getData(true);
        $allData['vendorDistribution'] = $this->getVendorDistribution()->getData(true);

        foreach ($years as $year) {
            $yearRequest = new Request(['year' => $year]);

            // Collection & Utility Trends (by year)
            $allData['collectionTrends'][$year] = $this->getCollectionTrends($yearRequest)->getData(true);
            $allData['utilityConsumption'][$year] = $this->getUtilityConsumption($yearRequest)->getData(true);
            
            // Vendor Pulse Data (by year and section)
            $allData['vendorPulse']['topPerformers'][$year]['All'] = $this->getTopPerformingVendors($yearRequest)->getData(true);
            $allData['vendorPulse']['needsSupport'][$year]['All'] = $this->getVendorsNeedingSupport($yearRequest)->getData(true);

            foreach ($sections as $section) {
                $sectionRequest = new Request(['year' => $year, 'section' => $section->name]);
                $allData['vendorPulse']['topPerformers'][$year][$section->name] = $this->getTopPerformingVendors($sectionRequest)->getData(true);
                $allData['vendorPulse']['needsSupport'][$year][$section->name] = $this->getVendorsNeedingSupport($sectionRequest)->getData(true);
            }
        }
        
        return response()->json($allData);
    }
    /**
     * Fetch key performance indicators for the dashboard.
     */
    public function getKpis()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();

        $totalVendors = User::whereHas('role', fn ($q) => $q->where('name', 'Vendor'))->count();

        $totalCollected = Payment::whereMonth('payment_date', $today->month)
            ->whereYear('payment_date', $today->year)
            ->sum('amount_paid');

        $totalOverdue = Billing::where('status', 'unpaid')
            ->where('due_date', '<', $today)
            ->sum('amount');

        $newSignups = User::whereHas('role', fn ($q) => $q->where('name', 'Vendor'))
            ->where('application_date', '>=', $startOfMonth)
            ->count();

        return response()->json([
            'totalVendors' => $totalVendors,
            'totalCollected' => (float) $totalCollected,
            'totalOverdue' => (float) $totalOverdue,
            'newSignups' => $newSignups,
        ]);
    }

    /**
     * Fetch vendor distribution by market section.
     */
    public function getVendorDistribution()
    {
        $distribution = Section::withCount(['stalls as vendor_count' => function ($query) {
                $query->whereHas('vendor');
            }])
            ->get(['name', 'vendor_count']);

        return response()->json($distribution);
    }

    /**
     * Fetch rent and utility collection trends for a given year.
     */
public function getCollectionTrends(Request $request)
{
    $year = $request->input('year', Carbon::now()->year);

    $collections = Billing::select(
            DB::raw('EXTRACT(MONTH FROM period_start) as month'),
            'utility_type',
            DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid"),
            DB::raw("SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) as unpaid")
        )
        ->whereYear('period_start', $year)
        ->groupBy('month', 'utility_type')
        ->orderBy('month')
        ->get();

        $trends = [];
        for ($i = 1; $i <= 12; $i++) {
            $trends[$i] = [
                'month' => $i,
                'paid' => [],
                'unpaid' => [],
            ];
        }

        foreach ($collections as $collection) {
            if (isset($trends[$collection->month])) {
                if ($collection->paid > 0) {
                    $trends[$collection->month]['paid'][$collection->utility_type] = (float) $collection->paid;
                }
                if ($collection->unpaid > 0) {
                    $trends[$collection->month]['unpaid'][$collection->utility_type] = (float) $collection->unpaid;
                }
            }
        }

        return response()->json(array_values($trends));
    }

    /**
     * Fetch utility consumption data for a given year.
     */
    public function getUtilityConsumption(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $consumption = Billing::select(
                DB::raw('EXTRACT(MONTH FROM period_start) as month'),
                'utility_type',
                DB::raw('SUM(consumption) as total_consumption')
            )
            ->where('utility_type', 'Electricity')
            ->whereYear('period_start', $year)
            ->groupBy('month', 'utility_type')
            ->orderBy('month')
            ->get();

        return response()->json($consumption);
    }

    /**
     * Get available years for filters from billing data.
     */
    public function getFilterYears()
    {
        $years = Billing::select(DB::raw('EXTRACT(YEAR FROM period_start) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        if ($years->isEmpty()) {
            $years->push(Carbon::now()->year);
        }

        return response()->json($years);
    }

    public function getTopPerformingVendors(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        // Start building the query
        $query = DB::table('users')
            ->join('stalls', 'users.id', '=', 'stalls.vendor_id')
            ->join('sections', 'stalls.section_id', '=', 'sections.id') // Join sections table
            ->join('billing', 'stalls.id', '=', 'billing.stall_id')
            ->leftJoin('payments', 'billing.id', '=', 'payments.billing_id')
            ->where('billing.status', 'paid')
            ->whereYear('billing.period_start', $year);

        // ✅ **FIX APPLIED HERE**
        // This block checks if a section filter was provided in the request.
        // If it exists and isn't "All", it adds a 'where' clause to the query
        // to filter the results by the selected section name.
        if ($request->filled('section') && $request->section !== 'All') {
            $query->where('sections.name', $request->section);
        }

        $vendorStats = $query->select(
                'users.id',
                'users.name',
                'stalls.table_number',
                'sections.name as section_name', // Select the section name
                DB::raw('COUNT(billing.id) as paid_bills_count'),
                DB::raw('SUM(CASE WHEN payments.payment_date <= billing.due_date THEN 1 ELSE 0 END) as on_time_bills_count')
            )
            ->groupBy('users.id', 'users.name', 'stalls.table_number', 'sections.name')
            ->having(DB::raw('COUNT(billing.id)'), '>', 0) 
            ->orderByRaw('on_time_bills_count / paid_bills_count DESC')
            ->orderByDesc('on_time_bills_count')
            ->limit(5)
            ->get();

        $formattedVendors = $vendorStats->map(function ($vendor) {
            $onTimePercentage = round(($vendor->on_time_bills_count / $vendor->paid_bills_count) * 100);
            return [
                'name' => $vendor->name,
                'stall_number' => $vendor->table_number,
                'metric' => "{$onTimePercentage}% On-Time",
                'section_name' => $vendor->section_name, // Add to output
            ];
        });

        return response()->json($formattedVendors);
    }
    /**
     * ✅ OPTIMIZED VERSION: Fetch top 5 vendors with the most overdue bills, with section filtering.
     */
    public function getVendorsNeedingSupport(Request $request)
{
    // Add year variable
    $year = $request->input('year', Carbon::now()->year);

    $query = User::whereHas('role', fn ($q) => $q->where('name', 'Vendor'))
        ->with(['stall:id,vendor_id,table_number', 'billings' => function ($query) use ($year) {
            // Apply year filter
            $query->whereYear('period_start', $year)
                  ->where('status', 'unpaid')
                  ->where('due_date', '<', now())
                  ->select('stall_id', 'utility_type', 'period_start');
        }])
        ->withCount(['billings as overdue_bills_count' => function ($query) use ($year) {
            // Apply year filter
            $query->whereYear('period_start', $year)->where('status', 'unpaid')->where('due_date', '<', now());
        }]);

    if ($request->filled('section') && $request->section !== 'All') {
        $sectionName = $request->section;
        $query->whereHas('stall.section', fn($q) => $q->where('name', $sectionName));
    }

    $paginatedVendors = $query->having('overdue_bills_count', '>', 0)
        ->orderByDesc('overdue_bills_count')
        ->paginate(15);

    $paginatedVendors->getCollection()->transform(function ($vendor) {
        $overdueDetails = $vendor->billings->map(function ($bill) {
            return $bill->utility_type . ' - ' . \Carbon\Carbon::parse($bill->period_start)->format('F Y');
        });

        return [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'stall_number' => $vendor->stall->table_number ?? 'N/A',
            'metric' => $vendor->overdue_bills_count . ' Overdue Bill(s)',
            'overdue_bills_details' => $overdueDetails,
        ];
    });

    return response()->json($paginatedVendors);
    }
    
}