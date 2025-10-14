{{-- Header --}}
<div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl md:text-3xl font-semibold">Payment History</h2>
            <p class="text-lg">{{ $vendor->name }} - Stall: {{ $vendor->stall->table_number ?? 'N/A' }}</p>
        </div>
        <button data-action="back-to-details"
            class="bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-white/30 transition-smooth flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Back to Vendor Information
        </button>
    </div>
</div>

{{-- Main Content Card --}}
<div class="card-table p-4 md:p-8 rounded-2xl shadow-soft">
    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 items-center mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center gap-2 w-full md:w-auto">
            <i class="fas fa-calendar-alt text-gray-500"></i>
            <label for="ph_year_filter" class="font-medium text-gray-700">Year:</label>
            <select id="ph_year_filter"
                class="filter-select bg-white border border-gray-300 rounded-md py-2 px-3 flex-grow">
                {{-- Year options will be populated by JS --}}
            </select>
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
            <i class="fas fa-calendar-day text-gray-500"></i>
            <label for="ph_month_filter" class="font-medium text-gray-700">Month:</label>
            <select id="ph_month_filter"
                class="filter-select bg-white border border-gray-300 rounded-md py-2 px-3 flex-grow">
                <option value="all">All Months</option>
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}">
                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="relative flex-grow w-full md:w-auto">
            <i class="fas fa-search text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
            <input type="text" id="ph_search_input"
                class="search-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md"
                placeholder="Search by category...">
        </div>
    </div>

    {{-- Container for table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full responsive-table">
            {{-- START OF FIX --}}
            <thead>
                <tr class="table-header">
                    <th class="px-4 py-2 text-center">Category</th>
                    <th class="px-4 py-2 text-center">Period Covered</th>
                    <th class="px-4 py-2 text-center">Bill Amount</th>
                    <th class="px-4 py-2 text-center">Due Date</th>
                    <th class="px-4 py-2 text-center">Amount After Due</th>
                    <th class="px-4 py-2 text-center">Disconnection Date</th>
                    <th class="px-4 py-2 text-center">Payment Status</th>
                </tr>
            </thead>
            {{-- END OF FIX --}}
            <tbody id="paymentHistoryTableBody">
                {{-- Rows will be populated by JS --}}
            </tbody>
        </table>
        <div id="ph_no_results" class="text-center py-12 text-gray-500 hidden">
            <i class="fas fa-search text-4xl mb-4"></i>
            <p>No payment history found for the selected criteria.</p>
        </div>
    </div>
</div>
