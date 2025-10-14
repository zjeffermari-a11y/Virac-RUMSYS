<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Virac Public Market</title>
    @vite('resources/css/app.css')
    @vite('resources/css/all.min.css')
    @vite('resources/js/app.js')
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex flex-col justify-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex justify-center">
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col md:flex-row w-full max-w-5xl">
                <div class="md:w-1/2 relative h-64 md:h-auto overflow-hidden">
                    <img src="{{ asset('images/login.png') }}" alt="Virac Public Market"
                        class="object-cover w-full h-full object-top" />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-blue-900/70 to-transparent flex flex-col justify-end p-8 text-white">
                        <h2 class="text-2xl font-bold mb-2">Virac Public Market</h2>
                        <p class="text-sm opacity-90">Rent and Utility Management System</p>
                    </div>
                </div>

                <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Forgot Password?</h1>
                        <p class="text-gray-600">Enter your username below. We will send a temporary password to your
                            registered mobile number.</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 p-4 text-sm text-green-800 bg-green-100 rounded-lg" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.sms') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                                Username
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="username" name="username" type="text" autocomplete="username" required
                                    value="{{ old('username') }}"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror"
                                    placeholder="Enter your username">
                            </div>
                            @error('username')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit"
                                class="rounded-button whitespace-nowrap w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Send Password Reset via SMS
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-6">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            &larr; Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
