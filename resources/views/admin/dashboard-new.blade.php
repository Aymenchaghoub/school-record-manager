@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-soft p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-primary-100">Here's what's happening in your school today.</p>
            </div>
            <div class="hidden lg:block">
                <i class="fas fa-chart-line text-6xl text-primary-200 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card
            title="Total Students"
            :value="$statistics['total_students'] ?? 0"
            icon="fas fa-user-graduate"
            color="primary"
            trend="up"
            trend-value="+12%"
        />
        
        <x-stat-card
            title="Total Teachers"
            :value="$statistics['total_teachers'] ?? 0"
            icon="fas fa-chalkboard-teacher"
            color="success"
            trend="up"
            trend-value="+5%"
        />
        
        <x-stat-card
            title="Total Classes"
            :value="$statistics['total_classes'] ?? 0"
            icon="fas fa-school"
            color="warning"
        />
        
        <x-stat-card
            title="Active Events"
            :value="$statistics['active_events'] ?? 0"
            icon="fas fa-calendar-check"
            color="info"
            trend="up"
            trend-value="+8%"
        />
    </div>

    <!-- Quick Actions -->
    <x-card title="Quick Actions" icon="fas fa-bolt">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.users.create') }}" class="group">
                <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl p-6 text-center hover:shadow-md transition-all duration-300 border border-primary-200 group-hover:scale-105">
                    <i class="fas fa-user-plus text-3xl text-primary-600 mb-3"></i>
                    <p class="text-sm font-medium text-gray-700">Add User</p>
                </div>
            </a>
            
            <a href="{{ route('admin.classes.create') }}" class="group">
                <div class="bg-gradient-to-br from-success-50 to-success-100 rounded-xl p-6 text-center hover:shadow-md transition-all duration-300 border border-success-200 group-hover:scale-105">
                    <i class="fas fa-plus-circle text-3xl text-success-600 mb-3"></i>
                    <p class="text-sm font-medium text-gray-700">Add Class</p>
                </div>
            </a>
            
            <a href="{{ route('admin.subjects.create') }}" class="group">
                <div class="bg-gradient-to-br from-warning-50 to-warning-100 rounded-xl p-6 text-center hover:shadow-md transition-all duration-300 border border-warning-200 group-hover:scale-105">
                    <i class="fas fa-book text-3xl text-warning-600 mb-3"></i>
                    <p class="text-sm font-medium text-gray-700">Add Subject</p>
                </div>
            </a>
            
            <a href="{{ route('admin.events.create') }}" class="group">
                <div class="bg-gradient-to-br from-danger-50 to-danger-100 rounded-xl p-6 text-center hover:shadow-md transition-all duration-300 border border-danger-200 group-hover:scale-105">
                    <i class="fas fa-calendar-plus text-3xl text-danger-600 mb-3"></i>
                    <p class="text-sm font-medium text-gray-700">Add Event</p>
                </div>
            </a>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <x-card title="Recent Users" icon="fas fa-users">
            <x-slot name="actions">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </x-slot>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recent_users ?? [] as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <x-badge :type="$user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'warning' : ($user->role === 'student' ? 'info' : 'success'))" size="sm">
                                    {{ ucfirst($user->role) }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <x-badge :type="$user->is_active ? 'success' : 'danger'" size="sm">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No recent users</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Upcoming Events -->
        <x-card title="Upcoming Events" icon="fas fa-calendar-alt">
            <x-slot name="actions">
                <a href="{{ route('admin.events.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </x-slot>
            
            <div class="space-y-4">
                @forelse($upcoming_events ?? [] as $event)
                <div class="flex items-center space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-100 to-primary-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-primary-600"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                        <p class="text-xs text-gray-500">{{ $event->event_date->format('M d, Y') }}</p>
                    </div>
                    <x-badge :type="$event->is_mandatory ? 'danger' : 'info'" size="sm">
                        {{ $event->is_mandatory ? 'Mandatory' : 'Optional' }}
                    </x-badge>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No upcoming events</p>
                </div>
                @endforelse
            </div>
        </x-card>
    </div>

    <!-- Recent Activities -->
    <x-card title="Recent Activities" icon="fas fa-history">
        <div class="space-y-4">
            @forelse($recent_activities ?? [] as $activity)
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-full flex items-center justify-center">
                        <i class="{{ $activity->icon ?? 'fas fa-info' }} text-secondary-600 text-xs"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">{{ $activity->description ?? 'Activity performed' }}</p>
                    <p class="text-xs text-gray-500">{{ $activity->created_at ?? now() }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No recent activities</p>
            </div>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
