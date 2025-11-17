<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'SchoolSphere') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    
    <!-- Temporary: Using Tailwind CDN until Vite issue is resolved -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
                        warning: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="h-full bg-gradient-to-br from-primary-50 via-white to-secondary-50">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 animate-fade-in">
            <!-- Logo -->
            <div class="text-center">
                <div class="mx-auto flex justify-center mb-6 group">
                    <svg class="h-24 w-24 drop-shadow-lg transition-transform duration-300 group-hover:scale-110" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 5 L95 50 L50 95 L5 50 Z" fill="#000000"/>
                        <path d="M50 5 L95 50 L70 50 Q60 40 50 40 Q40 40 30 50 L5 50 Z" fill="#0066FF"/>
                        <ellipse cx="50" cy="50" rx="35" ry="8" fill="#FFFFFF"/>
                        <path d="M5 50 L30 50 Q40 60 50 60 Q60 60 70 50 L95 50 L50 95 Z" fill="#0066FF"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Welcome back</h2>
                <p class="mt-2 text-sm text-gray-600">Sign in to your <span class="font-semibold">School<span class="text-blue-600">Sphere</span></span> account</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-soft p-8">
                @if(session('error'))
                <div class="mb-6 px-4 py-3 bg-danger-50 border border-danger-200 text-danger-700 rounded-xl animate-slide-in">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-danger-50 border border-danger-200 text-danger-700 rounded-xl animate-slide-in">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required autofocus
                                   value="{{ old('email') }}"
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 @error('email') border-danger-300 @enderror"
                                   placeholder="you@example.com">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 @error('password') border-danger-300 @enderror"
                                   placeholder="Enter your password">
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.reset') }}" class="text-sm text-primary-600 hover:text-primary-500 transition-colors">
                            Forgot your password?
                        </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 hover:shadow-md">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign in
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Demo Credentials</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gradient-to-br from-primary-50 to-primary-100 p-3 rounded-lg border border-primary-200">
                            <p class="text-xs font-semibold text-primary-900">Admin</p>
                            <p class="text-xs text-primary-700 mt-1">admin@school.com</p>
                            <p class="text-xs text-primary-600">password</p>
                        </div>
                        <div class="bg-gradient-to-br from-success-50 to-success-100 p-3 rounded-lg border border-success-200">
                            <p class="text-xs font-semibold text-success-900">Teacher</p>
                            <p class="text-xs text-success-700 mt-1">teacher@school.com</p>
                            <p class="text-xs text-success-600">password</p>
                        </div>
                        <div class="bg-gradient-to-br from-warning-50 to-warning-100 p-3 rounded-lg border border-warning-200">
                            <p class="text-xs font-semibold text-warning-900">Student</p>
                            <p class="text-xs text-warning-700 mt-1">student@school.com</p>
                            <p class="text-xs text-warning-600">password</p>
                        </div>
                        <div class="bg-gradient-to-br from-secondary-50 to-secondary-100 p-3 rounded-lg border border-secondary-200">
                            <p class="text-xs font-semibold text-secondary-900">Parent</p>
                            <p class="text-xs text-secondary-700 mt-1">parent@school.com</p>
                            <p class="text-xs text-secondary-600">password</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-500">
                © {{ date('Y') }} <span class="font-semibold text-gray-700">School<span class="text-blue-600">Sphere</span></span>. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
