@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Virac Public Market - Superadmin Dashboard')

@vite('resources/js/superadmin.js')

@section('profile_summary')
    <div class="bg-gradient-to-r from-[#ffa600] to-[#ff8800] rounded-xl p-2 w-full text-center shadow-lg">
        <div class="font-bold text-yellow-900">Admin</div>
        <div class="font-bold text-yellow-900">Supervisor</div>
    </div>
@endsection

@section('navigation')
    <a href="#dashboardSection" data-section="dashboardSection"
        class="nav-link active text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>

    <div class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth"
        id="billingManagementDropdown">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Billing Management</span>
            </div>
            <i class="fas fa-chevron-down transform transition-transform duration-200" id="billingManagementArrow"></i>
        </div>
    </div>
    <div class="pl-4" id="billingManagementSubmenu">
        <a href="#marketStallRentalRatesSection" data-section="marketStallRentalRatesSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-start">
            <i class="fas fa-store mr-3"></i>Market Stall/Table Rental Rates
        </a>
        <a href="#electricityWaterRatesSection" data-section="electricityWaterRatesSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-start">
            <i class="fas fa-lightbulb mr-3"></i>Electricity and Water Rates
        </a>
        <a href="#electricityMeterReadingScheduleSection" data-section="electricityMeterReadingScheduleSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-start">
            <i class="fas fa-calendar-alt mr-3"></i>Electricity Meter Reading Schedule
        </a>
        <a href="#dueDateDisconnectionDateScheduleSection" data-section="dueDateDisconnectionDateScheduleSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-start">
            <i class="fas fa-calendar-day mr-3"></i>Due Date and Disconnection Date Schedule
        </a>
        <a href="#discountsSurchargesPenaltySection" data-section="discountsSurchargesPenaltySection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-start">
            <i class="fas fa-percent mr-3"></i>Discounts, Surcharges, and Penalty
        </a>
        <a href="#billingStatementSmsNotificationSettingsSection"
            data-section="billingStatementSmsNotificationSettingsSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-start">
            <i class="fas fa-sms mr-3"></i>Billing Statement SMS Notification Settings
        </a>
    </div>

    <a href="#notificationSection" data-section="notificationSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-bell"></i>
        <span>Edit Requests</span>
    </a>
    <a href="#announcementSection" data-section="announcementSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-bullhorn"></i>
        <span>Announcements</span>
    </a>
    <a href="#systemUserManagementSection" data-section="systemUserManagementSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-users-cog"></i>
        <span>System User Management</span>
    </a>
    <a href="#auditTrailsSection" data-section="auditTrailsSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-clipboard-list"></i>
        <span>Audit Trails</span>
    </a>
    <a href="#profileSection" data-section="profileSection"
        class="nav-link text-black font-medium rounded-xl p-3 mb-3 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
    </a>
@endsection

