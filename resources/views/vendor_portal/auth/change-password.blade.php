<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Password - Virac Public Market</title>
    @vite(['resources/css/app.css'])
    @vite(['resources/css/all.min.css'])
    @vite(['resources/js/app.js'])
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
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Create a New Password</h1>
                        <p class="text-gray-600">For your security, you must create a new permanent password.</p>
                    </div>

                    @if (session('warning'))
                        <div class="mb-4 p-4 text-sm text-yellow-800 bg-yellow-100 rounded-lg" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('vendor.password.update') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="username" type="text" name="username"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm @error('username') border-red-500 @enderror"
                                    value="{{ old('username', Auth::user()->username) }}"
                                    placeholder="Create a unique username" required>
                            </div>
                            @error('username')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current
                                Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-gray-400"></i>
                                </div>
                                <input id="current_password" type="password" name="current_password"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm @error('current_password') border-red-500 @enderror"
                                    placeholder="Enter temporary password" required autofocus>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-eye text-gray-400 cursor-pointer toggle-password-icon"></i>
                                </div>
                            </div>
                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New
                                Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" type="password" name="password"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm @error('password') border-red-500 @enderror"
                                    placeholder="Create a strong password" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-eye text-gray-400 cursor-pointer toggle-password-icon"></i>
                                </div>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password_confirmation" type="password" name="password_confirmation"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm"
                                    placeholder="Re-enter your new password" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-eye text-gray-400 cursor-pointer toggle-password-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div id="passwordRequirements" class="pt-2">
                            <div class="space-y-1.5">
                                <div id="req-length"
                                    class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                    <i class="fas fa-circle text-xs"></i>Must be at least 8 characters
                                </div>
                                <div id="req-letter"
                                    class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                    <i class="fas fa-circle text-xs"></i>Must contain at least one letter
                                </div>
                                <div id="req-number"
                                    class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                    <i class="fas fa-circle text-xs"></i>Must contain at least one number
                                </div>
                                <div id="req-special"
                                    class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                    <i class="fas fa-circle text-xs"></i>Must contain at least one special character
                                </div>
                                <div id="req-case"
                                    class="flex items-center gap-2 text-xs text-gray-500 transition-all duration-200 hidden">
                                    <i class="fas fa-circle text-xs"></i>Must contain both uppercase and lowercase
                                    letters
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4">
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form-vendor').submit();"
                                class="w-1/3 text-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                Back
                            </a>
                            <button type="submit"
                                class="rounded-button whitespace-nowrap w-2/3 flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                Update Password & Continue
                            </button>
                        </div>
                    </form>

                    <form id="logout-form-vendor" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- The Javascript for password validation remains the same --}}
    <script>
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
                const text = element.querySelector('span');

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
                const checks = [{
                        key: 'length',
                        isValid: value.length >= 8
                    },
                    {
                        key: 'letter',
                        isValid: /[a-zA-Z]/.test(value)
                    },
                    {
                        key: 'number',
                        isValid: /\d/.test(value)
                    },
                    {
                        key: 'special',
                        isValid: /[^a-zA-Z0-9]/.test(value)
                    },
                    {
                        key: 'case',
                        isValid: /[a-z]/.test(value) && /[A-Z]/.test(value)
                    }
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
        });
    </script>
</body>

</html>
