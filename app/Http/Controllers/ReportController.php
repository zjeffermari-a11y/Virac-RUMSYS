<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Models\User;
use App\Models\Payment;
use App\Models\Billing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Generate and download a monthly report as a PDF.
     */
    public function download(Request $request)
    {
        // Fetch the same data your API uses by calling our private helper method
        $data = $this->getMonthlyReportData($request);
        
        $notes = $request->input('notes', '');

        // Read Chart.js directly from node_modules
        $chartJsPath = base_path('node_modules/chart.js/dist/chart.umd.js');
        $chartJsContent = '';
        
        if (file_exists($chartJsPath)) {
            $chartJsContent = file_get_contents($chartJsPath);
        } else {
            // Fallback: try to find it in a different location
            $altPath = base_path('node_modules/chart.js/dist/chart.js');
            if (file_exists($altPath)) {
                $chartJsContent = file_get_contents($altPath);
            }
        }

        // Render a dedicated Blade view for the PDF content
        $html = view('printing.report', [
            'data' => $data, 
            'notes' => $notes,
            'chartJsContent' => $chartJsContent
        ])->render();

        try {
            // Use Browsershot with Browserless.io remote Chrome service
            // This works on Laravel Cloud where local Chrome/Puppeteer cannot run
            $browserlessToken = env('BROWSERLESS_TOKEN');
            
            $browsershot = Browsershot::html($html)
                ->format('Letter')
                ->showBrowserHeaderAndFooter(false)
                ->waitUntilNetworkIdle()
                ->delay(3000); // 3 seconds to ensure charts render

            // Use Browserless.io remote Chrome if token is configured
            if ($browserlessToken) {
                $browsershot->setRemoteInstance('https://chrome.browserless.io?token=' . $browserlessToken);
                \Illuminate\Support\Facades\Log::info('Browsershot: Using Browserless.io remote instance');
            } else {
                // Fallback to local Chrome with standard args (for local development)
                $browsershot->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu']);
                \Illuminate\Support\Facades\Log::info('Browsershot: Using local Chrome (no BROWSERLESS_TOKEN set)');
            }

            $pdf = $browsershot->pdf();

            // Create a filename based on the report period
            $filename = 'Monthly_Report_' . str_replace(' ', '_', $data['report_period']) . '.pdf';

            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Downloaded Monthly Report for ' . $data['report_period'],
                'module' => 'Reports',
                'result' => 'Success',
                'created_at' => now(),
            ]);

            // Return the generated PDF as a download
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Report Generation Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to generate report: ' . $e->getMessage()], 400);
        }
    }

    /**
     * A private helper method to fetch the report data.
     * This logic is copied from your StaffController to avoid code duplication.
     */
    private function getMonthlyReportData(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $targetDate = Carbon::parse($validated['month']);
        $year = $targetDate->year;
        $month = $targetDate->month;

        $totalCollected = Payment::whereYear('payment_date', $year)->whereMonth('payment_date', $month)->sum('amount_paid');
        $newVendors = User::whereYear('application_date', $year)->whereMonth('application_date', $month)->count();

        $collectionsBreakdown = DB::table('payments')
            ->join('billing', 'payments.billing_id', '=', 'billing.id')
            ->join('stalls', 'billing.stall_id', '=', 'stalls.id')
            ->join('sections', 'stalls.section_id', '=', 'sections.id')
            ->whereYear('payments.payment_date', $year)
            ->whereMonth('payments.payment_date', $month)
            ->select(
                'sections.name as section_name',
                'billing.utility_type',
                DB::raw('SUM(payments.amount_paid) as total')
            )
            ->groupBy('sections.name', 'billing.utility_type')
            ->get();

        $delinquentVendors = User::whereHas('role', fn($q) => $q->where('name', 'Vendor'))
            ->whereHas('billings', function ($query) use ($targetDate) {
                $query->where('status', 'unpaid')
                      ->where('due_date', '<=', $targetDate->endOfMonth());
            })
            ->with(['stall:id,vendor_id,table_number', 'billings' => function($q) use ($targetDate) {
                $q->where('status', 'unpaid')->where('due_date', '<=', $targetDate->endOfMonth())->select('stall_id', 'utility_type', 'amount');
            }])
            ->get(['id', 'name']);

        $delinquentVendors->each(function ($vendor) {
            $vendor->total_due = $vendor->billings->sum('amount');
        });

        $utilityPeriod = $targetDate->copy()->subMonthNoOverflow();

        $collectionByUtility = Billing::select(
            'utility_type',
            DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid"),
            DB::raw("SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) as unpaid")
        )
            ->where(function ($query) use ($year, $month) {
                // Rent bills belong to their own consumption month
                $query->where('utility_type', 'Rent')
                    ->whereYear('period_start', $year)
                    ->whereMonth('period_start', $month);
            })
            ->orWhere(function ($query) use ($utilityPeriod) {
                // Utility bills belong to the month AFTER their consumption period
                $query->whereIn('utility_type', ['Water', 'Electricity'])
                    ->whereYear('period_start', $utilityPeriod->year)
                    ->whereMonth('period_start', $utilityPeriod->month);
            })
            ->groupBy('utility_type')
            ->get()
            ->keyBy('utility_type');

            DB::table('audit_trails')->insert([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role_id,
                'action' => 'Generated Monthly Report for ' . $targetDate->format('F Y'),
                'module' => 'Reports',
                'result' => 'Success',
                'created_at' => now(),
            ]);

        return [
            'report_period' => $targetDate->format('F Y'),
            'kpis' => [
                'total_collection' => $totalCollected,
                'new_vendors' => $newVendors,
                'delinquent_vendors_count' => $delinquentVendors->count(),
            ],
            'collections_breakdown' => $collectionsBreakdown,
            'delinquent_vendors' => $delinquentVendors,
            'chart_data' => [
                'by_utility' => $collectionByUtility,
            ]
        ];
    }
}