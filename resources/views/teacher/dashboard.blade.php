@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('header', 'Teacher Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-warning-600 to-warning-700 rounded-2xl p-8 text-white overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-warning-100 text-lg">{{ now()->format('l, F j, Y') }}</p>
            <p class="text-warning-200 mt-2">You have {{ $stats['total_classes'] ?? 0 }} classes and {{ $stats['total_students'] ?? 0 }} students this semester</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="My Classes"
            :value="$stats['total_classes'] ?? 0"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M12 7h.01M12 11h.01M12 15h.01"/></svg>'
        />
        <x-modern.stat-card
            title="Total Students"
            :value="$stats['total_students'] ?? 0"
            color="info"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>'
        />
        <x-modern.stat-card
            title="Grades This Week"
            :value="$stats['grades_this_week'] ?? 0"
            color="success"
            change="+{{ $stats['grades_change'] ?? '0' }}%"
            changeType="increase"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        />
        <x-modern.stat-card
            title="Absences Today"
            :value="$stats['absences_today'] ?? 0"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Classes -->
        <x-modern.card
            title="My Classes"
            subtitle="Classes you're teaching this semester"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M12 7h.01M12 11h.01M12 15h.01"/></svg>'
        >
            <x-slot name="actions">
                <x-modern.button
                    href="{{ route('teacher.classes') }}"
                    variant="subtle"
                    size="sm"
                >
                    View All
                </x-modern.button>
            </x-slot>
            
            <div class="space-y-3">
                @forelse($myClasses ?? [] as $class)
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all duration-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold">
                            {{ substr($class->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $class->name }}</p>
                            <p class="text-sm text-gray-500">{{ $class->students->count() ?? 0 }} students • Room {{ $class->pivot->room ?? 'TBD' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-2 py-1 bg-primary-100 text-primary-700 rounded-full font-medium">
                            Level {{ $class->level ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No classes assigned yet</p>
                @endforelse
            </div>
        </x-modern.card>

        <!-- Recent Grades -->
        <x-modern.card
            title="Recent Grades"
            subtitle="Latest grades you've entered"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>'
        >
            <x-slot name="actions">
                <x-modern.button
                    href="{{ route('teacher.grades.index') }}"
                    variant="subtle"
                    size="sm"
                >
                    View All
                </x-modern.button>
            </x-slot>
            
            <div class="space-y-3">
                @forelse($recentGrades ?? [] as $grade)
                <div class="flex items-center justify-between p-3 border-l-4 {{ $grade->value >= 70 ? 'border-success-500' : ($grade->value >= 50 ? 'border-warning-500' : 'border-danger-500') }} bg-white rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $grade->student->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">{{ $grade->subject->name ?? 'Unknown' }} • {{ $grade->exam_type }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold {{ $grade->value >= 70 ? 'text-success-600' : ($grade->value >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                            {{ $grade->value }}%
                        </p>
                        <p class="text-xs text-gray-500">{{ $grade->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No recent grades</p>
                @endforelse
            </div>
        </x-modern.card>
    </div>

    <!-- Today's Schedule & Absences -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Schedule -->
        <x-modern.card
            title="Today's Schedule"
            subtitle="Your classes for {{ now()->format('l, F j') }}"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        >
            <div class="space-y-3">
                @forelse($todaySchedule ?? [] as $schedule)
                <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="w-16 text-center">
                        <p class="text-sm font-semibold text-gray-900">{{ $schedule->start_time }}</p>
                        <p class="text-xs text-gray-500">{{ $schedule->end_time }}</p>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $schedule->class->name }}</p>
                        <p class="text-sm text-gray-500">{{ $schedule->subject->name }} • Room {{ $schedule->room }}</p>
                    </div>
                    <x-modern.badge
                        :type="$schedule->isPast() ? 'secondary' : ($schedule->isNow() ? 'success' : 'primary')"
                        size="sm"
                    >
                        {{ $schedule->isPast() ? 'Completed' : ($schedule->isNow() ? 'In Progress' : 'Upcoming') }}
                    </x-modern.badge>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No classes scheduled for today</p>
                @endforelse
            </div>
        </x-modern.card>

        <!-- Recent Absences -->
        <x-modern.card
            title="Recent Absences"
            subtitle="Student absences in your classes"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
        >
            <x-slot name="actions">
                <x-modern.button
                    href="{{ route('teacher.absences.index') }}"
                    variant="subtle"
                    size="sm"
                >
                    View All
                </x-modern.button>
            </x-slot>
            
            <div class="space-y-3">
                @forelse($recentAbsences ?? [] as $absence)
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $absence->student->name }}</p>
                        <p class="text-sm text-gray-500">{{ $absence->class->name }} • {{ $absence->absence_date->format('M d, Y') }}</p>
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
                <p class="text-center text-gray-500 py-8">No recent absences</p>
                @endforelse
            </div>
        </x-modern.card>
    </div>

    <!-- Quick Actions -->
    <x-modern.card title="Quick Actions">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('teacher.grades.create') }}" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl hover:from-primary-100 hover:to-primary-200 transition-all duration-200">
                <svg class="w-8 h-8 text-primary-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Enter Grades</span>
            </a>
            <a href="{{ route('teacher.absences.create') }}" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-warning-50 to-warning-100 rounded-xl hover:from-warning-100 hover:to-warning-200 transition-all duration-200">
                <svg class="w-8 h-8 text-warning-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Mark Attendance</span>
            </a>
            <a href="{{ route('teacher.classes') }}" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-info-50 to-info-100 rounded-xl hover:from-info-100 hover:to-info-200 transition-all duration-200">
                <svg class="w-8 h-8 text-info-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">View Classes</span>
            </a>
            <a href="{{ route('teacher.absences.index') }}" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-success-50 to-success-100 rounded-xl hover:from-success-100 hover:to-success-200 transition-all duration-200">
                <svg class="w-8 h-8 text-success-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">View Absences</span>
            </a>
        </div>
    </x-modern.card>
</div>
@endsection
