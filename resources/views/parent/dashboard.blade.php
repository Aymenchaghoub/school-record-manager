@extends('layouts.app')

@section('title', 'Parent Dashboard')
@section('header', 'Parent Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-success-600 to-success-700 rounded-2xl p-8 text-white overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-success-100 text-lg">{{ now()->format('l, F j, Y') }}</p>
            <p class="text-success-200 mt-2">You have {{ count($childrenStats ?? []) }} {{ count($childrenStats ?? []) == 1 ? 'child' : 'children' }} enrolled in our school</p>
        </div>
    </div>

    <!-- Children Overview -->
    @if(count($childrenStats ?? []) > 0)
    <div class="grid grid-cols-1 md:grid-cols-{{ min(count($childrenStats ?? []), 3) }} gap-6">
        @foreach($childrenStats ?? [] as $childData)
        <x-modern.card
            variant="gradient"
            class="relative"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-primary-600 font-bold text-xl shadow-md">
                        {{ strtoupper(substr($childData['child']->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $childData['child']->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $childData['class']->name ?? 'Not Assigned' }}</p>
                        <div class="flex items-center mt-2 space-x-3">
                            <x-modern.badge
                                type="primary"
                                size="sm"
                            >
                                GPA: {{ number_format($childData['overall_average'] ?? 0, 2) }}
                            </x-modern.badge>
                            <x-modern.badge
                                :type="$childData['total_absences'] <= 5 ? 'success' : 'warning'"
                                size="sm"
                            >
                                Absences: {{ $childData['total_absences'] ?? 0 }}
                            </x-modern.badge>
                        </div>
                    </div>
                </div>
                <a href="{{ route('parent.children.show', $childData['child']->id) }}" class="text-primary-600 hover:text-primary-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </x-modern.card>
        @endforeach
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Grades (All Children) -->
        <x-modern.card
            title="Recent Grades"
            subtitle="Latest grades for all your children"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        >
            <div class="space-y-3">
                @php
                    $allRecentGrades = collect($childrenStats ?? [])->flatMap(function($childData) {
                        return $childData['recent_grades'] ?? [];
                    })->sortByDesc('created_at')->take(5);
                @endphp
                @forelse($allRecentGrades as $grade)
                <div class="flex items-center justify-between p-3 border-l-4 {{ $grade->value >= 70 ? 'border-success-500' : ($grade->value >= 50 ? 'border-warning-500' : 'border-danger-500') }} bg-white rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $grade->student->name }}</p>
                        <p class="text-sm text-gray-500">{{ $grade->subject->name }} • {{ $grade->exam_type }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold {{ $grade->value >= 70 ? 'text-success-600' : ($grade->value >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                            {{ $grade->value }}%
                        </p>
                        <p class="text-xs text-gray-500">{{ $grade->created_at->format('M d') }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500">No recent grades</p>
                </div>
                @endforelse
            </div>
        </x-modern.card>

        <!-- Recent Absences (All Children) -->
        <x-modern.card
            title="Recent Attendance"
            subtitle="Attendance records for all your children"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        >
            <div class="space-y-3">
                @php
                    $recentAbsences = \App\Models\Absence::whereIn('student_id', collect($childrenStats ?? [])->pluck('child.id'))
                        ->with(['student', 'class'])
                        ->orderBy('absence_date', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                @forelse($recentAbsences as $absence)
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 {{ $absence->is_justified ? 'bg-success-100' : 'bg-danger-100' }} rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $absence->is_justified ? 'text-success-600' : 'text-danger-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $absence->is_justified ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $absence->student->name }}</p>
                            <p class="text-sm text-gray-500">{{ $absence->class->name }} • {{ $absence->absence_date->format('M d, Y') }}</p>
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
                    <p class="text-gray-500">Perfect attendance for all children!</p>
                </div>
                @endforelse
            </div>
        </x-modern.card>
    </div>

    <!-- Teacher Communication & Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Teacher Messages -->
        <x-modern.card
            title="Teacher Communications"
            subtitle="Recent messages from teachers"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>'
        >
            <div class="space-y-3">
                @forelse($teacherMessages ?? [] as $message)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="font-medium text-gray-900">{{ $message->teacher->name }}</p>
                            <p class="text-xs text-gray-500">{{ $message->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <x-modern.badge
                            type="{{ $message->priority === 'high' ? 'danger' : ($message->priority === 'medium' ? 'warning' : 'info') }}"
                            size="sm"
                        >
                            {{ ucfirst($message->priority ?? 'normal') }}
                        </x-modern.badge>
                    </div>
                    <p class="text-sm text-gray-700">{{ $message->message }}</p>
                    @if($message->child_id)
                    <p class="text-xs text-gray-500 mt-2">Regarding: {{ $message->child->name }}</p>
                    @endif
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No recent messages</p>
                @endforelse
            </div>
        </x-modern.card>

        <!-- Upcoming Events -->
        <x-modern.card
            title="Upcoming Events"
            subtitle="School activities and parent meetings"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        >
            <x-slot name="actions">
                <x-modern.button
                    href="{{ route('parent.events') }}"
                    variant="subtle"
                    size="sm"
                >
                    View All
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

    <!-- Quick Actions -->
    <x-modern.card title="Quick Actions">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('parent.children.index') }}" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl hover:from-primary-100 hover:to-primary-200 transition-all duration-200">
                <svg class="w-8 h-8 text-primary-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">View Children</span>
            </a>
            <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-success-50 to-success-100 rounded-xl hover:from-success-100 hover:to-success-200 transition-all duration-200">
                <svg class="w-8 h-8 text-success-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Report Cards</span>
            </a>
            <a href="#" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-warning-50 to-warning-100 rounded-xl hover:from-warning-100 hover:to-warning-200 transition-all duration-200">
                <svg class="w-8 h-8 text-warning-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Message Teacher</span>
            </a>
            <a href="{{ route('parent.events') }}" class="flex flex-col items-center justify-center p-6 bg-gradient-to-br from-info-50 to-info-100 rounded-xl hover:from-info-100 hover:to-info-200 transition-all duration-200">
                <svg class="w-8 h-8 text-info-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">View Calendar</span>
            </a>
        </div>
    </x-modern.card>
</div>
@endsection
