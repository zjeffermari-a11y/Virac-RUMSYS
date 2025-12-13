<script>
    // Make billing settings and utility rates globally available for the modal calculation script
    window.BILLING_SETTINGS = @json($billingSettings);
    window.UTILITY_RATES = @json($utilityRates);
</script>


<div id="vendor-outstanding-balance-view" data-bills="{{ json_encode($outstandingBills) }}">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl md:text-3xl font-semibold">Outstanding Balance</h2>
                <p class="text-lg">{{ $vendor->name }} - Stall: {{ $vendor->stall->table_number ?? 'N/A' }}</p>
            </div>
            <button data-action="back-to-details"
                class="bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-white/30 transition-smooth flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Back to Vendor Information
            </button>
        </div>
    </div>

    {{-- Please be advised Banner --}}
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded-lg shadow-sm"
        role="alert">
        <p class="font-bold">Please be advised:</p>
        <p>Official bills for the new month are generated at 7:00 AM on the 1st day of the month. In case of
            inconsistencies or errors, please report them to Market Operations.</p>
    </div>

    {{-- Total Outstanding Balance Card --}}
    @if (!$groupedBills->isEmpty())
        <div class="bg-white border-2 p-6 rounded-2xl mb-6 shadow-lg" style="border-color: #E6E8EB;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xl font-bold text-gray-800 mb-1">Total Outstanding Balance</p>
                </div>
                <div class="text-right">
                    <p class="text-4xl font-bold text-red-600">₱{{ number_format($totalOutstandingBalance, 2) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Bills Content --}}
    @if ($groupedBills->isEmpty())
        <div class="card-table p-8 rounded-2xl shadow-soft text-center text-gray-500">
            <p>This vendor has no outstanding bills.</p>
        </div>
    @else
        @foreach ($groupedBills as $month => $bills)
            <div class="card-table p-4 md:p-8 rounded-2xl shadow-soft mb-8 monthly-table-container cursor-pointer transition-shadow hover:shadow-xl"
                data-month="{{ $month }}">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">For the month of {{ $month }}</h3>
                    <a href="{{ route('printing.print', ['user' => $vendor->id, 'month' => $month]) }}" target="_blank"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg transition-smooth flex items-center gap-2">
                        <i class="fas fa-print"></i>
                        <span>Billing Statement</span>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full responsive-table">
                        <thead>
                            <tr class="table-header">
                                <th class="px-6 py-3 text-center">CATEGORY</th>
                                <th class="px-6 py-3 text-center">PERIOD COVERED</th>
                                <th class="px-6 py-3 text-center">AMOUNT DUE</th>
                                <th class="px-6 py-3 text-center">DUE DATE</th>
                                <th class="px-6 py-3 text-center">AMOUNT AFTER DUE</th>
                                <th class="px-6 py-3 text-center">DISCONNECTION DATE</th>
                                <th class="px-6 py-3 text-center">PAYMENT STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                                <tr class="table-row">
                                    <td data-label="Category" class="px-6 py-4 text-center">{{ $bill->utility_type }}
                                    </td>
                                    <td data-label="Period Covered" class="px-6 py-4 text-center">
                                        {{ \Carbon\Carbon::parse($bill->period_start)->format('M d') }} -
                                        {{ \Carbon\Carbon::parse($bill->period_end)->format('d, Y') }}</td>
                                    <td data-label="Amount Due" class="px-6 py-4 text-center">
                                        ₱{{ number_format($bill->display_amount_due, 2) }}
                                    </td>
                                    <td data-label="Due Date" class="px-6 py-4 text-center">
                                        {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}</td>
                                    <td data-label="Amount After Due" class="px-6 py-4 text-center">
                                        ₱{{ number_format($bill->amount_after_due, 2) }}
                                    </td>
                                    <td data-label="Disconnection Date" class="px-6 py-4 text-center">
                                        {{ $bill->disconnection_date ? \Carbon\Carbon::parse($bill->disconnection_date)->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td data-label="Payment Status" class="px-6 py-4 text-center">
                                        @if ($bill->status == 'paid')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                Paid on
                                                {{ $bill->payment ? \Carbon\Carbon::parse($bill->payment->payment_date)->format('M d, Y') : 'N/A' }}
                                            </span>
                                        @else
                                            <button
                                                class="mark-paid-btn bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg text-xs"
                                                data-billing-id="{{ $bill->id }}">
                                                Record Payment
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-200 font-semibold">
                                <td colspan="2" class="px-6 py-3 text-right text-gray-800">Total for
                                    {{ $month }}:</td>
                                <td class="px-6 py-3 text-center text-gray-800">
                                    ₱{{ number_format($bills->sum('amount_after_due'), 2) }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
