<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Billing;
use App\Models\User;
use Carbon\Carbon;

class VendorPaymentHistory extends Component
{
    public User $vendor;
    public $year;
    public $month = 'all';
    public $searchTerm = '';

    public function mount(User $vendor)
    {
        $this->vendor = $vendor;
        // Default to the latest year with payments, or the current year
        $this->year = Billing::whereHas('stall', function ($query) {
                $query->where('vendor_id', $this->vendor->id);
            })
            ->where('status', 'paid')
            ->latest('period_end')
            ->first()
            ?->period_end->year ?? Carbon::now()->year;
    }

    public function getAvailableYearsProperty()
    {
        // Get all unique years from the paid billings for this vendor
        return Billing::whereHas('stall', function ($query) {
                $query->where('vendor_id', $this->vendor->id);
            })
            ->where('status', 'paid')
            ->selectRaw('YEAR(period_end) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    private function formatBreakdown($billings)
    {
        $breakdown = [];
        $grandTotal = 0;

        foreach ($billings as $bill) {
            if (strtolower($bill->utility_type) === 'rent') {
                $breakdown[] = [
                    'category' => 'Rent',
                    'details' => 'Monthly Stall Rental Fee',
                    'discount' => '-',
                    'penalty' => '-',
                    'total' => number_format($bill->amount, 2),
                ];
                $grandTotal += $bill->amount;
            } else {
                // Handle utilities with detailed breakdown
                $consumption = $bill->consumption ?? ($bill->current_reading - $bill->previous_reading);
                $subtotal = $consumption * $bill->rate;

                $breakdown[] = [
                    'category' => $bill->utility_type,
                    'details' => "Consumption: {$consumption} @ ₱" . number_format($bill->rate, 2),
                    'discount' => '-',
                    'penalty' => '-',
                    'total' => number_format($subtotal, 2),
                ];
                $grandTotal += $subtotal;
            }
        }

        // Add a total row
        $breakdown[] = [
            'category' => '<strong>Grand Total</strong>',
            'details' => '',
            'discount' => '',
            'penalty' => '',
            'total' => '<strong>' . number_format($grandTotal, 2) . '</strong>',
        ];

        return $breakdown;
    }

    public function showBreakdownForMonth($month)
    {
        $billings = $this->getBaseQuery()
            ->whereYear('period_end', $this->year)
            ->whereMonth('period_end', $month)
            ->get();

        $breakdown = $this->formatBreakdown($billings);
        $this->dispatch('show-bill-breakdown', breakdown: $breakdown);
    }

    public function showBreakdownForCurrentView()
    {
        $query = $this->getBaseQuery();

        if ($this->month !== 'all') {
            $query->whereYear('period_end', $this->year)
                  ->whereMonth('period_end', $this->month);
        } else {
            // This case might not be needed if the button is hidden for "all months" view,
            // but is here for completeness. It will show for the entire year.
            $query->whereYear('period_end', $this->year);
        }

        $billings = $query->get();
        $breakdown = $this->formatBreakdown($billings);
        $this->dispatch('show-bill-breakdown', breakdown: $breakdown);
    }


    private function getBaseQuery()
    {
        return Billing::whereHas('stall', function ($query) {
                $query->where('vendor_id', $this->vendor->id);
            })
            ->where('status', 'paid')
            ->with('payment')
            ->orderBy('period_end', 'desc');
    }

    public function render()
    {
        $query = $this->getBaseQuery();

        $query->whereYear('period_end', $this->year);

        if ($this->month !== 'all') {
            $query->whereMonth('period_end', $this->month);
        }

        if (!empty($this->searchTerm)) {
            $query->where('utility_type', 'like', '%' . $this->searchTerm . '%');
        }

        $payments = $query->get();

        // Group by month for the accordion view if 'All Months' is selected
        $paymentsByMonth = ($this->month === 'all')
            ? $payments->groupBy(fn($date) => Carbon::parse($date->period_end)->month)
            : collect();

        return view('livewire.vendor-payment-history', [
            'payments' => $payments,
            'paymentsByMonth' => $paymentsByMonth,
            'availableYears' => $this->getAvailableYearsProperty(),
        ]);
    }
}
