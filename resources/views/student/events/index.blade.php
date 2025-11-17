@extends('layouts.app')

@section('title', 'Events')
@section('header', 'Events')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">School Events</h2>
            <p class="mt-1 text-sm text-gray-600">View upcoming and past school events</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-modern.button
                href="{{ route('student.dashboard') }}"
                variant="subtle"
                size="sm"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
            >
                Back to Dashboard
            </x-modern.button>
        </div>
    </div>

    <!-- Events Timeline -->
    <div class="space-y-4">
        @forelse($events as $event)
        <x-modern.card>
            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <!-- Date Badge -->
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex flex-col items-center justify-center text-white">
                            <span class="text-2xl font-bold">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</span>
                            <span class="text-xs uppercase">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</span>
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $event->title }}</h3>
                                <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('l, F j, Y') }}
                                    </span>
                                    @if($event->start_time)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $event->start_time }}
                                    </span>
                                    @endif
                                    @if($event->location)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $event->location }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <x-modern.badge
                                :type="$event->type == 'exam' ? 'danger' : ($event->type == 'assignment' ? 'warning' : 'info')"
                                size="sm"
                            >
                                {{ ucfirst($event->type) }}
                            </x-modern.badge>
                        </div>

                        @if($event->description)
                        <p class="mt-3 text-sm text-gray-600">{{ $event->description }}</p>
                        @endif

                        @if($event->end_date && $event->start_date != $event->end_date)
                        <div class="mt-3 text-xs text-gray-500">
                            Duration: {{ \Carbon\Carbon::parse($event->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-modern.card>
        @empty
        <x-modern.empty-state
            title="No Events Scheduled"
            description="There are no upcoming events at this time. Check back later for updates."
            icon='<svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        />
        @endforelse
    </div>

    @if($events->hasPages())
    <div class="mt-6">
        {{ $events->links() }}
    </div>
    @endif
</div>
@endsection
