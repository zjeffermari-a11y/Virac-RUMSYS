@extends('layouts.app') {{-- Extends the newly created master layout --}}

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Virac Public Market - Meter Reader Clerk Dashboard') {{-- Sets the specific page title --}}

@vite('resources/js/meter.js')

@push('styles')
    <style>
        /* Hide all sections by default */
        .dashboard-section {
            display: none;
        }

        /* Show active section */
        .dashboard-section.active {
            display: block;
        }
    </style>
@endpush

@section('profile_summary')
    <div class="bg-gradient-to-r from-[#ffa600] to-[#ff8800] rounded-xl p-2 w-full text-center shadow-lg">
        <div class="font-bold text-yellow-900">Meter Reader Clerk</div>
    </div>
@endsection

@section('navigation')
    <div class="flex-grow">
        <a href="#homeSection" data-section="homeSection"
            class="nav-link active text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer flex items-center space-x-3">
            <i class="fas fa-tasks"></i>
            <span>Pending Tasks</span>
        </a>
        <a href="#electricitySection" data-section="electricitySection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer flex items-center space-x-3">
            <i class="fas fa-bolt"></i>
            <span>Electricity Meter Reading</span>
        </a>
        <a href="#notificationSection" data-section="notificationSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer flex items-center space-x-3">
            <i class="fas fa-bell"></i>
            <span>Edit Request</span>
        </a>
        {{-- This is the new link for the archives page --}}
        <a href="#archivesSection" data-section="archivesSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer flex items-center space-x-3">
            <i class="fas fa-archive"></i>
            <span>Archived Readings</span>
        </a>
        <a href="#profileSection" data-section="profileSection"
            class="nav-link text-black font-medium rounded-xl p-3 mb-2 hover:bg-gradient-to-r hover:from-[#9466ff] hover:to-[#4f46e5] cursor-pointer flex items-center space-x-3">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>

    </div>
@endsection

