<div
    class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg relative overflow-visible">
    {{-- Decorative elements --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full transform translate-x-32 -translate-y-32">
    </div>
    <div class="absolute bottom-0 right-16 w-32 h-32 bg-white/10 rounded-full transform translate-y-16"></div>

    {{-- Main Content --}}
    <div class="flex justify-between items-start relative z-10">
        {{-- Title and Subtitle --}}
        <div>
            <div class="flex items-center gap-3 mb-2">
                @if (isset($icon))
                    <i class="fas {{ $icon }} text-3xl"></i>
                @endif
                <h2 class="text-2xl md:text-3xl font-semibold">{{ $title ?? 'Page Title' }}</h2>
            </div>
            @if (isset($subtitle))
                <p class="text-base md:text-lg text-white/80 @if (isset($icon)) pl-12 @endif">{{ $subtitle }}</p>
            @endif
        </div>

        {{-- Notification Bell (removed wrapper, added relative positioning) --}}
        <div class="notificationBell relative">
            <button
                class="relative text-white hover:text-gray-200 focus:outline-none transition-transform transform hover:scale-110">
                <i class="fas fa-bell text-2xl"></i>
                <span
                    class="notificationDot absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-500 border-2 border-white hidden animate-pulse"></span>
            </button>

            {{-- Notification Dropdown Panel - moved inside bell container --}}
            <div
                class="notificationDropdown hidden absolute top-full right-0 mt-2 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-50">
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
