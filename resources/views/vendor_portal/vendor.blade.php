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
        <i class="fas fa-wallet"></i>
        <span>Outstanding Balance</span>
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
    <a href="#analyticsSection" data-section="analyticsSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-chart-line"></i>
        <span>Analytics</span>
    </a>
    <a href="#notificationsSection" data-section="notificationsSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-bell"></i>
        <span>Notifications</span>
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
        const paymentHistoryInitialData = @json($paymentHistoryInitial ?? ['data' => [], 'total' => 0, 'has_more' => false]);
    </script>

    <div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>

    {{-- Announcement banner removed - announcements now appear as notifications in the bell dropdown --}}

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
    {{-- OUTSTANDING BALANCE SECTION --}}
    {{-- =================================================================== --}}
    <div id="homeSection" class="dashboard-section active">
        <div id="homeContent">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center gap-2">
                    <i class="fa-sharp fa-solid fa-coins"></i>
                        <h2 class="text-3xl font-semibold">Outstanding Balance</h2>
                    </div>
                    {{-- Notification Bell --}}
                    <div class="notificationBell relative">
                        <button
                            class="relative text-white hover:text-gray-200 focus:outline-none transition-transform transform hover:scale-110">
                            <i class="fas fa-bell text-2xl"></i>
                            <span
                                class="notificationDot absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-500 border-2 border-white hidden animate-pulse"></span>
                        </button>
                        {{-- Notification Dropdown Panel --}}
                        <div
                            class="notificationDropdown hidden absolute top-full right-0 mt-2 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-[9999]">
                            <div class="p-3 font-semibold text-gray-800 border-b">
                                Notifications
                            </div>
                            <div class="notificationList max-h-96 overflow-y-auto">
                                {{-- Notification items will be inserted here by JS --}}
                            </div>
                        </div>
                    </div>
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
            <div class="bg-white border-2 p-6 rounded-2xl mb-6 shadow-lg" style="border-color: #E6E8EB;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xl font-bold text-gray-800 mb-1">Total Outstanding Balance</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-red-600">₱{{ number_format($totalOutstandingBalance, 2) }}</p>
                    </div>
                </div>
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
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-4 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <div class="flex items-center justify-between relative z-10">
                    <h2 class="text-3xl font-semibold">Hello, {{ $vendor->name }}</h2>
                    {{-- Notification Bell --}}
                    <div class="notificationBell relative">
                        <button
                            class="relative text-white hover:text-gray-200 focus:outline-none transition-transform transform hover:scale-110">
                            <i class="fas fa-bell text-2xl"></i>
                            <span
                                class="notificationDot absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-500 border-2 border-white hidden animate-pulse"></span>
                        </button>
                        {{-- Notification Dropdown Panel --}}
                        <div
                            class="notificationDropdown hidden absolute top-full right-0 mt-2 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-[9999]">
                            <div class="p-3 font-semibold text-gray-800 border-b">
                                Notifications
                            </div>
                            <div class="notificationList max-h-96 overflow-y-auto">
                                {{-- Notification items will be inserted here by JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row gap-8 items-center">
                {{-- Profile Image --}}
                <div class="relative">
                    <div
                        class="w-72 h-72 rounded-2xl shadow-inner overflow-hidden bg-gray-200 relative flex items-center justify-center flex-shrink-0">
                        @if($vendor->profile_picture)
                            <img id="profileSectionImg" src="{{ $vendor->profile_picture_url ?? Storage::url($vendor->profile_picture) }}" alt="Profile" class="w-full h-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <i id="profileSectionIcon" class="fas fa-user-circle text-9xl text-gray-400 hidden"></i>
                        @else
                            <img id="profileSectionImg" src="" alt="Profile" class="w-full h-full object-cover hidden">
                            <i id="profileSectionIcon" class="fas fa-user-circle text-9xl text-gray-400"></i>
                        @endif
                    </div>
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

            {{-- Change Password --}}
            <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-key text-market-primary"></i>
                    Change Password
                </h3>
                <form id="changePasswordForm">
                    @csrf
                    <div class="mb-4">
                        <label for="currentPassword" class="block text-gray-700 font-medium mb-2">Current Password</label>
                        <input type="password" id="currentPassword" name="current_password" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label for="newPassword" class="block text-gray-700 font-medium mb-2">New Password</label>
                        <input type="password" id="newPassword" name="password" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters with letters, numbers, symbols, and mixed case</p>
                    </div>
                    <div class="mb-6">
                        <label for="confirmPassword" class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="password_confirmation" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                    </div>
                    <button type="submit" id="changePasswordBtn"
                        class="w-full bg-gradient-to-r from-market-primary to-market-secondary text-white py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- PAYMENT HISTORY SECTION --}}
    {{-- =================================================================== --}}
    <div id="paymentHistorySection" class="dashboard-section">

        <div id="paymentHistoryContent">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <div class="flex items-center justify-between relative z-10">
                    <h2 class="text-3xl font-semibold">Payment History</h2>
                    {{-- Notification Bell --}}
                    <div class="notificationBell relative">
                        <button
                            class="relative text-white hover:text-gray-200 focus:outline-none transition-transform transform hover:scale-110">
                            <i class="fas fa-bell text-2xl"></i>
                            <span
                                class="notificationDot absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-500 border-2 border-white hidden animate-pulse"></span>
                        </button>
                        {{-- Notification Dropdown Panel --}}
                        <div
                            class="notificationDropdown hidden absolute top-full right-0 mt-2 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-[9999]">
                            <div class="p-3 font-semibold text-gray-800 border-b">
                                Notifications
                            </div>
                            <div class="notificationList max-h-96 overflow-y-auto">
                                {{-- Notification items will be inserted here by JS --}}
                            </div>
                        </div>
                    </div>
                </div>
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

    {{-- =================================================================== --}}
    {{-- ANALYTICS SECTION --}}
    {{-- =================================================================== --}}
    <div id="analyticsSection" class="dashboard-section">
        <div id="analyticsContent">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
                <div class="flex items-center justify-between relative z-10">
                    <h2 class="text-3xl font-semibold">Analytics & Insights</h2>
                    {{-- Notification Bell --}}
                    <div class="notificationBell relative">
                        <button
                            class="relative text-white hover:text-gray-200 focus:outline-none transition-transform transform hover:scale-110">
                            <i class="fas fa-bell text-2xl"></i>
                            <span
                                class="notificationDot absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-500 border-2 border-white hidden animate-pulse"></span>
                        </button>
                        {{-- Notification Dropdown Panel --}}
                        <div
                            class="notificationDropdown hidden absolute top-full right-0 mt-2 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-[9999]">
                            <div class="p-3 font-semibold text-gray-800 border-b">
                                Notifications
                            </div>
                            <div class="notificationList max-h-96 overflow-y-auto">
                                {{-- Notification items will be inserted here by JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Electricity Consumption Chart --}}
            <div class="card-table p-8 rounded-2xl shadow-soft mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                    <i class="fas fa-bolt text-yellow-500"></i>
                    Electricity Consumption Trend
                </h3>
                <div class="relative h-96">
                    <canvas id="electricityConsumptionChart"></canvas>
                </div>
                <p class="text-sm text-gray-500 mt-4 text-center">
                    <i class="fas fa-info-circle"></i>
                    Shows your monthly electricity consumption in kWh over the past 12 months
                </p>
            </div>

            {{-- Payment Tracking Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {{-- Payment Status Pie Chart --}}
                <div class="card-table p-8 rounded-2xl shadow-soft">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-chart-pie text-blue-500"></i>
                        Payment Performance
                    </h3>
                    <div class="relative h-80">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                    <div id="paymentStats" class="mt-4 text-center space-y-2">
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-green-600" id="onTimeCount">0</span> payments on time
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-red-600" id="lateCount">0</span> late payments
                        </p>
                    </div>
                </div>

                {{-- Payment Timeline Chart --}}
                <div class="card-table p-8 rounded-2xl shadow-soft">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-calendar-alt text-purple-500"></i>
                        Payment Timeline
                    </h3>
                    <div class="relative h-80">
                        <canvas id="paymentTimelineChart"></canvas>
                    </div>
                    <p class="text-sm text-gray-500 mt-4 text-center">
                        <i class="fas fa-info-circle"></i>
                        Monthly breakdown of on-time vs late payments
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications Section --}}
    <div id="notificationsSection" class="dashboard-section">
        <div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-4 md:p-6 rounded-2xl mb-4 shadow-lg relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl md:text-3xl font-semibold">Notifications</h2>
                    <p class="text-base md:text-lg mt-1">View all your notifications</p>
                </div>
            </div>
        </div>

        <div class="card-table p-4 md:p-6 rounded-2xl shadow-soft">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <button id="markAllAsReadBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg transition-smooth flex items-center gap-2">
                        <i class="fas fa-check-double"></i>
                        <span>Mark All as Read</span>
                    </button>
                    <span id="unreadCountBadge" class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold hidden">
                        <span id="unreadCountText">0</span> unread
                    </span>
                </div>
            </div>

            <div id="notificationsLoader" class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>Loading notifications...
            </div>

            <div id="notificationsList" class="space-y-3 hidden">
                {{-- Notifications will be populated by JavaScript --}}
            </div>

            <div id="noNotificationsMessage" class="text-center py-8 text-gray-500 hidden">
                <i class="fas fa-bell-slash text-4xl mb-4 text-gray-400"></i>
                <p class="text-lg">You have no notifications.</p>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>
@endsection
