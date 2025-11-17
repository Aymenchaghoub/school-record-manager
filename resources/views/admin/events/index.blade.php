@extends('layouts.app')

@section('title', 'Events Management')
@section('header', 'Events Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">Manage school events, holidays, and activities</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-modern.button 
                href="{{ route('admin.events.create') }}"
                variant="primary"
                size="md"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            >
                Create Event
            </x-modern.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="Total Events"
            :value="$events->total() ?? 0"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        />
        <x-modern.stat-card
            title="Upcoming"
            :value="$events->where('start_date', '>=', now()->format('Y-m-d'))->count() ?? 0"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-modern.stat-card
            title="This Month"
            :value="$events->whereBetween('start_date', [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')])->count() ?? 0"
            color="info"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
        />
        <x-modern.stat-card
            title="Published"
            :value="$events->where('is_published', true)->count() ?? 0"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>'
        />
    </div>

    <!-- Events Table -->
    <x-modern.table
        title="All Events"
        :searchable="true"
        :headers="['Event', 'Date & Time', 'Location', 'Type', 'Status', 'Actions']"
    >
        <x-slot name="actions">
            <x-modern.button
                variant="subtle"
                size="sm"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>'
            >
                Filter
            </x-modern.button>
            <x-modern.button
                variant="subtle"
                size="sm"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>'
            >
                Export
            </x-modern.button>
        </x-slot>

        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($events as $event)
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 flex-shrink-0 rounded-xl flex items-center justify-center" style="background-color: {{ $event->color ?? '#3B82F6' }}20;">
                            @php
                                $icons = [
                                    'exam' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                    'meeting' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                                    'holiday' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
                                    'sports' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                                    'cultural' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z',
                                    'parent_meeting' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                                    'other' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
                                ];
                                $icon = $icons[$event->type] ?? $icons['other'];
                            @endphp
                            <svg class="w-5 h-5" fill="none" stroke="{{ $event->color ?? '#3B82F6' }}" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                            @if($event->description)
                            <p class="text-xs text-gray-500">{{ Str::limit($event->description, 50) }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                        <div class="font-medium">{{ \Carbon\Carbon::parse($event->event_date ?? $event->start_date)->format('M d, Y') }}</div>
                        @if($event->event_time)
                        <div class="text-xs text-gray-500">{{ $event->event_time }}</div>
                        @elseif($event->start_date)
                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event->start_date)->format('g:i A') }}</div>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ $event->location ?? 'TBD' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                        $typeColors = [
                            'exam' => 'danger',
                            'meeting' => 'info',
                            'holiday' => 'success',
                            'sports' => 'warning',
                            'cultural' => 'primary',
                            'parent_meeting' => 'secondary',
                            'other' => 'secondary'
                        ];
                    @endphp
                    <x-modern.badge
                        :type="$typeColors[$event->type] ?? 'secondary'"
                        size="sm"
                    >
                        {{ ucfirst(str_replace('_', ' ', $event->type ?? 'event')) }}
                    </x-modern.badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($event->is_published ?? true)
                        <x-modern.badge
                            type="success"
                            size="sm"
                            :dot="true"
                        >
                            Published
                        </x-modern.badge>
                    @else
                        <x-modern.badge
                            type="secondary"
                            size="sm"
                            :dot="true"
                        >
                            Draft
                        </x-modern.badge>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.events.show', $event->id) }}" 
                           class="text-primary-600 hover:text-primary-900 transition-colors duration-200"
                           title="View Details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.events.edit', $event->id) }}" 
                           class="text-warning-600 hover:text-warning-900 transition-colors duration-200"
                           title="Edit Event">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" class="inline-block" 
                              onsubmit="return confirm('Are you sure you want to delete this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-danger-600 hover:text-danger-900 transition-colors duration-200"
                                    title="Delete Event">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No events found</p>
                    <p class="text-gray-400 text-sm mt-1">Get started by creating your first event</p>
                    <div class="mt-4">
                        <x-modern.button 
                            href="{{ route('admin.events.create') }}"
                            variant="primary"
                            size="sm"
                        >
                            Create First Event
                        </x-modern.button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-modern.table>

    <!-- Pagination -->
    @if($events->hasPages())
    <div class="bg-white rounded-xl shadow-soft border border-gray-200 px-4 py-3 sm:px-6">
        {{ $events->links() }}
    </div>
    @endif
</div>
@endsection
