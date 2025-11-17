@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('header', 'Student Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-info-600 to-info-700 rounded-2xl p-8 text-white overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-info-100 text-lg">{{ now()->format('l, F j, Y') }}</p>
            @if($currentClass ?? null)
            <p class="text-info-200 mt-2">Class: {{ $currentClass->name }} • Academic Year {{ date('Y') }}/{{ date('Y') + 1 }}</p>
            @endif
        </div>
    </div>

    <!-- Academic Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="Overall GPA"
            :value="number_format($stats['gpa'] ?? 0, 2)"
            color="primary"
            change="{{ $stats['gpa_change'] ?? '+0.0' }}"
            changeType="{{ $stats['gpa_trend'] ?? 'neutral' }}"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>'
        />
        <x-modern.stat-card
            title="Total Subjects"
            :value="$stats['total_subjects'] ?? 0"
            color="info"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        />
        <x-modern.stat-card
            title="Attendance Rate"
            :value="($stats['attendance_rate'] ?? 0) . '%'"
            color="{{ ($stats['attendance_rate'] ?? 0) >= 90 ? 'success' : 'warning' }}"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
        />
        <x-modern.stat-card
            title="Class Rank"
            :value="'#' . ($stats['class_rank'] ?? 'N/A')"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Grades -->
        <div class="lg:col-span-2">
            <x-modern.card
                title="Recent Grades"
                subtitle="Your latest academic performance"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            >
                <x-slot name="actions">
                    <x-modern.button
                        href="{{ route('student.grades.index') }}"
                        variant="subtle"
                        size="sm"
                    >
                        View All Grades
                    </x-modern.button>
                </x-slot>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentGrades ?? [] as $grade)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center text-white text-xs">
                                            {{ substr($grade->subject->name ?? 'N/A', 0, 2) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $grade->subject->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-gray-500">{{ $grade->teacher->name ?? 'Unknown' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $grade->exam_type ?? 'Quiz' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $percentage = $grade->value ?? 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <span class="text-sm font-semibold {{ $percentage >= 70 ? 'text-success-600' : ($percentage >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                                            {{ $percentage }}%
                                        </span>
                                        <x-modern.badge
                                            :type="$percentage >= 90 ? 'success' : ($percentage >= 70 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger'))"
                                            size="sm"
                                            class="ml-2"
                                        >
                                            {{ $percentage >= 90 ? 'A' : ($percentage >= 80 ? 'B' : ($percentage >= 70 ? 'C' : ($percentage >= 60 ? 'D' : 'F'))) }}
                                        </x-modern.badge>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $grade->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p>No grades recorded yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-modern.card>
        </div>

        <!-- Subject Performance -->
        <div>
            <x-modern.card
                title="Subject Performance"
                subtitle="Average by subject"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
            >
                <div class="space-y-3">
                    @forelse($subjectPerformance ?? [] as $subjectData)
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">{{ $subjectData['subject']->name ?? 'Unknown' }}</span>
                            <span class="text-sm font-semibold {{ $subjectData['average'] >= 70 ? 'text-success-600' : ($subjectData['average'] >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                                {{ number_format($subjectData['average'], 1) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $subjectData['average'] >= 70 ? 'bg-success-500' : ($subjectData['average'] >= 50 ? 'bg-warning-500' : 'bg-danger-500') }} h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $subjectData['average'] }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">No subjects enrolled</p>
                    @endforelse
                </div>
            </x-modern.card>
        </div>
    </div>

    <!-- Attendance & Schedule -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Attendance -->
        <x-modern.card
            title="Recent Attendance"
            subtitle="Your attendance records"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        >
            <x-slot name="actions">
                <x-modern.button
                    href="{{ route('student.absences') }}"
                    variant="subtle"
                    size="sm"
                >
                    View All
                </x-modern.button>
            </x-slot>
            
            <div class="space-y-3">
                @forelse($recentAbsences ?? [] as $absence)
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 {{ $absence->is_justified ? 'bg-success-100' : 'bg-danger-100' }} rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $absence->is_justified ? 'text-success-600' : 'text-danger-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $absence->is_justified ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $absence->class->name ?? 'Unknown Class' }}</p>
                            <p class="text-sm text-gray-500">{{ $absence->absence_date->format('M d, Y') }} • {{ $absence->period ?? 'All Day' }}</p>
                        </div>
                    </div>
                    <x-modern.badge
                        :type="$absence->is_justified ? 'success' : 'danger'"
                        size="sm"
                    >
                        {{ $absence->is_justified ? 'Justified' : 'Unjustified' }}
                    </x-modern.badge>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500">Perfect attendance! Keep it up!</p>
                </div>
                @endforelse
            </div>
        </x-modern.card>

        <!-- Upcoming Events -->
        <x-modern.card
            title="Upcoming Events"
            subtitle="School activities and important dates"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        >
            <x-slot name="actions">
                <x-modern.button
                    href="{{ route('student.events') }}"
                    variant="subtle"
                    size="sm"
                >
                    View Calendar
                </x-modern.button>
            </x-slot>
            
            <div class="space-y-3">
                @forelse($upcomingEvents ?? [] as $event)
                <div class="p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                {{ \Carbon\Carbon::parse($event->start_date)->format('d') }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($event->start_date)->format('l, M d') }} at {{ $event->start_time ?? 'TBD' }}</p>
                                @if($event->location)
                                <p class="text-xs text-gray-500 mt-1">
                                    <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $event->location }}
                                </p>
                                @endif
                            </div>
                        </div>
                        <x-modern.badge
                            type="primary"
                            size="sm"
                        >
                            {{ ucfirst($event->type ?? 'Event') }}
                        </x-modern.badge>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No upcoming events</p>
                @endforelse
            </div>
        </x-modern.card>
    </div>
</div>
@endsection
