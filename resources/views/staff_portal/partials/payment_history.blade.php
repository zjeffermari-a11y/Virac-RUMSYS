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
                    <th class="px-4 py-2 text-left">Category</th>
                    <th class="px-4 py-2 text-left">Period Covered</th>
                    <th class="px-4 py-2 text-right">Bill Amount</th>
                    <th class="px-4 py-2 text-center">Due Date</th>
                    <th class="px-4 py-2 text-center">Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paymentHistory as $bill)
                    <tr class="table-row">
                        <td class="px-4 py-2">{{ $bill->utility_type }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($bill->period_start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($bill->period_end)->format('M d, Y') }}</td>
                        <td class="px-4 py-2 text-right">₱{{ number_format($bill->payment->amount_paid, 2) }}</td>
                        <td class="px-4 py-2 text-center">{{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="whitespace-nowrap px-4 py-1.5 text-xs font-semibold text-white bg-gray-800 rounded-full">
                                Paid on {{ \Carbon\Carbon::parse($bill->payment->payment_date)->format('M d, Y') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="5" class="text-center py-8 text-gray-500">No payment history found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
