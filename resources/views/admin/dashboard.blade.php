@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('header', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 text-white overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-primary-100 text-lg">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Total Students -->
        <x-modern.stat-card
            title="Total Students"
            :value="$stats['total_students'] ?? 0"
            color="primary"
            change="+12%"
            changeType="increase"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>'
        />
        
        <!-- Total Teachers -->
        <x-modern.stat-card
            title="Total Teachers"
            :value="$stats['total_teachers'] ?? 0"
            color="success"
            change="+3%"
            changeType="increase"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        />
        
        <!-- Total Parents -->
        <x-modern.stat-card
            title="Total Parents"
            :value="$stats['total_parents'] ?? 0"
            color="info"
            change="+8%"
            changeType="increase"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        />
        
        <!-- Total Classes -->
        <x-modern.stat-card
            title="Active Classes"
            :value="$stats['total_classes'] ?? 0"
            color="warning"
            change="0%"
            changeType="neutral"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M12 7h.01M12 11h.01M12 15h.01"/></svg>'
        />
        
        <!-- Total Subjects -->
        <x-modern.stat-card
            title="Total Subjects"
            :value="$stats['total_subjects'] ?? 0"
            color="primary"
            change="+2"
            changeType="increase"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        />
        
        <!-- Upcoming Events -->
        <x-modern.stat-card
            title="Upcoming Events"
            :value="$stats['upcoming_events'] ?? 0"
            color="danger"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        />
    </div>
    
    <!-- Charts and Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Class Performance Chart -->
        <x-modern.card
            title="Class Performance"
            subtitle="Average grades by class"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
        >
            @if(count($classPerformance ?? []) > 0)
            <div class="h-64">
                <canvas id="classPerformanceChart"></canvas>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span class="flex items-center">
                        <span class="w-3 h-3 bg-success-500 rounded-full mr-2"></span>
                        Excellent (≥70%)
                    </span>
                    <span class="flex items-center">
                        <span class="w-3 h-3 bg-warning-500 rounded-full mr-2"></span>
                        Good (50-70%)
                    </span>
                    <span class="flex items-center">
                        <span class="w-3 h-3 bg-danger-500 rounded-full mr-2"></span>
                        Needs Improvement (<50%)
                    </span>
                </div>
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-gray-500">No class performance data available</p>
            </div>
            @endif
        </x-modern.card>

        <!-- Absence Trends Chart -->
        <x-modern.card
            title="Absence Trends"
            subtitle="Last 30 days"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>'
        >
            @if(count($absenceTrends ?? []) > 0)
            <div class="h-64">
                <canvas id="absenceTrendsChart"></canvas>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ collect($absenceTrends)->sum('count') }}</p>
                        <p class="text-xs text-gray-500 uppercase">Total Absences</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-warning-600">{{ number_format(collect($absenceTrends)->avg('count'), 1) }}</p>
                        <p class="text-xs text-gray-500 uppercase">Daily Average</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-success-600">{{ collect($absenceTrends)->max('count') }}</p>
                        <p class="text-xs text-gray-500 uppercase">Peak Day</p>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                <p class="text-gray-500">No absence data available</p>
            </div>
            @endif
        </x-modern.card>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Class Performance List -->
        <x-modern.card
            title="Detailed Performance"
            subtitle="Class rankings"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        >
            <div class="space-y-3">
                @forelse($classPerformance ?? [] as $item)
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all duration-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold">
                            {{ substr($item['class']->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $item['class']->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item['student_count'] }} students enrolled</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold {{ $item['average_grade'] >= 70 ? 'text-success-600' : ($item['average_grade'] >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                            {{ number_format($item['average_grade'], 1) }}%
                        </p>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Avg. Grade</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-gray-500">No class performance data available</p>
                </div>
                @endforelse
            </div>
        </x-modern.card>
        
        <!-- Recent Grades -->
        <x-modern.card
            title="Recent Grades"
            subtitle="Latest student assessments"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>'
            noPadding="true"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentGrades ?? [] as $grade)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $grade->student->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $grade->subject->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $percentage = isset($grade->value) && isset($grade->max_value) ? ($grade->value / $grade->max_value) * 100 : 0;
                                @endphp
                                <x-modern.badge
                                    :type="$percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger')"
                                    size="sm"
                                >
                                    {{ $grade->value ?? 0 }}/{{ $grade->max_value ?? 100 }}
                                </x-modern.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $grade->teacher->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500">No recent grades recorded</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-modern.card>
    </div>
    
    <!-- Recent Absences and Upcoming Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Absences -->
        <x-modern.card
            title="Recent Absences"
            subtitle="Student attendance tracking"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
        >
            <div class="space-y-3">
                @forelse($recentAbsences ?? [] as $absence)
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all duration-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br {{ $absence->is_justified ? 'from-success-500 to-success-600' : 'from-danger-500 to-danger-600' }} rounded-xl flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $absence->is_justified ? 'M5 13l4 4L19 7' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $absence->student->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">
                                <span class="font-medium">{{ $absence->class->name ?? 'N/A' }}</span>
                                • {{ $absence->absence_date ? $absence->absence_date->format('M d, Y') : 'Unknown date' }}
                            </p>
                        </div>
                    </div>
                    <x-modern.badge
                        :type="$absence->is_justified ? 'success' : 'danger'"
                        size="sm"
                        :dot="true"
                    >
                        {{ $absence->is_justified ? 'Justified' : 'Unjustified' }}
                    </x-modern.badge>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500">No recent absences recorded</p>
                </div>
                @endforelse
            </div>
        </x-modern.card>
        
        <!-- Upcoming Events -->
        <x-modern.card
            title="Upcoming Events"
            subtitle="Scheduled activities and meetings"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        >
            <div class="space-y-3">
                @forelse($upcomingEvents ?? [] as $event)
                <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ $event->title ?? 'Untitled Event' }}</p>
                                <div class="mt-1 space-y-1">
                                    <p class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ isset($event->event_date) ? \Carbon\Carbon::parse($event->event_date)->format('M d, Y') : 'Date TBD' }}
                                        @if($event->event_time)
                                            at {{ $event->event_time }}
                                        @endif
                                    </p>
                                    @if($event->location)
                                    <p class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $event->location }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <x-modern.badge
                            type="primary"
                            size="sm"
                        >
                            {{ ucfirst($event->event_type ?? 'General') }}
                        </x-modern.badge>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500">No upcoming events scheduled</p>
                </div>
                @endforelse
            </div>
        </x-modern.card>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-bolt mr-2 text-yellow-500"></i> Quick Actions
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.users.create') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-user-plus text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Add User</span>
            </a>
            <a href="{{ route('admin.classes.create') }}" class="flex flex-col items-center justify-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-plus-circle text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Create Class</span>
            </a>
            <a href="{{ route('admin.subjects.create') }}" class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-book-medical text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Add Subject</span>
            </a>
            <a href="{{ route('admin.events.create') }}" class="flex flex-col items-center justify-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                <i class="fas fa-calendar-plus text-2xl text-red-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Create Event</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Class Performance Chart
    @if(count($classPerformance ?? []) > 0)
    const classCtx = document.getElementById('classPerformanceChart');
    if (classCtx) {
        const classData = @json($classPerformance);
        const classLabels = classData.map(item => item.class.name);
        const classGrades = classData.map(item => item.average_grade);
        const classColors = classGrades.map(grade => {
            if (grade >= 70) return 'rgba(16, 185, 129, 0.8)'; // success
            if (grade >= 50) return 'rgba(245, 158, 11, 0.8)'; // warning
            return 'rgba(239, 68, 68, 0.8)'; // danger
        });

        new Chart(classCtx, {
            type: 'bar',
            data: {
                labels: classLabels,
                datasets: [{
                    label: 'Average Grade (%)',
                    data: classGrades,
                    backgroundColor: classColors,
                    borderColor: classColors.map(color => color.replace('0.8', '1')),
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Average: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    @endif

    // Absence Trends Chart
    @if(count($absenceTrends ?? []) > 0)
    const absenceCtx = document.getElementById('absenceTrendsChart');
    if (absenceCtx) {
        const absenceData = @json($absenceTrends);
        const absenceLabels = absenceData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const absenceCounts = absenceData.map(item => item.count);

        new Chart(absenceCtx, {
            type: 'line',
            data: {
                labels: absenceLabels,
                datasets: [{
                    label: 'Absences',
                    data: absenceCounts,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(59, 130, 246, 1)',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Absences: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
