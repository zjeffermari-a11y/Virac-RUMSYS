<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Password - Virac Public Market</title>
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

            <!-- Right Section: Change Password Form -->
            <div class="lg:w-2/5 px-6 pt-6 pb-6 lg:px-8 lg:pt-8 lg:pb-8 xl:px-10 xl:pt-10 flex flex-col justify-center max-w-md mx-auto lg:max-w-none h-full overflow-y-auto relative">
                <!-- Logo at upper right -->
                <div class="absolute top-5 right-5 lg:top-6 lg:right-6 xl:top-8 xl:right-8">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 lg:h-16 w-auto">
                </div>
                
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-1 h-8 bg-yellow-500 rounded-full"></div>
                        <div>
                            <h1 class="text-xl lg:text-2xl font-bold text-gray-800 mb-1">Create a New Password</h1>
                            <p class="text-gray-600 text-xs lg:text-sm">For your security, you must create a new permanent password</p>
                        </div>
                    </div>
                </div>

                @if (session('warning'))
                    <div class="mb-4 p-3 text-xs lg:text-sm text-yellow-800 bg-yellow-100 rounded-xl flex items-center gap-2" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('vendor.password.update') }}" class="space-y-5" id="changePasswordForm">
                    @csrf

                    <div>
                        <label for="username" class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400 text-sm"></i>
                            </div>
                            <input id="username" type="text" name="username"
                                value="{{ old('username', Auth::user()->username) }}"
                                class="block w-full pl-10 pr-3 py-2.5 border-2 border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all @error('username') border-red-500 @enderror"
                                placeholder="Enter your username" required>
                        </div>
                        @error('username')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_password" class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">
                            Current Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-key text-gray-400 text-sm"></i>
                            </div>
                            <input id="current_password" type="password" name="current_password"
                                class="block w-full pl-10 pr-10 py-2.5 border-2 border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all @error('current_password') border-red-500 @enderror"
                                placeholder="Enter temporary password" required autofocus>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600 text-sm" data-target="current_password"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400 text-sm"></i>
                            </div>
                            <input id="password" type="password" name="password"
                                class="block w-full pl-10 pr-10 py-2.5 border-2 border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all @error('password') border-red-500 @enderror"
                                placeholder="Create a strong password" required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600 text-sm" data-target="password"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400 text-sm"></i>
                            </div>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                class="block w-full pl-10 pr-10 py-2.5 border-2 border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                                placeholder="Re-enter your new password" required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 cursor-pointer hover:text-gray-600 text-sm" data-target="password_confirmation"></i>
                            </button>
                        </div>
                    </div>

                    <div id="passwordRequirements" class="pt-2">
                        <div class="space-y-1.5">
                            <div id="req-length" class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                <i class="fas fa-circle text-xs"></i>
                                <span>Must be at least 8 characters</span>
                            </div>
                            <div id="req-letter" class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                <i class="fas fa-circle text-xs"></i>
                                <span>Must contain at least one letter</span>
                            </div>
                            <div id="req-number" class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                <i class="fas fa-circle text-xs"></i>
                                <span>Must contain at least one number</span>
                            </div>
                            <div id="req-special" class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                <i class="fas fa-circle text-xs"></i>
                                <span>Must contain at least one special character</span>
                            </div>
                            <div id="req-case" class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                <i class="fas fa-circle text-xs"></i>
                                <span>Must contain both uppercase and lowercase letters</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form-vendor').submit();"
                            class="w-1/3 text-center py-2.5 px-4 border-2 border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-all">
                            Back
                        </a>
                        <button type="submit" id="submitButton"
                            class="w-2/3 flex justify-center items-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-market-primary hover:bg-market-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 cursor-pointer transition-all duration-300 shadow-lg hover:shadow-xl">
                            <span id="buttonText">Update Password & Continue</span>
                            <span id="spinner" class="hidden ml-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>

                <form id="logout-form-vendor" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

                <p class="mt-6 text-center text-xs text-gray-500">
                    &copy; 2025 Virac Public Market Management System. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.querySelector('i').getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const eyeIcon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        });

        // Password validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const requirements = {
                length: document.getElementById('req-length'),
                letter: document.getElementById('req-letter'),
                number: document.getElementById('req-number'),
                special: document.getElementById('req-special'),
                case: document.getElementById('req-case'),
            };

            const updateRequirementUI = (element, isValid) => {
                if (!element) return;
                const icon = element.querySelector('i');
                
                element.classList.remove('text-gray-500', 'text-green-600');
                icon.classList.remove('fa-circle', 'fa-check-circle', 'text-gray-400', 'text-green-500');

                if (isValid) {
                    icon.classList.add('fa-check-circle', 'text-green-500');
                    element.classList.add('text-green-600');
                } else {
                    icon.classList.add('fa-circle', 'text-gray-400');
                    element.classList.add('text-gray-500');
                }
            };

            const toggleRequirementVisibility = (element, show) => {
                if (!element) return;
                element.classList.toggle('hidden', !show);
            };

            const validatePassword = () => {
                const value = passwordInput.value;
                const checks = [
                    { key: 'length', isValid: value.length >= 8 },
                    { key: 'letter', isValid: /[a-zA-Z]/.test(value) },
                    { key: 'number', isValid: /\d/.test(value) },
                    { key: 'special', isValid: /[^a-zA-Z0-9]/.test(value) },
                    { key: 'case', isValid: /[a-z]/.test(value) && /[A-Z]/.test(value) }
                ];

                let allPreviousValid = true;
                for (const check of checks) {
                    const reqEl = requirements[check.key];
                    if (allPreviousValid) {
                        toggleRequirementVisibility(reqEl, true);
                        updateRequirementUI(reqEl, check.isValid);
                        if (!check.isValid) {
                            allPreviousValid = false;
                        }
                    } else {
                        toggleRequirementVisibility(reqEl, false);
                        updateRequirementUI(reqEl, false);
                    }
                }
            };

            passwordInput.addEventListener('input', validatePassword);

            // Form submission handler
            document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
                const button = document.getElementById('submitButton');
                const buttonText = document.getElementById('buttonText');
                const spinner = document.getElementById('spinner');

                button.disabled = true;
                buttonText.textContent = 'Updating...';
                spinner.classList.remove('hidden');
            });
        });
    </script>
</body>

</html>