@section('content')
    <script>
        window.meterReadings = @json($meterReadings);
        window.editRequestsData = @json($editRequests);
        window.scheduleDay = @json($scheduleDay);
        window.billingMonthName = @json($billingMonthName);
        window.archiveMonths = @json($archiveMonths);
        window.unreadNotificationsCount = @json($unreadNotificationsCount);
        window.loggedInUserId = {{ auth()->id() }};
    </script>

    <div id="homeSection" class="dashboard-section active">
        @include('layouts.partials.content-header', ['title' => 'Hello, Meter Reader Clerk!'])

        <div class="mb-6 md:mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tasks"></i> Pending Tasks
                </h3>
            </div>

            <p>Please refer to the tables below for pending tasks and
                upcoming schedules for electricity readings</p>

            <div>
                <div class="card-gradient p-4 md:p- shadow-soft">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                        <h4 class="font-bold text-gray-800 flex items-center gap-2 text-base md:text-lg">
                            <i class="fas fa-bolt text-yellow-500"></i> Electricity Meter Reading
                        </h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full max-w-2xl overflow-hidden text-xs md:text-sm text-center">
                            <thead>
                                <tr class="table-header">
                                    <th class="px-6 py-4  text-sm font-medium uppercase tracking-wider text-auto">
                                        MONTH</th>
                                    <th class="px-6 py-4  text-sm font-medium uppercase tracking-wider text-auto">
                                        DAY</th>
                                    <th class="px-6 py-4  text-sm font-medium uppercase tracking-wider text-auto">
                                        START READING</th>
                            </thead>
                            <tbody>
                                @foreach ($upcomingTasks as $task)
                                    <tr class="table-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $task['month_name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $task['day'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if ($task['is_active'])
                                                <a href="#" data-section="electricitySection"
                                                    class="nav-link font-bold text-green-600 hover:text-green-800 transition-colors">
                                                    Start Reading <i class="fas fa-arrow-right ml-1"></i>
                                                </a>
                                            @else
                                                <span
                                                    class="inline-block font-bold bg-gray-300 text-gray-600 px-6 py-2 rounded-lg cursor-not-allowed">
                                                    <i class="fas fa-lock mr-2"></i> Start Reading
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- This is the new electricitySection div --}}
    <div id="electricitySection" class="dashboard-section">
        @include('layouts.partials.content-header', ['title' => 'Electricity Meter Reading'])

        {{-- Filters and Table Container --}}
        <div class="card-table p-4 sm:p-6 rounded-2xl shadow-soft h-auto max-w-6xl mx-auto">
            {{-- Filter Controls --}}
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="w-full md:w-72">
                    <div class="relative">
                        <div id="searchIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
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
                            data-section="Semi-Wet">
                            Semi-Wet
                        </button>
                    </div>
                </div>
            </div>

            {{-- Reading Table Container --}}
            <div class="overflow-x-auto space-x-8 mt-6">
                <table id="readingTable" class="table-fixed w-full responsive-table">
                    {{-- Thead and Tbody will be dynamically generated by meter.js --}}
                </table>
            </div>

            {{-- Submit Button Container --}}
            <div class="mt-6 flex justify-end">
                <button id="submitReadingsBtn" class="action-button">
                    <i class="fas fa-paper-plane"></i>
                    Submit Readings
                </button>
            </div>
            <div id="paginationContainer" class="mt-6 flex flex-wrap justify-center items-center gap-2"></div>
        </div>
    </div>

    <div id="notificationSection" class="dashboard-section">
        @include('layouts.partials.content-header', ['title' => 'Edit Requests'])

        {{-- Notification Table Container --}}
        <div class="card-table p-6 rounded-2xl shadow-soft h-auto max-w-6xl mx-auto">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2 mb-4">
                <i class="fas fa-history text-market-primary"></i> Edit Request History
            </h3>

            <div class="overflow-x-auto">
                <table class="min-w-full responsive-table">
                    <thead id="notificationTableHeader">
                        <tr class="table-header">
                            <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Request Date</th>
                            <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Stall Number</th>
                            <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-4 text-left text-sm font-medium uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody id="notificationTableBody">
                        {{-- Notification rows will be dynamically inserted here by meter.js --}}
                    </tbody>
                </table>
            </div>

            {{-- Message for when there are no notifications --}}
            <div id="noNotificationsMessage" class="text-center py-12 text-gray-500 hidden">
                <i class="fas fa-bell-slash text-4xl mb-4"></i>
                <p>You have no edit requests at this time.</p>
            </div>
        </div>
    </div>

    <div id="archivesSection" class="dashboard-section">
        {{-- Header --}}
        @include('layouts.partials.content-header', ['title' => 'Archived Meter Readings'])

        {{-- Filter Form --}}
        <form id="archiveFilterForm">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                {{-- Left side: Search and Month --}}
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="relative w-full sm:w-72">
                        <input type="search" name="search" placeholder="Search by Stall Number..."
                            class="block w-full pl-10 pr-4 py-2 border-gray-300 border rounded-lg bg-white focus:border-blue-400">
                        <i class="fas fa-search text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                    </div>
                    <select name="month" class="w-full sm:w-auto border-gray-300 border rounded-lg bg-white p-2">
                        <option value="">All Months</option>
                        @foreach ($archiveMonths as $month)
                            <option value="{{ $month->month_value }}">{{ $month->month }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Right side: Section Toggles --}}
                <div class="flex justify-center space-x-1 bg-gray-100 p-1 rounded-xl">
                    <button type="button" class="section-nav-btn px-4 py-2 rounded-lg font-medium active"
                        data-section="">All Sections</button>
                    <button type="button" class="section-nav-btn px-4 py-2 rounded-lg font-medium"
                        data-section="Wet Section">Wet Section</button>
                    <button type="button" class="section-nav-btn px-4 py-2 rounded-lg font-medium"
                        data-section="Dry Section">Dry Section</button>
                    <button type="button" class="section-nav-btn px-4 py-2 rounded-lg font-medium"
                        data-section="Semi-Wet">Semi-Wet</button>
                </div>
            </div>
        </form>

        <div id="archiveScrollContainer" class="card-table rounded-2xl shadow-soft overflow-y-auto mt-4"
            style="max-height: 600px;">
            <div id="archiveResultsContainer" class="space-y-4">
                {{-- Results will be rendered by JavaScript --}}
            </div>

            <div id="archiveLoadingIndicator" class="hidden text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-market-primary"></i>
                <p class="mt-2 text-gray-600">Loading...</p>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full mx-4 relative">
                <button id="closeModalTop" type="button"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>

                <div class="text-left">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-3xl text-gray-800"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Confirm Meter Reading</h3>
                    </div>

                    <p class="text-gray-600 mb-5">
                        Are you sure you want to submit the following reading?
                        Once submitted, the data will be saved and cannot be edited unless permitted.
                    </p>

                    <div class="flex items-center gap-2 text-green-700 mb-8">
                        <i class="fas fa-check-circle"></i>
                        <p class="text-sm font-medium">Make sure the reading is correct before confirming.</p>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button id="cancelSubmit" type="button"
                            class="px-6 py-2 text-gray-800 bg-gray-200 font-semibold rounded-lg shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            Check again
                        </button>
                        <button id="confirmSubmit" type="button"
                            class="px-6 py-2 bg-amber-400 text-amber-900 font-bold rounded-lg shadow-sm hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="requestEditModal" class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full mx-4 relative border-2 border-amber-400">
                <button id="closeRequestModalBtn" type="button"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
                <div class="text-left">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-3xl text-gray-800"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Confirm Request Edit</h3>
                    </div>
                    <p class="text-gray-600 mb-5">
                        Please review the details carefully. This request will be sent to the admin for approval. Once
                        submitted, the current billing entry cannot be changed unless approved.
                    </p>
                    <div class="mb-6">
                        <label for="editReason" class="block text-sm font-medium text-gray-700 mb-2">Why are you
                            requesting an edit?</label>
                        <textarea id="editReason" rows="3"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500"
                            placeholder="e.g., Typo in the original reading..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button id="cancelRequestBtn" type="button"
                            class="px-6 py-2 text-gray-800 bg-gray-200 font-semibold rounded-lg shadow-sm hover:bg-gray-300">
                            Cancel
                        </button>
                        <button id="sendRequestBtn" type="button"
                            class="px-6 py-2 bg-amber-400 text-amber-900 font-bold rounded-lg shadow-sm hover:bg-amber-500">
                            Send Request
                        </button>
                    </div>
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
@endsection
