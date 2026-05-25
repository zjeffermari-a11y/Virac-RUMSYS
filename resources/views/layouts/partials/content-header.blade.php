{{-- ============================================================ --}}
{{-- NexaVerse-style page header: simple title + action buttons  --}}
{{-- ============================================================ --}}
<div class="flex items-center justify-between mb-6">

    {{-- Page title --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-dark-muted leading-tight">
            @if (isset($icon))
                <i class="fas {{ $icon }} text-dark-accent mr-2 text-xl"></i>
            @endif
            {{ $title ?? 'Page Title' }}
        </h2>
        @if (isset($subtitle))
            <p class="text-sm text-gray-500 dark:text-dark-border mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    {{-- Right-side actions: search + theme toggle + notification --}}
    <div class="flex items-center gap-3">

        {{-- Dark Mode Toggle --}}
        <button type="button"
            class="theme-toggle w-9 h-9 rounded-full bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border/40 flex items-center justify-center text-gray-500 dark:text-dark-border hover:text-[#011936] dark:hover:text-dark-muted shadow-sm transition-all duration-200 hover:scale-110 focus:outline-none"
            aria-label="Toggle theme">
            <i class="theme-toggle-dark-icon hidden fas fa-moon text-sm"></i>
            <i class="theme-toggle-light-icon hidden fas fa-sun text-sm"></i>
        </button>

        {{-- Notification Bell --}}
        <div class="notificationBell relative">
            <button
                aria-label="View notifications"
                aria-expanded="false"
                aria-haspopup="true"
                class="relative w-9 h-9 rounded-full bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border/40 flex items-center justify-center text-gray-500 dark:text-dark-border hover:text-[#011936] dark:hover:text-dark-muted shadow-sm transition-all duration-200 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-dark-accent">
                <i class="fas fa-bell text-sm" aria-hidden="true"></i>
                <span
                    class="notificationDot absolute -top-0.5 -right-0.5 block h-2.5 w-2.5 rounded-full bg-red-500 border-2 border-white dark:border-dark-surface hidden animate-pulse"
                    aria-hidden="true"></span>
            </button>

            {{-- Notification Dropdown Panel --}}
            <div
                class="notificationDropdown hidden absolute top-full right-0 mt-2 w-96 bg-white dark:bg-dark-surface rounded-xl shadow-2xl border border-gray-100 dark:border-dark-border/40 z-50 overflow-hidden">
                <div class="px-4 py-3 font-semibold text-gray-800 dark:text-dark-muted border-b border-gray-100 dark:border-dark-border/30 flex items-center gap-2">
                    <i class="fas fa-bell text-dark-accent text-sm"></i> Notifications
                </div>
                <div class="notificationList max-h-96 overflow-y-auto">
                    {{-- Notification items will be inserted here by JS --}}
                </div>
            </div>
        </div>
    </div>
</div>
