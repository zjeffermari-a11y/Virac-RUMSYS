@php
    use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin" />
    <title>@yield('title', 'Virac Public Market')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite('resources/css/app.css')
    @vite('resources/css/all.min.css')
    @vite('resources/js/app.js')
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Dark Mode Initializer -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

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

<body class="bg-[#f0f2f5] dark:bg-dark-bg text-slate-800 dark:text-dark-muted min-h-screen font-sans transition-colors duration-300 antialiased selection:bg-dark-accent selection:text-dark-bg">
    {{-- Skip to main content link for keyboard/screen reader users --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 z-50 bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold shadow-lg">
        Skip to main content
    </a>

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
            {{-- ======================================================= --}}
            {{-- SIDEBAR: Always dark navy (#011936) — NexaVerse style    --}}
            {{-- ======================================================= --}}
            <div id="sidebar"
                class="sidebar w-64 bg-[#011936] fixed h-full border-r border-white/5 z-20 flex flex-col transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl">

                {{-- Logo / Brand --}}
                <div class="p-5 border-b border-white/10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex-shrink-0 rounded-lg bg-dark-accent/20 flex items-center justify-center">
                            <img src="{{ asset('images/logo.png') }}" alt="Virac Public Market Logo"
                                width="36" height="36" class="w-full h-auto object-contain">
                        </div>
                        <div>
                            <h1 class="text-sm font-bold text-white leading-tight">Virac Public Market</h1>
                            <p class="text-[10px] text-dark-border">Rent &amp; Utility System</p>
                        </div>
                    </div>
                </div>

                {{-- Profile Badge --}}
                <div class="px-4 py-4 border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <div id="sidebarProfileImage"
                            class="w-10 h-10 rounded-full bg-dark-accent/30 flex items-center justify-center shadow-inner overflow-hidden flex-shrink-0">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ Auth::user()->profile_picture_url }}"
                                     alt="Profile picture of {{ Auth::user()->name }}"
                                     width="40" height="40"
                                     loading="lazy"
                                     class="w-full h-full object-cover"
                                     id="sidebarProfilePicture">
                                <i id="sidebarProfileIcon" class="fas fa-user text-lg text-dark-border hidden" aria-hidden="true"></i>
                            @else
                                <img id="sidebarProfilePicture" src="" alt="" width="40" height="40" class="w-full h-full object-cover hidden">
                                <i id="sidebarProfileIcon" class="fas fa-user text-lg text-dark-border" aria-hidden="true"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div id="sidebarUserName" class="text-xs font-semibold text-white truncate">{{ Auth::user()->name ?? 'User' }}</div>
                            <div class="text-[10px] text-dark-accent font-medium uppercase tracking-wider">
                                {{ str_replace('_', ' ', Auth::user()->role->name ?? 'No Role') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="mt-4 flex-grow overflow-y-auto px-3" aria-label="Main navigation">
                    @yield('navigation')
                </nav>

                {{-- Logout --}}
                <div class="p-4 border-t border-white/10">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <button type="submit" form="logout-form"
                        class="w-full flex items-center gap-3 text-dark-border hover:text-white hover:bg-white/10 rounded-xl px-4 py-3 transition-all duration-200 cursor-pointer text-sm font-medium">
                        <i class="fas fa-sign-out-alt text-dark-accent" aria-hidden="true"></i>
                        <span>Log out</span>
                    </button>
                </div>
            </div>

            <div id="main-content" class="main-content flex-1 md:ml-64 p-4 sm:p-6">
                <button id="hamburgerButton"
                    aria-label="Open sidebar menu"
                    aria-expanded="false"
                    aria-controls="sidebar"
                    class="md:hidden fixed top-4 left-4 z-30 p-2 rounded-md bg-[#011936] text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-dark-accent">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Theme Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtns = document.querySelectorAll('.theme-toggle');
            
            // Check current theme
            const isDarkMode = localStorage.getItem('theme') === 'dark' || 
                               (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
                               
            toggleBtns.forEach(btn => {
                const darkIcon = btn.querySelector('.theme-toggle-dark-icon');
                const lightIcon = btn.querySelector('.theme-toggle-light-icon');
                
                // Set initial icon state
                if (isDarkMode) {
                    lightIcon.classList.remove('hidden');
                } else {
                    darkIcon.classList.remove('hidden');
                }

                btn.addEventListener('click', function() {
                    // Toggle icons on all buttons
                    toggleBtns.forEach(b => {
                        b.querySelector('.theme-toggle-dark-icon').classList.toggle('hidden');
                        b.querySelector('.theme-toggle-light-icon').classList.toggle('hidden');
                    });

                    // Toggle theme class
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                    
                    // Dispatch event for chart.js updates
                    window.dispatchEvent(new Event('theme-changed'));
                });
            });
        });
    </script>
</body>

</html>
