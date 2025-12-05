<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Virac Public Market')</title>
    @vite('resources/css/app.css')
    @vite('resources/css/all.min.css')
    @vite('resources/css/roboto.css')
    @vite('resources/js/app.js')

    <style>
        @media print {

            /* Hide the sidebar and any other elements you don't want to print */
            .sidebar-bg,
            .print-hide {
                display: none !important;
            }

            /* Ensure the main content area expands to fill the page */
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            /* Ensure the report itself is visible and doesn't have extra styling */
            .dashboard-section {
                display: none !important;
            }

            #reportsSection,
            #reportResultContainer {
                display: block !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 1rem !important;
            }
        }
    </style>
</head>

<body class="body-bg min-h-screen" style="font-family: 'Roboto', sans-serif;">
    <div id="globalLoadingSpinner" class="loading-overlay hidden">
        <div class="spinner-circle"></div>
    </div>
    <!-- 🔥 Skeleton Preloader (MUST be inside <body>) -->
    <div id="globalPreloader" class="skeleton-preloader">
        <!-- Sidebar Skeleton -->
        <aside class="skeleton-sidebar">
            <div class="skeleton-profile-img"></div>
            <div class="skeleton-sidebar-item"></div>
            <div class="skeleton-sidebar-item"></div>
            <div class="skeleton-sidebar-item"></div>
        </aside>

        <!-- Main Content Skeleton -->
        <main class="skeleton-main">
            <div class="skeleton-nav">
                <div class="skeleton-nav-item"></div>
                <div class="skeleton-nav-item"></div>
                <div class="skeleton-nav-item"></div>
            </div>

            <div class="skeleton-cards">
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
            </div>

            <div class="skeleton-table">
                <div class="skeleton-table-header"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
            </div>
        </main>
    </div>

    <!-- 🔥 Actual Dashboard Content -->
    <div id="dashboardContent">
        <div class="flex min-h-screen">
            <div id="sidebar"
                class="sidebar w-64 sidebar-bg shadow-xl fixed h-full border-r z-20 border-gray-100 flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center space-x-2">
                        <div class="w-12 flex-shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="Virac Public Market Logo"
                                class="w-full h-auto object-contain">
                        </div>
                        <div>
                            <h1
                                class="text-base font-semibold bg-gradient-to-r from-sky-500 to-indigo-600 bg-clip-text text-transparent">
                                Virac Public Market
                            </h1>
                            <p class="text-xs text-gray-500">Rent and Utility Management System</p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-stretch space-x-3">
                        <div class="flex-shrink-0">
                            <div id="sidebarProfileImage"
                                class="w-20 h-20 rounded-xl bg-gray-200 flex items-center justify-center shadow-inner overflow-hidden">
                                @php
                                    $sidebarProfileUrl = null;
                                    if (Auth::user()->role && Auth::user()->role->name == 'Vendor' && Auth::user()->profile_picture) {
                                        if (str_starts_with(Auth::user()->profile_picture, 'data:')) {
                                            $sidebarProfileUrl = Auth::user()->profile_picture;
                                        } else {
                                            $sidebarProfileUrl = Storage::disk('b2')->temporaryUrl(
                                                Auth::user()->profile_picture,
                                                now()->addDays(7)
                                            );
                                        }
                                    }
                                @endphp
                                @if($sidebarProfileUrl)
                                    <img src="{{ $sidebarProfileUrl }}" alt="Profile" class="w-full h-full object-cover">
                                @else
                                    <i id="sidebarProfileIcon" class="fas fa-user text-4xl text-gray-400"></i>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-1">
                            <div
                                class="flex items-center justify-center w-full bg-amber-500 text-white rounded-lg shadow py-2 px-4">
                                @if (Auth::user()->role && Auth::user()->role->name == 'Vendor')
                                    <div class="text-center">
                                        <span class="text-base font-semibold uppercase tracking-wide">
                                            {{ Auth::user()->role->name }}
                                        </span>
                                        <span class="block text-sm font-medium">
                                            {{ Auth::user()->stall->table_number ?? 'Unassigned Stall' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-base font-semibold uppercase tracking-wide">
                                        {{ str_replace('_', ' ', Auth::user()->role->name ?? 'No Role Assigned') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <nav class="mt-6 flex-grow overflow-y-auto">
                    <div class="px-4">
                        @yield('navigation')
                    </div>
                </nav>
                <div class="w-full p-4 mt-auto">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="w-full bg-gradient-to-r from-market-primary to-market-secondary text-white py-3 rounded-button hover:bg-none-[#b04143] hover:to-[#4f46e5] cursor-pointer shadow-lg hover:shadow-xl transition-smooth flex items-center justify-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="main-content flex-1 md:ml-64 p-4 sm:p-8 overflow-y-auto overflow-x-hidden">
                <button id="hamburgerButton"
                    class="md:hidden fixed top-4 left-4 z-30 p-2 rounded-md bg-gray-800 text-white">
                    <i class="fas fa-bars"></i>
                </button>
                @yield('content')
            </div>
        </div>
    </div>
</body>

</html>