@section('content')
    <script>
        window.INITIAL_STATE = @json($initialState ?? null);
        window.loggedInUserId = {{ auth()->id() }};
    </script>

    {{-- =================================================================== --}}
    {{-- DASHBOARD SECTION --}}
    {{-- =================================================================== --}}
    <div id="dashboardSection" class="dashboard-section overflow-visible">
        {{-- Header --}}
        @include('layouts.partials.content-header', ['title' => 'Dashboard'])

        {{-- Sub-header with Title and Year Filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Market Insights</h2>
                <p class="text-gray-500">Overview of market operations for the year <span id="selectedYearDisplay"
                        class="font-semibold">2025</span>.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <select id="dashboardYearFilter"
                    class="bg-white border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-market-primary">
                    {{-- Options will be populated by JS --}}
                </select>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="kpiContainer">
            {{-- KPI Cards will be populated by JS --}}
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-soft">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Vendor Distribution by Section</h3>
                <div class="h-80 flex items-center justify-center">
                    <canvas id="vendorDistributionChart"></canvas>
                </div>
            </div>
            <div class="lg:col-span-3 bg-white p-6 rounded-2xl shadow-soft">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Monthly Collection Trends</h3>
                    <select id="collectionTypeFilter"
                        class="text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-1 focus:outline-none">
                        <option value="Rent">Rent</option>
                        <option value="Electricity">Electricity</option>
                        <option value="Water">Water</option>
                    </select>
                </div>
                <div class="h-80">
                    <canvas id="collectionTrendsChart"></canvas>
                </div>
            </div>
            <div class="lg:col-span-5 bg-white p-6 rounded-2xl shadow-soft">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Monthly Electricity Consumption</h3>
                <div class="h-80">
                    <canvas id="utilityConsumptionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Vendors & Vendors Needing Support --}}
        <div class="mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-soft">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-trophy text-yellow-500 mr-3"></i> Top Vendors (On-Time Payments)
                        </h4>
                        <select id="topVendorsSectionFilter"
                            class="text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-1 focus:outline-none">
                            <option value="All">All Sections</option>
                        </select>
                    </div>
                    <div id="topPerformersContainer" class="space-y-3">
                        {{-- Content will be populated by JS --}}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-soft">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-hands-helping text-blue-500 mr-3"></i> Vendors Needing Support
                        </h4>
                        <select id="needsSupportSectionFilter"
                            class="text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-1 focus:outline-none">
                            <option value="All">All Sections</option>
                        </select>
                    </div>
                    <div id="vendorsNeedingSupportContainer" class="space-y-3">
                        {{-- Content will be populated by JS --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- BILLING MANAGEMENT: Market Stall Rental Rates --}}
    {{-- =================================================================== --}}
    <div id="marketStallRentalRatesSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', [
            'title' => 'Billing Management',
            'subtitle' => 'Market Stall/Table Rental Rates',
            'icon' => 'fa-file-invoice-dollar',
        ])

        <div class="bg-white rounded-2xl shadow-lg mb-6">
            <div class="sticky top-0 z-10 bg-white p-4 sm:p-6 border-b border-gray-200">
                {{-- Updated this container to be responsive --}}
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <h3 id="rentalRatesHeader" class="text-2xl font-semibold text-gray-800 text-center sm:text-left">Wet
                        Section Rental Rates</h3>
                    <div id="rentalRatesDefaultButtons" class="flex items-center justify-center sm:justify-end gap-4">
                        <button id="addRentalRateBtn"
                            class="bg-gradient-to-r from-market-primary to-market-secondary text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-200 flex items-center gap-2 transition-transform transform hover:scale-105 active:scale-95">
                            <i class="fas fa-plus"></i>
                            <span>Add New Row</span>
                        </button>

                        <button id="editAllRatesBtn"
                            class="bg-blue-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-600 transition-all duration-200 flex items-center gap-2 transition-transform transform hover:scale-105 active:scale-95">
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </button>
                    </div>
                    <div id="rentalRatesEditButtons" class="hidden flex items-center justify-center sm:justify-end gap-4">
                        <button id="saveAllRentalRatesBtn"
                            class="bg-green-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-green-600 transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Save Changes</span>
                        </button>
                        <button id="cancelEditRatesBtn"
                            class="bg-gray-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-gray-600 transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </button>
                    </div>
                </div>

                {{-- Updated this container to be responsive --}}
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div class="w-full md:w-72">
                        <div class="relative">
                            <div id="searchIcon"
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <div id="loadingSpinner"
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none hidden">
                                <i class="fas fa-spinner fa-spin text-blue-500"></i>
                            </div>
                            <input type="search" id="rentalRatesSearchInput" placeholder="Search by Stall or Table..."
                                class="block w-full pl-10 pr-10 py-2 border-gray-200 border rounded-lg leading-5 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-200">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" id="clearSearchBtn"
                                    class="hidden h-full px-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-auto">
                        <div class="flex justify-center space-x-1 bg-gray-100 p-1 rounded-xl">
                            <button
                                class="section-nav-btn active px-4 py-2 sm:px-6 sm:py-3 rounded-lg font-medium transition-all duration-200 text-sm sm:text-base"
                                data-section="Wet Section">
                                Wet Section
                            </button>
                            <button
                                class="section-nav-btn px-4 py-2 sm:px-6 sm:py-3 rounded-lg font-medium transition-all duration-200 text-sm sm:text-base"
                                data-section="Dry Section">
                                Dry Section
                            </button>
                            <button
                                class="section-nav-btn px-4 py-2 sm:px-6 sm:py-3 rounded-lg font-medium transition-all duration-200 text-sm sm:text-base"
                                data-section="Semi-Wet">Semi-Wet</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto p-6">
                {{-- Added 'responsive-table' class for mobile view --}}
                <table class="w-full border-collapse responsive-table">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Table Number
                            </th>

                            <th id="areaColumnHeader"
                                class="hidden border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Area (sq. m)
                            </th>

                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Rate <span id="rateUnit" class="font-normal text-gray-600">(per day)</span>
                            </th>

                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Monthly Rental
                            </th>
                            <th class="hidden border border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody id="rentalRatesTableBody">
                    </tbody>
                </table>
                <div id="rentalRatesPagination" class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    {{-- Pagination will be rendered by JavaScript --}}
                </div>
            </div>
        </div>

        <div id="rentalRateModal"
            class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
                <div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 id="modalTitle" class="text-xl font-semibold">Add New Row</h3>
                        <button id="closeModal" class="text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="rentalRateForm" class="p-6">
                    <input type="hidden" id="editId" name="edit_id">
                    <div class="mb-4">
                        <label for="stallNumber" class="block text-gray-700 font-medium mb-2">Stall Number
                            *</label>
                        <input type="text" id="stallNumber" name="stall_number" required placeholder="e.g., A-01"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label for="tableNumber" class="block text-gray-700 font-medium mb-2">Table Number
                            *</label>
                        <input type="text" id="tableNumber" name="table_number" required placeholder="e.g., T-05"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label for="dailyRate" class="block text-gray-700 font-medium mb-2">Daily Rate (₱)
                            *</label>
                        <input type="number" id="dailyRate" name="daily_rate" required min="0" step="0.01"
                            placeholder="0.00"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                    </div>
                    <div class="mb-6">
                        <label for="monthlyRate" class="block text-gray-700 font-medium mb-2">Monthly Rate (₱)
                            *</label>
                        <input type="number" id="monthlyRate" name="monthly_rate" required min="0"
                            step="0.01" placeholder="0.00"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" id="saveBtn"
                            class="flex-1 bg-gradient-to-r from-market-primary to-market-secondary text-white py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-200">
                            Save
                        </button>
                        <button type="button" id="cancelBtn"
                            class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-medium hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- History Log Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">History Logs</h3>
            <div id="rentalRateHistoryContainer" class="history-log-container">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Date & Time</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Action</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Table Number</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Section</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Old Daily Rate</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">New Daily Rate</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Old Monthly Rate</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">New Monthly Rate</th>
                            </tr>
                        </thead>
                        <tbody id="rentalRateHistoryTableBody">
                            {{-- History rows will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="rentalRateHistoryLoader" class="history-log-loader">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- BILLING MANAGEMENT: Electricity and Water Rates --}}
    {{-- =================================================================== --}}
    <div id="electricityWaterRatesSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', [
            'title' => 'Billing Management',
            'subtitle' => 'Electricity and Water Rates',
            'icon' => 'fa-file-invoice-dollar',
        ])

        {{-- ... rest of market stall section ... --}}
        {{-- Table for Electricity and Water Rates --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            {{-- MODIFIED: Added button container --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Utility Rates</h3>
                <div id="utilityRatesDefaultButtons">
                    <button id="editUtilityRatesBtn"
                        class="bg-blue-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-600 transition-all duration-200 flex items-center gap-2 transition-transform transform hover:scale-105 active:scale-95">
                        <i class="fas fa-edit"></i>
                        <span>Edit Rates</span>
                    </button>
                </div>
                <div id="utilityRatesEditButtons" class="hidden flex items-center gap-2">
                    <button id="saveUtilityRatesBtn"
                        class="bg-green-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save</span>
                    </button>
                    <button id="cancelUtilityRatesBtn"
                        class="bg-gray-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-gray-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse responsive-table table-fixed">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">

                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-1/2">
                                Utility
                            </th>

                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-1/3">
                                Rate
                            </th>

                        </tr>
                    </thead>
                    <tbody id="utilityRatesTableBody">
                        {{-- Rows will be populated by JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">History Logs</h3>
            <div id="utilityRateHistoryContainer" class="history-log-container">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Date
                                    &
                                    Time
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Utility
                                    Type
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Old
                                    Rate
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">New
                                    Rate
                                </th>
                            </tr>
                        </thead>
                        <tbody id="utilityRateHistoryTableBody">
                            {{-- History rows will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="utilityRateHistoryLoader" class="history-log-loader">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- BILLING MANAGEMENT: Electricity Meter Reading Schedule --}}
    {{-- =================================================================== --}}
    <div id="electricityMeterReadingScheduleSection" class="dashboard-section overflow-visible">
        {{-- Header --}}
        @include('layouts.partials.content-header', [
            'title' => 'Billing Management',
            'subtitle' => 'Electricity Meter Reading Schedule',
            'icon' => 'fa-file-invoice-dollar',
        ])

        {{-- Schedule Setting Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Set Schedule</h3>
            {{-- Viewing State --}}
            <div id="scheduleView" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-gray-700 text-lg">
                    Meter Reading is scheduled on every
                    <strong id="scheduleDayDisplay"
                        class="text-xl text-market-primary underline decoration-dotted">25th</strong>
                    day of every month.
                </p>
                <button id="editScheduleBtn"
                    class="bg-blue-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-600 transition-all duration-200 flex items-center gap-2 transition-transform transform hover:scale-105 active:scale-95">
                    <i class="fas fa-edit"></i>
                    <span>Edit Schedule</span>
                </button>
            </div>
            {{-- Editing State --}}
            <div id="scheduleEdit" class="hidden">
                <p class="text-gray-700 text-lg mb-4">
                    Set the day of the month for the recurring meter reading schedule.
                </p>
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <label for="scheduleDayInput" class="font-medium text-gray-600">Schedule Day:</label>
                    <input type="number" id="scheduleDayInput" min="1" max="31"
                        class="w-full sm:w-32 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent"
                        placeholder="e.g., 25">
                    <div class="flex items-center gap-2">
                        <button id="saveScheduleBtn"
                            class="bg-green-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-600 transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Save</span>
                        </button>
                        <button id="cancelScheduleBtn"
                            class="bg-gray-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-gray-600 transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </button>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Note: This schedule will be reflected on the Meter Reader Clerk's
                    page and will be used for SMS notifications.</p>
            </div>
        </div>
        {{-- History Log Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">History Logs</h3>
            <div id="electricityMeterReadingScheduleContainer" class="history-log-container">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Date
                                    &
                                    Time
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Old Schedule Day
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    New Schedule Day
                                </th>
                            </tr>
                        </thead>
                        <tbody id="scheduleHistoryTableBody">
                            {{-- History rows will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="electricityMeterReadingScheduleLoader" class="history-log-loader">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- BILLING MANAGEMENT: Due Date and Disconnection Date Schedule --}}
    {{-- =================================================================== --}}
    <div id="dueDateDisconnectionDateScheduleSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', [
            'title' => 'Billing Management',
            'subtitle' => 'Due Date and Disconnection Date Schedule',
            'icon' => 'fa-file-invoice-dollar',
        ])

        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Billing Schedules</h3>
                <div id="billingDatesDefaultButtons">
                    <button id="editBillingDatesBtn"
                        class="bg-blue-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-600 transition-all duration-200 flex items-center gap-2 transition-transform transform hover:scale-105 active:scale-95">
                        <i class="fas fa-edit"></i>
                        <span>Edit Schedules</span>
                    </button>
                </div>
                <div id="billingDatesEditButtons" class="hidden flex items-center gap-2">
                    <button id="saveBillingDatesBtn"
                        class="bg-green-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save</span>
                    </button>
                    <button id="cancelBillingDatesBtn"
                        class="bg-gray-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-gray-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse responsive-table">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            {{-- MODIFIED: Updated table headers --}}
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-1/3">
                                Utility Category
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-1/3">
                                Due Date
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-1/3">
                                Disconnection Date
                            </th>
                        </tr>
                    </thead>
                    <tbody id="billingDatesTableBody">
                        {{-- Rows will be populated by JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- History Log Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">History Logs</h3>
            <div id="dueDateDisconnectionDateScheduleContainer" class="history-log-container">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                {{-- MODIFIED: Updated table headers --}}
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Date & Time
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Item Changed
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Old Schedule
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    New Schedule
                                </th>
                            </tr>
                        </thead>
                        <tbody id="billingDatesHistoryTableBody">
                            {{-- History rows will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="dueDateDisconnectionDateScheduleLoader" class="history-log-loader">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- BILLING MANAGEMENT: Billing Statement SMS Notification Settings --}}
    {{-- =================================================================== --}}
    <div id="billingStatementSmsNotificationSettingsSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', [
            'title' => 'Billing Management',
            'subtitle' => 'Billing Statement SMS Notification Settings',
            'icon' => 'fa-file-invoice-dollar',
        ])

        <div class="bg-white rounded-2xl shadow-lg p-6">
            {{-- Credit Tracker --}}
            <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 p-2 rounded-lg text-blue-600">
                        <i class="fas fa-coins text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900">Semaphore API Credits</h4>
                        <p class="text-sm text-blue-700">Remaining credits for SMS notifications</p>
                    </div>
                </div>
                <div class="text-right">
                    <span id="semaphoreCreditBalance" class="text-2xl font-bold text-blue-800">Loading...</span>
                    <span class="text-sm text-blue-600 font-medium block">Credits</span>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="mb-6 border-b border-gray-200">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button data-tab="billStatement"
                        class="notification-tab active text-market-primary border-b-2 border-market-primary px-3 py-2 text-lg font-medium">
                        Bill Statement
                    </button>
                    <button data-tab="paymentReminder"
                        class="notification-tab text-gray-500 hover:text-gray-700 px-3 py-2 text-lg font-medium">
                        Payment Reminder
                    </button>
                    <button data-tab="overdueAlert"
                        class="notification-tab text-gray-500 hover:text-gray-700 px-3 py-2 text-lg font-medium">
                        Overdue Alert
                    </button>
                </nav>
            </div>

            {{-- Tab Content Area --}}
            <div id="templateEditorContainer">
                {{-- Bill Statement Tab Content --}}
                <div data-content="billStatement" class="notification-tab-content">
                    <p class="text-gray-600 mb-4">This message is sent monthly and lists all utilities,
                        regardless
                        of
                        their payment status.</p>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Editor for Wet Section --}}
                        <div>
                            <label for="templateBillStatementWet" class="block text-gray-800 font-semibold mb-2">For
                                Wet Section Vendors:</label>
                            <textarea id="templateBillStatementWet" rows="5" data-template-key="bill_statement.wet_section"
                                class="template-editor w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-market-primary"></textarea>
                            <div class="text-sm text-gray-500 mt-2 text-right"
                                data-counter-for="templateBillStatementWet">0/160 characters (1 SMS)</div>
                        </div>
                        {{-- Preview for Wet Section --}}
                        <div class="flex flex-col items-center">
                            <label class="block text-gray-800 font-semibold mb-2">Live Preview</label>
                            <div class="w-full max-w-xs h-64 bg-gray-800 rounded-2xl p-2 shadow-lg">
                                <div data-preview-for="templateBillStatementWet"
                                    class="bg-white rounded-lg h-full p-3 text-gray-800 break-words overflow-y-auto overflow-x-hidden whitespace-pre-wrap">
                                    Your message preview will appear here.
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Editor for Dry Section --}}
                        <div>
                            <label for="templateBillStatementDry" class="block text-gray-800 font-semibold mb-2">For
                                Dry & Semi-Wet Section Vendors:</label>
                            <textarea id="templateBillStatementDry" rows="5" data-template-key="bill_statement.dry_section"
                                class="template-editor w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-market-primary"></textarea>
                            <div class="text-sm text-gray-500 mt-2 text-right"
                                data-counter-for="templateBillStatementDry">0/160 characters (1 SMS)</div>
                        </div>
                        {{-- Preview for Dry Section --}}
                        <div class="flex flex-col items-center">
                            <label class="block text-gray-800 font-semibold mb-2">Live Preview</label>
                            <div class="w-full max-w-xs h-64 bg-gray-800 rounded-2xl p-2 shadow-lg">
                                <div data-preview-for="templateBillStatementDry"
                                    class="bg-white rounded-lg h-full p-3 text-gray-800 break-words overflow-y-auto overflow-x-hidden whitespace-pre-wrap">
                                    Your message preview will appear here.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Other Tab Contents (follow the same grid pattern) --}}
                <div data-content="paymentReminder" class="notification-tab-content hidden">
                    <p class="text-gray-600 mb-4">This reminder is sent on the due date and only lists unpaid
                        items.
                    </p>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <label for="templatePaymentReminder"
                                class="block text-gray-800 font-semibold mb-2">Template:</label>
                            <textarea id="templatePaymentReminder" rows="5" data-template-key="payment_reminder.template"
                                class="template-editor w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-market-primary"></textarea>
                            <div class="text-sm text-gray-500 mt-2 text-right" data-counter-for="templatePaymentReminder">
                                0/160 characters (1 SMS)</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <label class="block text-gray-800 font-semibold mb-2">Live Preview</label>
                            <div class="w-full max-w-xs h-64 bg-gray-800 rounded-2xl p-2 shadow-lg">
                                <div data-preview-for="templatePaymentReminder"
                                    class="bg-white rounded-lg h-full p-3 text-gray-800 break-words overflow-y-auto overflow-x-hidden whitespace-pre-wrap"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-content="overdueAlert" class="notification-tab-content hidden">
                    <p class="text-gray-600 mb-4">This alert is sent the day after the due date if bills remain
                        unpaid.
                    </p>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <label for="templateOverdueAlert"
                                class="block text-gray-800 font-semibold mb-2">Template:</label>
                            <textarea id="templateOverdueAlert" rows="5" data-template-key="overdue_alert.template"
                                class="template-editor w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-market-primary"></textarea>
                            <div class="text-sm text-gray-500 mt-2 text-right" data-counter-for="templateOverdueAlert">
                                0/160 characters (1 SMS)</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <label class="block text-gray-800 font-semibold mb-2">Live Preview</label>
                            <div class="w-full max-w-xs h-64 bg-gray-800 rounded-2xl p-2 shadow-lg">
                                <div data-preview-for="templateOverdueAlert"
                                    class="bg-white rounded-lg h-full p-3 text-gray-800 break-words overflow-y-auto overflow-x-hidden whitespace-pre-wrap"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Placeholder Guide and Save Button --}}
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-800">Available Placeholders</h4>
                    <input type="text" id="placeholderSearch" placeholder="Search placeholders..." 
                        class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-market-primary focus:border-market-primary">
                </div>
                
                <div id="placeholderButtons" class="space-y-4 mb-6">
                    {{-- Basic Information --}}
                    <div class="placeholder-category">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fas fa-user text-market-primary"></i>
                            Basic Information
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <button class="placeholder-btn bg-blue-50 text-blue-700 px-3 py-1.5 rounded-md hover:bg-blue-100 border border-blue-200 transition-colors text-sm font-mono" 
                                data-category="basic" title="Vendor's full name">@{{ vendor_name }}</button>
                            <button class="placeholder-btn bg-blue-50 text-blue-700 px-3 py-1.5 rounded-md hover:bg-blue-100 border border-blue-200 transition-colors text-sm font-mono" 
                                data-category="basic" title="Stall/Table number">@{{ stall_number }}</button>
                            <button class="placeholder-btn bg-blue-50 text-blue-700 px-3 py-1.5 rounded-md hover:bg-blue-100 border border-blue-200 transition-colors text-sm font-mono" 
                                data-category="basic" title="Current timestamp">@{{ timestamp }}</button>
                        </div>
                    </div>

                    {{-- Bill Month & Dates --}}
                    <div class="placeholder-category">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fas fa-calendar text-market-primary"></i>
                            Dates & Period
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <button class="placeholder-btn bg-green-50 text-green-700 px-3 py-1.5 rounded-md hover:bg-green-100 border border-green-200 transition-colors text-sm font-mono" 
                                data-category="dates" title="Billing month (e.g., September 2025)">@{{ bill_month }}</button>
                            <button class="placeholder-btn bg-green-50 text-green-700 px-3 py-1.5 rounded-md hover:bg-green-100 border border-green-200 transition-colors text-sm font-mono" 
                                data-category="dates" title="Earliest due date">@{{ due_date }}</button>
                            <button class="placeholder-btn bg-green-50 text-green-700 px-3 py-1.5 rounded-md hover:bg-green-100 border border-green-200 transition-colors text-sm font-mono" 
                                data-category="dates" title="Electricity disconnection date">@{{ disconnection_date }}</button>
                        </div>
                    </div>

                    {{-- Detailed Bill Information --}}
                    <div class="placeholder-category">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fas fa-file-invoice-dollar text-market-primary"></i>
                            Detailed Bill Information
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <button class="placeholder-btn bg-purple-50 text-purple-700 px-3 py-1.5 rounded-md hover:bg-purple-100 border border-purple-200 transition-colors text-sm font-mono" 
                                data-category="details" title="Rent details with original, discounted amount, and due date">@{{ rent_details }}</button>
                            <button class="placeholder-btn bg-purple-50 text-purple-700 px-3 py-1.5 rounded-md hover:bg-purple-100 border border-purple-200 transition-colors text-sm font-mono" 
                                data-category="details" title="Water bill amount and due date">@{{ water_details }}</button>
                            <button class="placeholder-btn bg-purple-50 text-purple-700 px-3 py-1.5 rounded-md hover:bg-purple-100 border border-purple-200 transition-colors text-sm font-mono" 
                                data-category="details" title="Electricity calculation, amount, due date, and disconnection date">@{{ electricity_details }}</button>
                        </div>
                    </div>

                    {{-- Amounts & Totals --}}
                    <div class="placeholder-category">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fas fa-money-bill-wave text-market-primary"></i>
                            Amounts & Totals
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <button class="placeholder-btn bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-md hover:bg-yellow-100 border border-yellow-200 transition-colors text-sm font-mono" 
                                data-category="amounts" title="Total amount due">@{{ total_due }}</button>
                            <button class="placeholder-btn bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-md hover:bg-yellow-100 border border-yellow-200 transition-colors text-sm font-mono" 
                                data-category="amounts" title="New total due (with penalties)">@{{ new_total_due }}</button>
                            <button class="placeholder-btn bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-md hover:bg-yellow-100 border border-yellow-200 transition-colors text-sm font-mono" 
                                data-category="amounts" title="Rent amount only">@{{ rent_amount }}</button>
                            <button class="placeholder-btn bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-md hover:bg-yellow-100 border border-yellow-200 transition-colors text-sm font-mono" 
                                data-category="amounts" title="Water amount only">@{{ water_amount }}</button>
                            <button class="placeholder-btn bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-md hover:bg-yellow-100 border border-yellow-200 transition-colors text-sm font-mono" 
                                data-category="amounts" title="Electricity amount only">@{{ electricity_amount }}</button>
                        </div>
                    </div>

                    {{-- Bill Summaries --}}
                    <div class="placeholder-category">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fas fa-list text-market-primary"></i>
                            Bill Summaries
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <button class="placeholder-btn bg-orange-50 text-orange-700 px-3 py-1.5 rounded-md hover:bg-orange-100 border border-orange-200 transition-colors text-sm font-mono" 
                                data-category="summaries" title="All unpaid bills with amounts and due dates">@{{ bill_details }}</button>
                            <button class="placeholder-btn bg-orange-50 text-orange-700 px-3 py-1.5 rounded-md hover:bg-orange-100 border border-orange-200 transition-colors text-sm font-mono" 
                                data-category="summaries" title="Upcoming bills (not yet due)">@{{ upcoming_bill_details }}</button>
                            <button class="placeholder-btn bg-orange-50 text-orange-700 px-3 py-1.5 rounded-md hover:bg-orange-100 border border-orange-200 transition-colors text-sm font-mono" 
                                data-category="summaries" title="Overdue bills">@{{ overdue_bill_details }}</button>
                            <button class="placeholder-btn bg-orange-50 text-orange-700 px-3 py-1.5 rounded-md hover:bg-orange-100 border border-orange-200 transition-colors text-sm font-mono" 
                                data-category="summaries" title="List of unpaid utility types">@{{ unpaid_items }}</button>
                            <button class="placeholder-btn bg-orange-50 text-orange-700 px-3 py-1.5 rounded-md hover:bg-orange-100 border border-orange-200 transition-colors text-sm font-mono" 
                                data-category="summaries" title="List of overdue utility types">@{{ overdue_items }}</button>
                        </div>
                    </div>

                    {{-- Links & Other --}}
                    <div class="placeholder-category">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fas fa-link text-market-primary"></i>
                            Links & Other
                        </h5>
                        <div class="flex flex-wrap gap-2">
                            <button class="placeholder-btn bg-gray-50 text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 border border-gray-200 transition-colors text-sm font-mono" 
                                data-category="other" title="Vendor portal website URL">@{{ website_url }}</button>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <button id="saveTemplatesBtn"
                        class="bg-green-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-600 transition-all duration-200 flex items-center gap-2 ml-auto">
                        <i class="fas fa-save"></i>
                        <span>Save All Templates</span>
                    </button>
                </div>

            </div>
        </div>

        <div class="mt-12">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">SMS Sending Schedule</h3>
                    <p class="text-gray-500">Set the days and time for automated SMS notifications (Philippine Time).</p>
                </div>
                <div id="smsSchedulesDefaultButtons">
                    <button id="editSmsSchedulesBtn"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-5 py-2 rounded-lg transition-smooth">
                        <i class="fas fa-edit text-sm"></i><span class="ml-2">Edit Schedule</span>
                    </button>
                </div>
                <div id="smsSchedulesEditButtons" class="hidden">
                    <button id="saveSmsSchedulesBtn"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold px-5 py-2 rounded-lg transition-smooth">
                        <i class="fas fa-save text-sm"></i><span class="ml-2">Save Changes</span>
                    </button>
                    <button id="cancelSmsSchedulesBtn"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-5 py-2 rounded-lg transition-smooth ml-2">
                        <i class="fas fa-times text-sm"></i><span class="ml-2">Cancel</span>
                    </button>
                </div>
            </div>

            <div class="card-table shadow-soft overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full responsive-table">
                        <thead>
                            <tr class="table-header">
                                <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Notification
                                    Type</th>
                                <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Scheduled Days
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Scheduled Time
                                </th>
                            </tr>
                        </thead>
                        <tbody id="smsScheduleTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">
                <h4 class="text-lg font-bold text-gray-700 mb-2">Change History</h4>
                <div id="smsScheduleHistoryContainer"
                    class="card-table shadow-soft overflow-hidden max-h-96 overflow-y-auto">
                    <div class="overflow-x-auto">
                        <table class="min-w-full responsive-table">
                            <thead>
                                <tr class="table-header">
                                    <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Date &
                                        Time</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Item
                                        Changed</th>
                                    <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Old Value
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">New Value
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="smsScheduleHistoryTableBody">
                            </tbody>
                        </table>
                        <div id="smsScheduleHistoryLoader" class="text-center p-4 hidden">
                            <i class="fas fa-spinner fa-spin"></i> Loading more...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="discountsSurchargesPenaltySection" class="dashboard-section overflow-visible">
        {{-- Header --}}
        @include('layouts.partials.content-header', [
            'title' => 'Billing Management',
            'subtitle' => 'Discounts, Surcharges, and Penalty',
            'icon' => 'fa-file-invoice-dollar',
        ])

        {{-- Settings Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Discounts, Surcharges, and Penalties</h3>
                <div id="billingSettingsDefaultButtons">
                    <button id="editBillingSettingsBtn"
                        class="bg-blue-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Settings</span>
                    </button>
                </div>
                <div id="billingSettingsEditButtons" class="hidden flex items-center gap-2">
                    <button id="saveBillingSettingsBtn"
                        class="bg-green-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save</span>
                    </button>
                    <button id="cancelBillingSettingsBtn"
                        class="bg-gray-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-gray-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>

            {{-- Rent Table --}}
            <div class="mb-8">
                <h4 class="text-xl font-semibold text-gray-700 mb-4">Rent Settings</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Category
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Discount
                                    (%)</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Surcharge (%)</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Monthly
                                    Interest (%)</th>
                            </tr>
                        </thead>
                        <tbody id="rentSettingsTableBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Utilities Table --}}
            <div>
                <h4 class="text-xl font-semibold text-gray-700 mb-4">Electricity & Water Settings</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Category
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Discount
                                    (%)</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Penalty
                                    (%)</th>
                            </tr>
                        </thead>
                        <tbody id="utilitySettingsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- History Log Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">History Logs</h3>
            <div id="billingSettingsHistoryContainer" class="history-log-container">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Date &
                                    Time</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Category
                                </th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Item
                                    Changed</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Old
                                    Value</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">New
                                    Value</th>
                            </tr>
                        </thead>
                        <tbody id="billingSettingsHistoryTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div id="billingSettingsHistoryLoader" class="history-log-loader">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- NOTIFICATION SECTION --}}
    {{-- =================================================================== --}}
    <div id="notificationSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', ['title' => 'Edit Requests'])

        {{-- Electricity Reading Edit Requests Table --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-6">Electricity Reading Edit Requests</h3>
            <div id="readingEditRequestsContainer" class="history-log-container">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse responsive-table">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Request
                                    Date</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Request
                                    Reason</th>
                                <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody id="readingEditRequestsTableBody">
                            {{-- Request rows will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="readingEditRequestsLoader" class="history-log-loader">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>

        {{-- SMS Settings Table --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">SMS Settings</h3>
                <div id="smsSettingsDefaultButtons">
                    <button id="editSmsSettingsBtn"
                        class="bg-blue-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Numbers</span>
                    </button>
                </div>
                <div id="smsSettingsEditButtons" class="hidden flex items-center gap-2">
                    <button id="saveSmsSettingsBtn"
                        class="bg-green-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save</span>
                    </button>
                    <button id="cancelSmsSettingsBtn"
                        class="bg-gray-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-gray-600 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse responsive-table">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                User
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Number
                            </th>
                        </tr>
                    </thead>
                    <tbody id="smsSettingsTableBody">
                        {{-- Settings rows will be populated by JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- =================================================================== --}}
    {{-- ANNOUNCEMENT SECTION --}}
    {{-- =================================================================== --}}
    <div id="announcementSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', ['title' => 'Announcements'])

        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Left Column: Create Announcement Form --}}
                <div>
                    <div class="bg-white rounded-2xl shadow-lg p-6 h-full">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Create Announcement</h3>
                        <form id="createAnnouncementForm">
                            <div class="mb-4">
                                <label for="announcementTitle" class="block text-gray-700 font-medium mb-2">Title *</label>
                                <input type="text" id="announcementTitle" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent"
                                    placeholder="Enter title...">
                            </div>
                            <div class="mb-4">
                                <label for="announcementContent" class="block text-gray-700 font-medium mb-2">Content *</label>
                                <textarea id="announcementContent" required rows="8"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-market-primary focus:border-transparent"
                                    placeholder="Enter announcement details..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">Recipients *</label>
                                <div class="space-y-2">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="recipientStaff" checked
                                            class="recipient-checkbox form-checkbox h-5 w-5 text-market-primary rounded focus:ring-market-primary">
                                        <span class="text-gray-700">Staff</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="recipientAllSections" checked
                                            class="recipient-checkbox form-checkbox h-5 w-5 text-market-primary rounded focus:ring-market-primary">
                                        <span class="text-gray-700">All Sections</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="recipientWetSection"
                                            class="recipient-checkbox form-checkbox h-5 w-5 text-market-primary rounded focus:ring-market-primary">
                                        <span class="text-gray-700">Wet Section</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="recipientDrySection"
                                            class="recipient-checkbox form-checkbox h-5 w-5 text-market-primary rounded focus:ring-market-primary">
                                        <span class="text-gray-700">Dry Section</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="recipientSemiWetSection"
                                            class="recipient-checkbox form-checkbox h-5 w-5 text-market-primary rounded focus:ring-market-primary">
                                        <span class="text-gray-700">Semi-Wet Section</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" id="announcementIsActive" checked
                                        class="form-checkbox h-5 w-5 text-market-primary rounded focus:ring-market-primary">
                                    <span class="text-gray-700">Publish Immediately</span>
                                </label>
                            </div>
                            <button type="submit" id="saveAnnouncementBtn"
                                class="w-full bg-gradient-to-r from-market-primary to-market-secondary text-white py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                <span>Post Announcement</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right Column: Draft Announcements --}}
                <div>
                    <div class="bg-white rounded-2xl shadow-lg p-6 h-full">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Draft Announcements</h3>
                        <div id="draftAnnouncementsList" class="space-y-4 max-h-[calc(100vh-400px)] overflow-y-auto pr-2">
                            {{-- Draft announcements will be populated by JS --}}
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Loading announcements...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sent Announcements List - Full Width at Bottom --}}
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Sent Announcements</h3>
                <div id="sentAnnouncementsList" class="space-y-4 max-h-[calc(100vh-500px)] overflow-y-auto pr-2">
                    {{-- Sent announcements will be populated by JS --}}
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading announcements...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- SYSTEM USER MANAGEMENT SECTION --}}
    {{-- =================================================================== --}}
    <div id="systemUserManagementSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', ['title' => 'System User Management'])

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-2xl mb-5 font-semibold text-gray-800">System User Management</h3>
            {{-- Filters and Actions --}}
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-col md:flex-row items-center gap-4 w-full">
                    {{-- Search Input --}}
                    <div class="relative w-full md:w-72">
                        <input type="search" id="userSearchInput" placeholder="Search by name or username..."
                            class="block w-full pl-10 pr-4 py-2 border-gray-200 border rounded-lg bg-gray-50 focus:bg-white focus:border-blue-400">
                        <i class="fas fa-search text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                    </div>
                    {{-- Role Filter --}}
                    <select id="userRoleFilter" class="w-48 border-gray-200 border rounded-lg bg-gray-50 p-2">
                        <option value="">All Roles</option>
                        {{-- Roles will be populated by JS or Blade --}}
                    </select>
                </div>
                {{-- Add User Button --}}
                <button id="addUserBtn"
                    class="bg-market-primary text-white px-6 py-2 rounded-xl font-medium hover:bg-market-secondary transition-colors w-full md:w-auto flex-shrink-0">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>

            {{-- Users Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-collapse responsive-table">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Role
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Name
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Username
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Last
                                Login</th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Status
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        {{-- User rows will be populated by JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Pagination Controls --}}
            <div id="usersPagination" class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                {{-- Pagination will be rendered by JavaScript --}}
            </div>
        </div>

        {{-- Add/Edit User Modal --}}
        <div id="userModal" class="fixed inset-0 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <form id="userForm">
                    <div class="p-6 border-b">
                        <h3 id="userModalTitle" class="text-xl font-semibold">Add New User</h3>
                    </div>
                    <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                        <input type="hidden" id="userId">
                        {{-- Form Fields --}}
                        <div>
                            <label for="userName" class="block text-gray-700 font-medium mb-1">Name *</label>
                            <input type="text" id="userName" required
                                class="w-full border border-gray-300 rounded-lg p-2">
                        </div>
                        <div>
                            <label for="userUsername" class="block text-gray-700 font-medium mb-1">Username
                                *</label>
                            <input type="text" id="userUsername" required
                                class="w-full border border-gray-300 rounded-lg p-2">
                        </div>
                        <div>
                            <label for="userContactNumber" class="block text-gray-700 font-medium mb-1">Contact
                                Number</label>
                            <input type="text" id="userContactNumber"
                                class="w-full border no-spinner border-gray-300 rounded-lg p-2" placeholder="09xxxxxxxxx"
                                maxlength="11">
                            <p id="contactNumberError" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        <div>
                            <label for="userApplicationDate" class="block text-gray-700 font-medium mb-1">Application
                                Date</label>
                            <input type="date" id="userApplicationDate"
                                class="w-full border border-gray-300 rounded-lg p-2">
                        </div>
                        <div>
                            <label for="userRole" class="block text-gray-700 font-medium mb-1">Role *</label>
                            <select id="userRole" required class="w-full border border-gray-300 rounded-lg p-2 bg-white">
                                {{-- Role options will be populated by JS or Blade --}}
                            </select>
                        </div>
                        <div>
                            <label for="userStatus" class="block text-gray-700 font-medium mb-1">Status
                                *</label>
                            <select id="userStatus" required
                                class="w-full border border-gray-300 rounded-lg p-2 bg-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label for="userPassword" class="block text-gray-700 font-medium mb-1">Password</label>
                            <input type="password" id="userPassword"
                                class="w-full border border-gray-300 rounded-lg p-2">
                        </div>
                        <div>
                            <label for="userPasswordConfirmation" class="block text-gray-700 font-medium mb-1">Confirm
                                Password</label>
                            <input type="password" id="userPasswordConfirmation"
                                class="w-full border border-gray-300 rounded-lg p-2">
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                        <button type="button" id="cancelUserModalBtn"
                            class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-medium hover:bg-gray-300 transition-all duration-200 ease-in-out transform hover:-translate-y-0.5 hover:shadow-lg">
                            Cancel
                        </button>
                        <button type="submit" id="saveUserBtn"
                            class="bg-green-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-green-600 transition-all duration-200 ease-in-out transform hover:-translate-y-0.5 hover:shadow-lg">
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- AUDIT TRAILS SECTION --}}
    {{-- =================================================================== --}}
    <div id="auditTrailsSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', ['title' => 'Audit Trails'])

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-col md:flex-row items-center gap-4 w-full">
                    {{-- Search Input --}}
                    <div class="relative w-full md:w-72">
                        <input type="search" id="auditTrailSearchInput" placeholder="Search by user or action..."
                            class="block w-full pl-10 pr-4 py-2 border-gray-200 border rounded-lg bg-gray-50 focus:bg-white focus:border-blue-400">
                        <i class="fas fa-search text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                    </div>
                    {{-- Role Filter --}}
                    <select id="auditTrailRoleFilter" class="w-48 border-gray-200 border rounded-lg bg-gray-50 p-2">
                        <option value="">All Roles</option>
                        {{-- Roles will be populated by JavaScript --}}
                    </select>
                    {{-- Date Range Filter --}}
                    <div id="auditTrailDateFilter" class="flex justify-center space-x-1 bg-gray-100 p-1 rounded-xl">
                        <button data-range="today"
                            class="date-range-btn px-4 py-2 rounded-lg font-medium text-sm sm:text-base transition-all duration-200">Today</button>
                        <button data-range="last7days"
                            class="date-range-btn px-4 py-2 rounded-lg font-medium text-sm sm:text-base transition-all duration-200">Last
                            7 Days</button>
                        <button data-range="this_month"
                            class="date-range-btn px-4 py-2 rounded-lg font-medium text-sm sm:text-base transition-all duration-200">This
                            Month</button>
                        <button data-range="all"
                            class="date-range-btn active px-4 py-2 rounded-lg font-medium text-sm sm:text-base transition-all duration-200">All</button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse responsive-table">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Date
                                &
                                Time</th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                User
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Role
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Action
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Module
                            </th>
                            <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                                Result
                            </th>
                        </tr>
                    </thead>
                    <tbody id="auditTrailsTableBody">
                        {{-- Log rows will be populated by JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Loading Indicator --}}
            <div id="auditTrailsLoader" class="text-center p-4 hidden">
                <i class="fas fa-spinner fa-spin text-market-primary text-2xl"></i>
                <p class="text-gray-500 mt-2">Loading more activities...</p>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
            <div class="bg-red-500 text-white p-6 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                    {{-- The h3 and p tags will be populated by JavaScript --}}
                    <h3 class="text-xl font-semibold">Confirm Action</h3>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-6">Are you sure? This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button id="confirmDelete"
                        class="flex-1 bg-red-500 text-white py-3 rounded-lg font-medium hover:bg-red-600 transition-colors">
                        Delete
                    </button>
                    <button id="cancelDelete"
                        class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-medium hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- PROFILE SECTION --}}
    {{-- =================================================================== --}}
    <div id="profileSection" class="dashboard-section overflow-visible">
        @include('layouts.partials.content-header', ['title' => 'User Profile'])
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Profile Picture & Information --}}
            <div class="bg-white rounded-2xl shadow-lg p-6">
                {{-- Profile Picture Section --}}
                <div class="mb-6 text-center">
                    <div class="relative inline-block">
                        <div id="profilePictureContainer" class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 mx-auto mb-4 border-4 border-market-primary shadow-lg">
                            @if(auth()->user()->profile_picture)
                                <img id="profilePictureImg" src="{{ Storage::url(auth()->user()->profile_picture) }}" 
                                     alt="Profile Picture" class="w-full h-full object-cover">
                            @else
                                <div id="profilePicturePlaceholder" class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-6xl text-gray-400"></i>
                                </div>
                            @endif
                        </div>
                        <label for="profilePictureInput" class="absolute bottom-0 right-0 bg-market-primary text-white rounded-full p-2 cursor-pointer hover:bg-market-secondary transition-colors shadow-lg">
                            <i class="fas fa-camera"></i>
                            <input type="file" id="profilePictureInput" accept="image/*" class="hidden">
                        </label>
                    </div>
                    <button id="removeProfilePictureBtn" class="text-sm text-red-600 hover:text-red-800 {{ auth()->user()->profile_picture ? '' : 'hidden' }}">
                        <i class="fas fa-trash"></i> Remove Picture
                    </button>
                </div>

                <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-user text-market-primary"></i>
                    Profile Information
                </h3>
                <div class="space-y-4">
                    @php
                        $user = auth()->user();
                        $profileDetails = [
                            ['icon' => 'fa-user', 'label' => 'Name', 'value' => $user->name],
                            ['icon' => 'fa-at', 'label' => 'Username', 'value' => $user->username],
                            ['icon' => 'fa-user-tag', 'label' => 'Role', 'value' => $user->role->name ?? 'N/A'],
                            ['icon' => 'fa-phone', 'label' => 'Contact Number', 'value' => $user->contact_number ?? 'Not set'],
                            ['icon' => 'fa-calendar', 'label' => 'Last Login', 'value' => $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('F j, Y g:i A') : 'Never'],
                            ['icon' => 'fa-info-circle', 'label' => 'Status', 'value' => ucfirst($user->status ?? 'active')],
                        ];
                    @endphp
                    @foreach ($profileDetails as $detail)
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas {{ $detail['icon'] }} text-market-primary w-8 text-center"></i>
                            <div class="flex-1 ml-4">
                                <span class="text-sm text-gray-600">{{ $detail['label'] }}</span>
                                <p class="font-semibold text-gray-800">{{ $detail['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Change Password --}}
            <div class="bg-white rounded-2xl shadow-lg p-6">
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

    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
@endsection
