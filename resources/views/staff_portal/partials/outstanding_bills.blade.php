{{-- Page Header --}}
<div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-semibold">Outstanding Balance</h2>
            <p class="text-lg">{{ $vendor->name }} - Stall: {{ $vendor->stall->table_number ?? 'N/A' }}</p>
        </div>
        <button data-action="back-to-details"
            class="bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-white/30 transition-smooth flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Back to Vendor Details
        </button>
    </div>
</div>

{{-- Please be advised Banner --}}
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded-lg shadow-sm" role="alert">
    <p class="font-bold">Please be advised:</p>
    <p>Official bills for the new month are generated at 7:00 AM on the 1st day of the month. In case of inconsistencies or errors, please report them to Market Operations.</p>
</div>

{{-- Total Outstanding Balance Card --}}
@php
    $totalOutstandingBalance = 0;
    foreach ($outstandingBills as $bill) {
        $totalOutstandingBalance += $bill->display_amount_due ?? $bill->amount;
    }
@endphp
@if ($outstandingBills->isNotEmpty())
    <div class="bg-white border-2 p-6 rounded-2xl mb-8 shadow-lg" style="border-color: #E6E8EB;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xl font-bold text-gray-800 mb-1">Total Outstanding Balance</p>
            </div>
            <div class="text-right">
                <h3 class="text-2xl font-bold text-red-600">₱{{ number_format($totalOutstandingBalance, 2) }}</h3>
            </div>
        </div>
    </div>
@endif

<div class="flex flex-col lg:flex-row gap-8">
    {{-- Unpaid Bills Section --}}
    <div class="w-full lg:w-2/3">
        <div class="card-table p-8 rounded-2xl shadow-soft">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Unpaid Bills</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="table-header">
                            <th class="px-4 py-2 text-left">Category</th>
                            <th class="px-4 py-2 text-left">Period Covered</th>
                            <th class="px-4 py-2 text-right">Amount Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDue = 0; @endphp
                        @forelse ($outstandingBills as $bill)
                            @php
                                $amountDue = $bill->display_amount_due ?? $bill->amount;
                                $totalDue += $amountDue;
                            @endphp
                            <tr class="table-row">
                                <td class="px-4 py-2">{{ $bill->utility_type }}</td>
                                <td class="px-4 py-2">
                                    {{ \Carbon\Carbon::parse($bill->period_end)->format('F Y') }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    @if (($bill->penalty_applied ?? 0) > 0 || ($bill->discount_applied ?? 0) > 0)
                                        <div class="flex flex-col items-end">
                                            <span class="text-sm text-gray-500 line-through">₱{{ number_format($bill->original_amount, 2) }}</span>
                                            <span class="font-semibold {{ ($bill->discount_applied ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ₱{{ number_format($amountDue, 2) }}
                                            </span>
                                        </div>
                                    @else
                                        <span>₱{{ number_format($amountDue, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="3" class="text-center py-8 text-gray-500">This vendor has no
                                    unpaid
                                    bills.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($outstandingBills->isNotEmpty())
                        <tfoot>
                            <tr class="border-t-2 border-gray-300">
                                <td colspan="2" class="px-4 py-2 text-right font-bold text-lg">Total Amount
                                    Due:
                                </td>
                                <td class="px-4 py-2 text-right font-bold text-lg text-red-600">
                                    ₱{{ number_format($totalDue, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Record Payment Section --}}
    <div class="w-full lg:w-1/3">
        <div class="card-gradient p-8 rounded-2xl shadow-soft">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Record a Payment</h3>

            @if ($outstandingBills->isNotEmpty())
                {{-- The form will POST to our new route --}}
                <form action="{{ route('staff.payments.store', $vendor->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-2">Amount
                            Paid</label>
                        <input type="number" name="amount_paid" id="amount_paid" step="0.01"
                            class="w-full p-2 border border-gray-300 rounded-md"
                            value="{{ number_format($totalOutstandingBalance, 2, '.', '') }}" required>
                    </div>
                    <div class="mb-6">
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment
                            Date</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ now()->format('Y-m-d') }}"
                            class="w-full p-2 border border-gray-300 rounded-md" required>
                    </div>
                    <button type="submit" class="action-button w-full">
                        <i class="fas fa-check-circle"></i>
                        Confirm Payment
                    </button>
                </form>
            @else
                <p class="text-gray-600">There are no bills to pay.</p>
            @endif
        </div>
    </div>
</div>
