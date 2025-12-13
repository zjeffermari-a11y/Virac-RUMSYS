<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Virac Public Market</title>
    @vite('resources/css/app.css')
    @vite('resources/css/all.min.css')
    @vite('resources/css/roboto.css')
    @vite('resources/js/app.js')
    <style>
        .corner-border {
            position: relative;
        }
        .corner-border-top {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 3px;
            background: #eab308;
            border-radius: 0 12px 0 0;
            z-index: 10;
        }
        @media (min-width: 1024px) {
            .corner-border-top {
                left: 60%;
            }
        }
        .corner-border-right {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 3px;
            background: #eab308;
            z-index: 10;
        }
        .corner-border-bottom {
            position: absolute;
            bottom: 0;
            right: 0;
            left: 0;
            height: 3px;
            background: #eab308;
            border-radius: 0 0 12px 0;
            z-index: 10;
        }
        @media (min-width: 1024px) {
            .corner-border-bottom {
                left: 60%;
            }
        }
    </style>
</head>

<body class="h-screen bg-blue-50 overflow-hidden">
    <div class="h-screen flex w-full px-4 pt-4 pb-6 lg:px-6 lg:pt-6 lg:pb-8">
        <div class="bg-white shadow-soft overflow-hidden flex flex-col lg:flex-row w-full h-full rounded-2xl corner-border relative">
            <div class="corner-border-top"></div>
            <div class="corner-border-right"></div>
            <div class="corner-border-bottom"></div>
            <!-- Left Section: Market Image -->
            <div class="lg:w-3/5 relative h-full overflow-hidden">
                <img src="{{ asset('images/vpm.png') }}" alt="Virac Public Market" 
                    class="object-cover w-full h-full" style="object-position: center 30%;" />
                <div class="absolute inset-0 bg-black/20 flex flex-col justify-end p-6 lg:p-8">
                    <h2 class="text-xl lg:text-2xl font-bold text-white mb-1">Virac Public Market</h2>
                    <p class="text-xs lg:text-sm text-white/90">Rent and Utility Management System</p>
                </div>
            </div>

            <!-- Right Section: Forgot Password Form -->
            <div class="lg:w-2/5 px-6 pt-6 pb-6 lg:px-8 lg:pt-8 lg:pb-8 xl:px-10 xl:pt-10 flex flex-col justify-center max-w-md mx-auto lg:max-w-none h-full overflow-y-auto relative">
                <!-- Logo at upper right -->
                <div class="absolute top-5 right-5 lg:top-6 lg:right-6 xl:top-8 xl:right-8">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 lg:h-16 w-auto">
                </div>
                
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-1 h-8 bg-yellow-500 rounded-full"></div>
                        <div>
                            <h1 class="text-xl lg:text-2xl font-bold text-gray-800 mb-1">Forgot Password?</h1>
                            <p class="text-gray-600 text-xs lg:text-sm">Enter your username below. We will send a temporary password to your registered mobile number.</p>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="mb-4 p-3 text-xs lg:text-sm text-green-800 bg-green-100 rounded-xl flex items-center gap-2" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.sms') }}" class="space-y-5" id="forgotPasswordForm">
                    @csrf
                    
                    <div>
                        <label for="username" class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400 text-sm"></i>
                            </div>
                            <input id="username" name="username" type="text" autocomplete="username" required
                                value="{{ old('username') }}"
                                class="block w-full pl-10 pr-3 py-2.5 border-2 border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all @error('username') border-red-500 @enderror"
                                placeholder="Enter your username">
                        </div>
                        @error('username')
                            <div class="text-sm text-red-600 mt-2 flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <button type="submit" id="submitButton"
                            class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-market-primary hover:bg-market-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 cursor-pointer transition-all duration-300 shadow-lg hover:shadow-xl">
                            <span id="buttonText">Send Password Reset via SMS</span>
                            <span id="spinner" class="hidden ml-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}"
                            class="text-xs lg:text-sm font-medium text-market-secondary hover:text-yellow-500 transition-colors">
                            &larr; Back to Login
                        </a>
                    </div>
                </form>

                <p class="mt-6 text-center text-xs text-gray-500">
                    &copy; 2025 Rent and Utility Management System. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form submission handler
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            const button = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const spinner = document.getElementById('spinner');

            // Disable button and show spinner
            button.disabled = true;
            buttonText.textContent = 'Sending...';
            spinner.classList.remove('hidden');
        });
    </script>
</body>

</html>
