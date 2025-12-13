@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Virac Public Market - Admin Aide Dashboard')

@vite('resources/js/staff.js')

@section('profile_summary')
    <div class="bg-gradient-to-r from-[#ffa600] to-[#ff8800] rounded-xl p-2 w-full text-center shadow-lg">
        <div class="font-bold text-yellow-900">Market Staff</div>
    </div>
@endsection

@section('navigation')
    <div class="flex-grow">
        <a href="#homeSection" data-section="homeSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-tasks"></i>
            <span>Pending Tasks</span>
        </a>
        <a href="#vendorManagementSection" data-section="vendorManagementSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-users-cog "></i>
            <span>Vendor Management</span>
        </a>
        <a href="#stallAssignmentSection" data-section="stallAssignmentSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-store-alt"></i>
            <span>Stall Assignment</span>
        </a>
        <a href="#dashboardSection" data-section="dashboardSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="#reportsSection" data-section="reportsSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-chart-line"></i>
            <span>Reports</span>
        </a>
        <a href="#notificationsSection" data-section="notificationsSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
        </a>
        <a href="#profileSection" data-section="profileSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer transition-smooth flex items-center space-x-3">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
    </div>
@endsection

@section('content')
    {{-- Announcement banner removed - announcements now appear as notifications in the bell dropdown --}}

    <style>
        @media print {

            /* Hide Reports header and filter box when printing */
            #reportsSection>div:first-child,
            #reportsSection .bg-gray-50 {
                display: none !important;
            }

            /* Hide navigation sidebar */
            nav,
            .nav-link {
                display: none !important;
            }

            /* Hide any buttons or controls */
            button:not(.print-button) {
                display: none !important;
            }
        }
    </style>
    <script>
        window.STAFF_PORTAL_STATE = @json($initialState ?? null);
        window.DASHBOARD_STATE = @json($dashboardState ?? null);
        window.BILLING_SETTINGS = @json($billingSettings ?? null);
        window.UTILITY_RATES = @json($utilityRates ?? null);
    </script>

    {{-- Home Section --}}
    <div id="homeSection" class="dashboard-section">
        <div
            class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
            </div>
            <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16">
            </div>
            <div class="flex items-center justify-between relative z-10">
                <h2 id="homeHeader" class="text-2xl md:text-3xl font-semibold">Hello, Market Staff!</h2>
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

        <div class="bg-white p-4 md:p-8 rounded-2xl shadow-soft">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-ul"></i>
                    Pending Tasks
                </h3>
            </div>

            <p class="text-gray-600 mb-6">Please refer to the table below for current billing and receipt printing.</p>

            <div class="card-gradient p-4 md:p-6 rounded-2xl shadow-inner">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                    <h4 class="font-bold text-gray-800 flex items-center gap-2 text-base md:text-lg">
                        <i class="fas fa-file-invoice-dollar text-yellow-500"></i> Bill Management
                    </h4>
                    <button id="bulkPrintBtn"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg transition-smooth flex items-center gap-2 shadow mt-2 md:mt-0">
                        <i class="fas fa-print"></i>
                        <span>Print Selected</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full rounded-xl overflow-hidden text-sm responsive-table bill-management-table">
                        <thead>
                            <tr class="table-header">
                                <th scope="col" class="p-4 text-right lg:text-center">
                                    <input type="checkbox" id="selectAllCheckbox"
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded">
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                                    Stall/Table Number
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                                    Vendor Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
                                    Print Receipt
                                </th>
                            </tr>
                        </thead>
                        <tbody id="dailyCollectionsTableBody">
                            {{-- Rows will be populated dynamically from the backend. --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor Management Section Wrapper --}}
    <div id="vendorManagementSection" class="dashboard-section">

        {{-- List View (This should be here) --}}
        <div id="vendorListView">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16">
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <h2 id="vendorManagementHeader" class="text-3xl font-semibold">Vendor Management</h2>
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
            <div class="card-table p-6 rounded-2xl shadow-soft h-auto max-w-6xl mx-auto">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <h3 id="vendorManagementTableHeader"
                        class="text-2xl font-semibold text-gray-800 text-center sm:text-left">Wet Section</h3>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="w-full md:w-72">
                            <div class="relative">
                                <div id="searchIcon"
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="search" id="vendorSearchInput" placeholder="Search..."
                                    class="block w-full pl-10 pr-10 py-2 border-gray-200 border rounded-lg leading-5 bg-gray-50 focus:bg-white focus:border-blue-400 focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-200">
                            </div>
                        </div>
                        <div class="w-full md:w-auto ml-auto">
                            <div id="vendorSectionNav" class="flex justify-center space-x-1 bg-gray-100 p-1 rounded-xl">
                                {{-- Section navigation buttons will be populated by JavaScript --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto space-x-8">
                    <table class="table-auto w-full responsive-table">
                        <thead>
                            <tr class="table-header">
                                <th class="px-8 py-4 text-left text-sm font-medium uppercase tracking-wider cursor-pointer sortable-header"
                                    data-sort-key="stallNumber">Stall/Table Number</th>
                                <th class="px-8 py-4 text-left text-sm font-medium uppercase tracking-wider cursor-pointer sortable-header"
                                    data-sort-key="vendorName">Vendor Name</th>
                                <th class="px-8 py-4 text-center text-sm font-medium uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="vendorTableBody">
                            {{-- Rows are dynamically inserted by staff.js --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Detail View (This should be here) --}}
        <div id="vendorDetailSection" class="hidden">
            <div
                class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
                </div>
                <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16">
                </div>
                <div class="flex justify-between items-center relative z-10">
                    <h2 class="text-3xl font-semibold">Vendor Information</h2>
                    <div class="flex items-center gap-3">
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
                    <button id="backToVendorList"
                        class="bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-white/30 transition-smooth flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </button>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 md:p-8 rounded-2xl shadow-soft">
                <div class="flex flex-col lg:flex-row gap-8">
                    {{-- Profile Picture Section --}}
                    <div class="w-full lg:w-auto flex flex-col items-center lg:items-start">
                        <div class="relative group">
                            <div
                                class="relative w-96 h-72 bg-gray-200 rounded-2xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                                <img id="profilePicturePreview" src="" alt="Vendor's Profile Picture"
                                    class="w-full h-full object-cover hidden">
                                <i id="profilePictureIcon" class="fas fa-user text-6xl text-gray-400"></i>
                            </div>
                            <label for="profilePictureInput"
                                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-lg font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-300 cursor-pointer rounded-2xl">
                                <i class="fas fa-camera mr-2"></i>
                                Change Picture
                            </label>
                            <input type="file" id="profilePictureInput" class="hidden" accept="image/*">
                        </div>
                        <div class="mt-6 text-left">
                            <a href="#" id="outstandingBalanceLink" data-section="homeSection"
                                class="text-2xl font-bold text-indigo-600 hover:underline">Outstanding
                                Balance</a>
                            <br>
                            <a href="#" id="paymentHistoryLink" data-section="paymentHistorySection"
                                class="text-2xl font-bold text-indigo-600 hover:underline mt-2 inline-block">Payment
                                History</a>
                        </div>
                    </div>
                    {{-- Profile Info Section --}}
                    <div class="w-full lg:flex-1">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-xl font-bold text-gray-800">Profile Information</h3>
                            <div class="flex items-center gap-2">
                                <button id="editVendorBtn"
                                    class="bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-bold px-6 py-2 rounded-lg transition-smooth flex items-center gap-2 shadow">
                                    <i class="fas fa-pencil-alt text-sm"></i>
                                    <span>Edit</span>
                                </button>
                            </div>
                        </div>
                        <div id="profileInfoContainer" class="space-y-4">
                            @php
                                $fields = [
                                    'vendorName' => ['icon' => 'fa-user', 'label' => 'Name'],
                                    'section' => ['icon' => 'fa-store', 'label' => 'Market Section'],
                                    'stallNumber' => ['icon' => 'fa-tag', 'label' => 'Stall Number'],
                                    'contact' => ['icon' => 'fa-phone', 'label' => 'Contact Number'],
                                    'appDate' => ['icon' => 'fa-calendar-alt', 'label' => 'Application Date'],
                                ];
                            @endphp

                            @foreach ($fields as $field => $details)
                                <div class="flex items-center p-2">
                                    <i class="fas {{ $details['icon'] }} text-indigo-500 w-6"></i>
                                    <span class="font-medium text-lg text-gray-600 w-48">{{ $details['label'] }}:</span>
                                    <span data-field="{{ $field }}"
                                        class="font-semibold text-gray-800 info-span"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="vendorSubSectionContainer" class="hidden">
            {{-- AJAX content will be loaded here --}}
        </div>
    </div>

    <div id="stallAssignmentSection" class="dashboard-section">
        <div class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-4 md:p-6 rounded-2xl mb-4 shadow-lg relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
            <h2 class="text-2xl md:text-3xl font-semibold">Stall Assignment</h2>
                    <p class="text-base md:text-lg mt-1">Assign available stalls to unassigned vendors.</p>
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

        <div class="card-table p-4 md:p-6 rounded-2xl shadow-soft">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 items-end">

                {{-- Vendor Selection --}}
                <div>
                    <label for="unassignedVendorSelect" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-plus mr-2"></i>Select an Unassigned Vendor
                    </label>
                    <select id="unassignedVendorSelect" class="w-full p-3 border border-gray-300 rounded-md bg-white">
                        <option value="">Loading vendors...</option>
                    </select>
                </div>

                {{-- Stall Selection --}}
                <div>
                    <label for="availableStallSelect" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-store mr-2"></i>Select an Available Stall
                    </label>
                    <div class="flex items-center gap-2">
                        <select id="stallSectionFilter" class="p-3 border border-gray-300 rounded-md bg-white">
                            {{-- Section options will be populated by JS --}}
                        </select>
                        <select id="availableStallSelect"
                            class="flex-grow p-3 border border-gray-300 rounded-md bg-white">
                            <option value="">Select a section first...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center">
                <button id="assignStallBtn" class="action-button w-full">
                    <i class="fas fa-link"></i>
                    Assign Stall to Vendor
                </button>
            </div>
        </div>
    </div>

    {{-- Dashboard Section --}}
    <div id="dashboardSection" class="dashboard-section">
        {{-- Header --}}
        <div
            class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
            </div>
            <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>
            <div class="flex items-center justify-between relative z-10">
                <h2 class="text-2xl md:text-3xl font-semibold">Dashboard</h2>
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

    {{-- Reports Section --}}
    <div id="reportsSection" class="dashboard-section">
        {{-- Header remains the same --}}
        <div
            class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
            </div>
            <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16">
            </div>
            <div class="flex items-center justify-between relative z-10">
                <h2 id="reportsHeader" class="text-3xl font-semibold">Reports</h2>
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

        <div class="bg-white p-4 md:p-8 rounded-2xl shadow-soft">
            {{-- Filters --}}
            <div class="flex flex-wrap items-end gap-4 mb-6 p-4 bg-gray-50 rounded-lg border">
                <div>
                    <label for="reportMonth" class="block text-sm font-medium text-gray-700">Select Month</label>
                    <input type="month" id="reportMonth" value="{{ now()->format('Y-m') }}"
                        class="mt-1 block w-full border-gray-300 shadow-sm sm:text-sm rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button id="generateReportBtn"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md flex items-center gap-2">
                    <i class="fas fa-search"></i>
                    <span>Generate Report</span>
                </button>
            </div>

            <div id="reportLoader" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                <p class="mt-2 text-gray-600">Generating Report...</p>
            </div>

            {{-- Report Results --}}
            <div id="reportResultContainer" class="hidden print-area space-y-8">
                <div class="flex justify-between items-start pb-4 border-b">
                    <div>
                        <h3 id="reportTitle" class="text-2xl font-bold text-gray-800"></h3>
                        <p id="reportPeriod" class="text-md text-gray-500"></p>
                    </div>
                    <div class="flex gap-2 print-hide">
                        <button id="downloadReportBtn"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            <span>Download</span>
                        </button>
                        <button id="printReportBtn"
                            class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-md flex items-center gap-2">
                            <i class="fas fa-print"></i>
                            <span>Print</span>
                        </button>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Summary Overview</h4>
                    <div id="reportKpis" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Collections Breakdown by Section</h4>
                    <div id="collectionsBreakdownContainer" class="overflow-x-auto rounded-lg border">
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Monthly Collection Trends</h4>
                        <div class="space-y-4">
                            <div class="h-40 bg-gray-50 p-4 rounded-lg border"><canvas id="rentChart"></canvas></div>
                            <div class="h-40 bg-gray-50 p-4 rounded-lg border"><canvas id="electricityChart"></canvas>
                            </div>
                            <div class="h-40 bg-gray-50 p-4 rounded-lg border"><canvas id="waterChart"></canvas></div>
                        </div>
                    </div>
                    <div class="overflow-auto">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Delinquent Vendors</h4>
                        <div class="rounded-lg border">
                            <table class="min-w-full ">
                                <thead>
                                    <tr class="table-header">
                                        <th class="px-4 py-2 text-left">Vendor</th>
                                        <th class="px-4 py-2 text-right">Amount Due</th>
                                    </tr>
                                </thead>
                                <tbody id="delinquentVendorsTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Notes/Comments</h4>
                    <textarea
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 transition"
                        rows="4" placeholder="Add any notes for this month's report..."></textarea>
                </div>
            </div>

            <div id="noReportDataMessage" class="hidden text-center py-12 text-gray-500">
                <i class="fas fa-info-circle text-4xl mb-4"></i>
                <p>No collection data found for the selected month.</p>
            </div>
        </div>
    </div>

    {{-- Edit Vendor Modal --}}
    <div id="editVendorModal"
        class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 hidden flex items-center justify-center transition-opacity duration-300">
        <div id="editVendorModalContent"
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-8 transform scale-95 opacity-0 transition-transform transition-opacity duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Edit Vendor Details</h3>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-800 transition-colors">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <form id="editVendorForm" class="space-y-4">
                @php
                    $modalFields = [
                        'vendorName' => ['icon' => 'fa-user', 'label' => 'Name', 'type' => 'text'],
                        'section' => ['icon' => 'fa-store', 'label' => 'Market Section', 'type' => 'text'],
                        'stallNumber' => ['icon' => 'fa-tag', 'label' => 'Stall Number', 'type' => 'text'],
                        'contact' => ['icon' => 'fa-phone', 'label' => 'Contact Number', 'type' => 'text'],
                        'appDate' => ['icon' => 'fa-calendar-alt', 'label' => 'Application Date', 'type' => 'date'],
                    ];
                @endphp

                @foreach ($modalFields as $field => $details)
                    <div>
                        <label for="modal_{{ $field }}"
                            class="block text-sm font-medium text-gray-700 mb-1">{{ $details['label'] }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas {{ $details['icon'] }} text-gray-400"></i>
                            </div>
                            @if ($field === 'section')
                                <select id="modal_{{ $field }}" data-field="{{ $field }}"
                                    class="info-input w-full pl-10 bg-slate-100 border-slate-300 border-2 rounded-md px-3 py-2 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all duration-200">
                                    {{-- Options will be populated by JavaScript --}}
                                </select>
                            @else
                                <input type="{{ $details['type'] }}" id="modal_{{ $field }}"
                                    data-field="{{ $field }}"
                                    class="info-input w-full pl-10 bg-slate-100 border-slate-300 border-2 rounded-md px-3 py-2 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all duration-200">
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end gap-4 pt-6">
                    <button type="button" id="cancelEditBtn"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-6 py-2 rounded-lg transition-smooth shadow">
                        Cancel
                    </button>
                    <button type="button" id="saveVendorBtn"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold px-6 py-2 rounded-lg transition-smooth flex items-center gap-2 shadow">
                        <i class="fas fa-save text-sm"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                            {{-- This will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Profile Section --}}
    <div id="profileSection" class="dashboard-section">
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

    {{-- Toast Notification --}}
    <div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2"></div>
@endsection
