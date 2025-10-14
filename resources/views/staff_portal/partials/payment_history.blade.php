    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-semibold">Payment History</h2>
                <p class="text-lg">{{ $vendor->name }} - Stall: {{ $vendor->stall->table_number ?? 'N/A' }}</p>
            </div>
            <button data-action="back-to-details"
                class="bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-white/30 transition-smooth flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Back to Vendor Details
            </button>
        </div>
    </div>

    {{-- Paid Bills Table --}}
    <div class="card-table p-8 rounded-2xl shadow-soft">
        <table class="min-w-full">
            <thead>
                <tr class="table-header">
                    <th class="px-4 py-2 text-left">Period</th>
                    <th class="px-4 py-2 text-left">Utility</th>
                    <th class="px-4 py-2 text-right">Amount Paid</th>
                    <th class="px-4 py-2 text-center">Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paymentHistory as $bill)
                    <tr class="table-row">
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($bill->period_start)->format('M Y') }}</td>
                        <td class="px-4 py-2">{{ $bill->utility_type }}</td>
                        <td class="px-4 py-2 text-right">₱{{ number_format($bill->payment->amount_paid, 2) }}</td>
                        <td class="px-4 py-2 text-center">
                            {{ \Carbon\Carbon::parse($bill->payment->payment_date)->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="4" class="text-center py-8 text-gray-500">No payment history found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
