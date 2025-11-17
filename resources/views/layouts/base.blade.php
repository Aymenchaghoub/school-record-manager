<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SchoolSphere') }}</title>

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
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                    },
                },
            },
        }
    </script>
    
    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white shadow-soft border-r border-gray-200 transition-all duration-300 transform lg:translate-x-0 -translate-x-full fixed lg:static lg:inset-0 z-30 h-full">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <a href="{{ route('dashboard') }}" class="block hover:opacity-80 transition-opacity">
                        <x-logo size="default" />
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                    @yield('navigation')
                </nav>

                <!-- User Info -->
                <div class="px-4 py-4 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-secondary-400 to-secondary-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ Auth::user()->name ?? 'User' }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ ucfirst(Auth::user()->role ?? 'user') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Mobile menu button -->
                        <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500">
                            <i class="fas fa-bars text-xl"></i>
                        </button>

                        <!-- Page Title -->
                        <h1 class="text-2xl font-semibold text-gray-900 hidden lg:block">
                            @yield('header', 'Dashboard')
                        </h1>

                        <!-- Right Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <button type="button" class="relative p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <i class="far fa-bell text-xl"></i>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-danger-500 rounded-full"></span>
                            </button>

                            <!-- User Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" type="button" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm">
                                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="hidden md:block text-sm font-medium text-gray-700">
                                        {{ Auth::user()->name ?? 'User' }}
                                    </span>
                                    <i class="fas fa-chevron-down text-xs text-gray-400" :class="{ 'rotate-180': open }"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95" 
                                     class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                     @click.stop>
                                    <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                        <i class="fas fa-user-circle mr-2"></i> Profile
                                    </a>
                                    <a href="{{ route('password.reset') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                        <i class="fas fa-key mr-2"></i> Change Password
                                    </a>
                                    <hr class="my-1">
                                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 hover:text-danger-700 transition-colors rounded-lg">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <!-- Flash Messages -->
                        @if(session('success'))
                            <div class="mb-4 px-4 py-3 bg-success-100 border border-success-200 text-success-700 rounded-xl animate-slide-in" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 px-4 py-3 bg-danger-100 border border-danger-200 text-danger-700 rounded-xl animate-slide-in" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('warning'))
                            <div class="mb-4 px-4 py-3 bg-warning-100 border border-warning-200 text-warning-700 rounded-xl animate-slide-in" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <span>{{ session('warning') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="mb-4 px-4 py-3 bg-primary-100 border border-primary-200 text-primary-700 rounded-xl animate-slide-in" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span>{{ session('info') }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Page Content -->
                        @yield('content')
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-4 py-4">
                <div class="max-w-7xl mx-auto text-center">
                    <p class="text-sm text-gray-500">
                        © {{ date('Y') }} <span class="font-semibold text-gray-700">School<span class="text-blue-600">Sphere</span></span>. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 lg:hidden hidden" onclick="toggleSidebar()"></div>

    <!-- Alpine.js for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                
                if (!sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        });

        // Auto-dismiss success/error alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
