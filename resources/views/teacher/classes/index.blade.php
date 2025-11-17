@extends('layouts.app')

@section('title', 'My Classes')
@section('header', 'My Classes')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Classes</h2>
            <p class="mt-1 text-sm text-gray-600">Classes you're teaching this semester</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-modern.button
                href="{{ route('teacher.dashboard') }}"
                variant="subtle"
                size="sm"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
            >
                Back to Dashboard
            </x-modern.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-modern.stat-card
            title="Total Classes"
            :value="$classes->count()"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
        />
        <x-modern.stat-card
            title="Total Students"
            :value="$classes->sum('students_count')"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        />
        <x-modern.stat-card
            title="Subjects Teaching"
            :value="$classes->flatMap(function($class) { return $class->subjects; })->unique('id')->count()"
            color="info"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        />
    </div>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classes as $class)
        <x-modern.card>
            <div class="p-6">
                <!-- Class Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($class->name, 0, 2) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h3>
                            <p class="text-sm text-gray-500">Level {{ $class->level }}</p>
                        </div>
                    </div>
                </div>

                <!-- Class Stats -->
                <div class="space-y-3 mb-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Students</span>
                        <span class="font-semibold text-gray-900">{{ $class->students_count }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Subjects Teaching</span>
                        <span class="font-semibold text-gray-900">{{ $class->subjects->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Academic Year</span>
                        <span class="font-semibold text-gray-900">{{ $class->academic_year ?? '2024-2025' }}</span>
                    </div>
                </div>

                <!-- Subjects List -->
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Your Subjects</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($class->subjects as $subject)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                            {{ $subject->name }}
                        </span>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
                    <x-modern.button
                        href="{{ route('teacher.grades.by-class', $class) }}"
                        variant="primary"
                        size="sm"
                        class="flex-1"
                    >
                        Grades
                    </x-modern.button>
                    <x-modern.button
                        href="{{ route('teacher.absences.by-class', $class) }}"
                        variant="subtle"
                        size="sm"
                        class="flex-1"
                    >
                        Absences
                    </x-modern.button>
                </div>
            </div>
        </x-modern.card>
        @empty
        <div class="col-span-full">
            <x-modern.empty-state
                title="No classes assigned"
                description="You don't have any classes assigned yet. Please contact the administrator."
                icon='<svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
            />
        </div>
        @endforelse
    </div>
</div>
@endsection
