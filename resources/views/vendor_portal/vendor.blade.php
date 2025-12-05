@extends($isStaffView ? 'layouts.staff_view' : 'layouts.app')

@section('title', 'Virac Public Market - Vendor Dashboard')

@vite('resources/js/vendor.js')

@section('profile_summary')
    @if (Auth::user()->role && Auth::user()->role->name == 'Vendor')
        <div class="bg-gradient-to-r from-[#ffa600] to-[#ff8800] rounded-xl p-2 w-full text-center shadow-lg">
            <div class="font-bold text-yellow-900">Stall Number:</div>
            <div class="font-bold text-yellow-900">{{ $vendor->stall->table_number ?? 'N/A' }}</div>
        </div>
    @endif
@endsection

@section('navigation')
    <a href="#homeSection" data-section="homeSection"
        class="nav-link active text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="#profileSection" data-section="profileSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
    </a>
    <a href="#paymentHistorySection" data-section="paymentHistorySection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-history"></i>
        <span>Payment History</span>
    </a>
@endsection

@section('content')
    <script>
        // Pass server-side data to the JavaScript file
        const isStaffView = @json($isStaffView);
        const vendorData = @json($vendor);
        const outstandingBillsData = @json($outstandingBills); // Flat list for JS
        const utilityRatesData = @json($utilityRates);
        const stallData = @json($stallData);
        const billingSettingsData = @json($billingSettings);
    </script>

    <div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>

    {{-- Success/Info Messages --}}
    @if (session('success'))
        <div class="fixed top-4 right-4 z-[100] bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-fade-in-down"
            id="successMessage">
            <i class="fas fa-check-circle text-2xl"></i>
            <div>
                <p class="font-semibold">Success!</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('successMessage').remove()"
                class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('successMessage');
                if (msg) {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                }
            }, 5000);
        </script>
    @endif

    {{-- =================================================================== --}}
    {{-- HOME SECTION --}}
    {{-- =================================================================== --}}
    <div id="homeSection" class="dashboard-section active">
        <div id="homeContent">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <div class="flex items-center gap-2">
                    <i class="fa-sharp fa-solid fa-coins"></i>
                    <h2 class="text-3xl font-semibold relative z-10">Outstanding Balance</h2>
                </div>
            </div>

            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded-lg shadow-sm"
                role="alert">
                <p class="font-bold">Please be advised:</p>
                <p>Official bills for the new month are generated at 7:00 AM on the 1st day of the month. In case of
                    inconsistencies or errors, please report them to Market Operations.</p>
            </div>

            @if ($groupedBills->isEmpty())
                <div class="card-table p-8 rounded-2xl shadow-soft mb-8 text-center text-gray-500">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-700">All Caught Up!</h3>
                    <p>There are no outstanding bills at this time.</p>
                </div>
            @else
                @foreach ($groupedBills as $month => $bills)
                    <div class="card-table p-8 rounded-2xl shadow-soft mb-8 monthly-table-container cursor-pointer transition-shadow hover:shadow-xl"
                        data-month="{{ $month }}">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-800">For the month of {{ $month }}</h3>
                            <a href="{{ route('printing.print', ['user' => $vendor->id, 'month' => $month]) }}"
                                target="_blank"
                                class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg transition-smooth flex items-center gap-2">
                                <i class="fas fa-print"></i>
                                <span>Billing Statement</span>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full overflow-hidden responsive-table">
                                <thead>
                                    <tr class="table-header">
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                            CATEGORY</th>
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                            PERIOD COVERED</th>
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                            AMOUNT DUE</th>
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">DUE
                                            DATE</th>
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                            AMOUNT AFTER DUE DATE</th>
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                            DISCONNECTION DATE</th>
                                        <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                            PAYMENT STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="outstanding-bills-body">
                                    @foreach ($bills as $bill)
                                        <tr class="table-row">
                                            <td data-label="Category"
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                                                {{ $bill->utility_type }}</td>
                                            <td data-label="Period Covered"
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($bill->period_start)->format('M d') }} -
                                                {{ \Carbon\Carbon::parse($bill->period_end)->format('M d, Y') }}</td>
                                            <td data-label="Amount Due"
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                ₱{{ number_format($bill->display_amount_due, 2) }}
                                            </td>
                                            <td data-label="Due Date"
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}</td>
                                            <td data-label="Amount After Due"
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                ₱{{ number_format($bill->amount_after_due, 2) }}</td>
                                            <td data-label="Disconnection Date"
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                {{ $bill->disconnection_date ? \Carbon\Carbon::parse($bill->disconnection_date)->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td data-label="Payment Status" class="px-6 py-4 text-center action-cell">
                                                @if ($isStaffView)
                                                    @if ($bill->status == 'paid')
                                                        <div class="flex justify-center">
                                                            <span
                                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                                <i class="fas fa-check-circle mr-2"></i>
                                                                Paid on
                                                                {{ $bill->payment ? \Carbon\Carbon::parse($bill->payment->payment_date)->format('M d, Y') : 'N/A' }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class="flex justify-center">
                                                            <button
                                                                class="mark-paid-btn bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg text-xs flex items-center justify-center transition-transform transform hover:scale-105"
                                                                data-billing-id="{{ $bill->id }}">
                                                                <i class="fas fa-check-circle mr-2"></i>
                                                                <span>Record Payment</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                @else
                                                    @if ($bill->status == 'paid')
                                                        <div class="flex justify-center">
                                                            <span
                                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                                <i class="fas fa-check-circle mr-2"></i>
                                                                Paid on
                                                                {{ $bill->payment ? \Carbon\Carbon::parse($bill->payment->payment_date)->format('M d, Y') : 'N/A' }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class="flex justify-center">
                                                            <span
                                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                                                <i class="fas fa-exclamation-circle mr-2"></i>
                                                                {{ ucfirst($bill->status) }}
                                                            </span>
                                                        </div>
                                                    @endif
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
                                        <td colspan="4" class="px-6 py-3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- PROFILE SECTION --}}
    {{-- =================================================================== --}}
    <div id="profileSection" class="dashboard-section">

        <div id="profileContent">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-4 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <h2 class="text-3xl font-semibold relative z-10">Hello, {{ $vendor->name }}</h2>
            </div>
            <div class="flex flex-col lg:flex-row gap-8 items-center">
                {{-- Profile Image --}}
                @php
                    $profilePictureUrl = null;
                    if ($vendor->profile_picture) {
                        if (str_starts_with($vendor->profile_picture, 'data:')) {
                            // Legacy base64 data
                            $profilePictureUrl = $vendor->profile_picture;
                        } else {
                            // S3 path - generate URL
                            $profilePictureUrl = Storage::disk('s3')->url($vendor->profile_picture);
                        }
                    }
                @endphp
                <div
                    class="w-72 h-72 rounded-2xl shadow-inner overflow-hidden bg-gray-200 relative flex items-center justify-center flex-shrink-0">
                    @if($profilePictureUrl)
                        <img id="profileSectionImg" src="{{ $profilePictureUrl }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        <img id="profileSectionImg" src="" alt="Profile" class="w-full h-full object-cover hidden">
                        <i id="profileSectionIcon" class="fas fa-user-circle text-9xl text-gray-400"></i>
                    @endif
                </div>
                {{-- Profile Details --}}
                <div class="flex-1 card-gradient p-8 rounded-2xl shadow-soft">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Profile Information</h3>
                    <div class="grid gap-4">
                        @php
                            $profileDetails = [
                                ['icon' => 'fa-user', 'label' => 'Name', 'value' => $vendor->name],
                                [
                                    'icon' => 'fa-store',
                                    'label' => 'Market Section',
                                    'value' => $vendor->stall->section->name ?? 'N/A',
                                ],
                                [
                                    'icon' => 'fa-tag',
                                    'label' => 'Stall Number',
                                    'value' => $vendor->stall->table_number ?? 'N/A',
                                ],
                                ['icon' => 'fa-phone', 'label' => 'Contact Number', 'value' => $vendor->contact_number],
                                [
                                    'icon' => 'fa-calendar',
                                    'label' => 'Application Date',
                                    'value' => \Carbon\Carbon::parse($vendor->application_date)->format('F j, Y'),
                                ],
                            ];
                        @endphp
                        @foreach ($profileDetails as $detail)
                            <div class="flex flex-col sm:flex-row items-start p-3 rounded-xl transition-smooth">
                                <span class="w-48 text-gray-600 flex items-center gap-3 mb-1 sm:mb-0">
                                    <i class="fas {{ $detail['icon'] }} text-market-primary"></i>{{ $detail['label'] }}
                                </span>
                                <span class="text-market-primary font-medium">{{ $detail['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- PAYMENT HISTORY SECTION --}}
    {{-- =================================================================== --}}
    <div id="paymentHistorySection" class="dashboard-section">

        <div id="paymentHistoryContent">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <h2 class="text-3xl font-semibold relative z-10">Payment History</h2>
            </div>
            <div class="card-table p-8 rounded-2xl shadow-soft">
                {{-- Filters --}}
                <div class="filter-container">
                    <div class="filter-group">
                        <label for="yearDropdown"><i class="fas fa-calendar-alt"></i> Year:</label>
                        <select id="yearDropdown" class="filter-select"></select>
                    </div>
                    <div class="filter-group">
                        <label for="monthDropdown"><i class="fas fa-calendar"></i> Month:</label>
                        <select id="monthDropdown" class="filter-select">
                            <option value="all">All Months</option>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}">
                                    {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Search payments...">
                    </div>
                </div>

                {{-- Container for single-month table view --}}
                <div id="singleTableContainer" class="overflow-x-auto mt-6">
                    <table class="min-w-full overflow-hidden responsive-table">
                        <thead>
                            <tr class="table-header">
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">CATEGORY
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">PERIOD
                                    COVERED
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">BILL AMOUNT
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">DUE DATE
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">AMOUNT AFTER
                                    DUE
                                    DATE</th>
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">
                                    DISCONNECTION DATE</th>
                                <th class="px-6 py-4 text-center text-sm font-medium uppercase tracking-wider">PAYMENT
                                    STATUS
                                </th>
                            </tr>
                        </thead>
                        <tbody id="paymentTableBody"></tbody>
                    </table>
                    <div class="mt-6 text-center">
                        <button id="paymentHistoryBtn"
                            class="bg-gradient-to-r from-market-primary to-market-secondary text-white px-6 py-2 rounded-button hover:bg-none-[#b04143] hover:to-[#4f46e5] shadow-lg hover:shadow-xl hidden">
                            View Full Details
                        </button>
                    </div>
                </div>

                {{-- Container for "All Months" accordion view --}}
                <div id="paymentAccordionContainer" class="mt-6 space-y-4 hidden"></div>

                {{-- Message for when no results are found --}}
                <div id="noResultsMessage" class="no-results hidden">
                    <i class="fas fa-search"></i>
                    <h3 class="text-xl font-medium text-gray-700 mt-4">No Payments Found</h3>
                    <p class="text-gray-500 mt-2">Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MODALS --}}
    {{-- =================================================================== --}}
    <div id="outstandingDetailsModal"
        class="modal-container hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto relative responsive-modal">
            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 cursor-pointer close-modal-btn">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="p-8 w-full">
                <h3 class="text-3xl font-bold text-gray-800 mb-6 flex items-center justify-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-market-primary"></i>
                    Bill Breakdown
                </h3>
                <div class="rounded-xl border border-[#30232d] overflow-hidden mb-6 w-full">
                    <table class="w-full bill-table responsive-table">
                        <thead>
                            <tr>
                                <th class="text-2xl">Category</th>
                                <th class="text-2xl">Original Payment</th>
                                <th class="text-2xl">Discount</th>
                                <th class="text-2xl">Surcharge/Penalty</th>
                                <th class="text-2xl">Total Amount to be Paid</th>
                            </tr>
                        </thead>
                        <tbody id="outstandingBreakdownDetails">
                            {{-- Populated by JS --}}
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-bold text-lg">
                                <td class="px-4 py-4 text-right">TOTALS:</td>
                                <td class="px-4 py-4 text-center" id="totalOriginalPayment">₱0.00</td>
                                <td class="px-4 py-4 text-center text-green-600" id="totalDiscount">₱0.00</td>
                                <td class="px-4 py-4 text-center text-red-600" id="totalSurcharge">₱0.00</td>
                                <td class="px-4 py-4 text-center text-market-primary" id="totalAmountDue">₱0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- Payment History Bill Breakdown Modal --}}
    <div id="billBreakdownModal"
        class="modal-container hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div
            class="bg-white border border-black shadow-md rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto relative">
            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 close-modal-btn">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="p-8 w-full">
                <h3 class="text-3xl font-bold text-gray-800 mb-6 flex items-center justify-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-market-primary"></i>
                    Bill Breakdown
                </h3>
                <div class="rounded-xl border border-[#30232d] overflow-hidden mb-6 w-full h-auto">
                    <table class="w-full bill-table responsive-table">
                        <thead>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-100 text-2xl">Category</th>
                                <th class="text-left px-4 py-2 bg-gray-100 text-2xl">Details</th>
                                <th class="text-left px-4 py-2 bg-gray-100 text-2xl">Discount</th>
                                <th class="text-left px-4 py-2 bg-gray-100 text-2xl">Surcharge/Penalty</th>
                                <th class="text-left px-4 py-2 bg-gray-100 text-2xl">Total Amount to be Paid</th>
                            </tr>
                        </thead>
                        <tbody id="billBreakdownDetails">
                            {{-- Populated by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>
@endsection
