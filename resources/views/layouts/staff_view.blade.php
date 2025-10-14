<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Virac Public Market')</title>
    @vite('resources/css/app.css')
    @vite('resources/css/all.min.css')
    @vite('resources/css/roboto.css')
    @vite('resources/js/app.js')
</head>

<body class="bg-gray-100 font-sans">
    <div id="app">
<header
    class="bg-gradient-to-r from-market-primary to-market-secondary text-white p-4 shadow-md fixed top-0 left-0 right-0 z-50">
    <div class="container mx-auto flex justify-end items-center">
        {{-- This link now correctly returns to the specific vendor's detail view --}}
        <a href="{{ url('/staff#vendorDetail/' . $vendor->id) }}" class="text-white font-bold py-2 px-4 rounded-lg hover:bg-white/10 transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Vendor Information</span>
        </a>
    </div>
</header>


        <main class="py-20 px-4">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>

</html>
