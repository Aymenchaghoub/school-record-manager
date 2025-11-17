@extends('layouts.app')

@section('title', 'Absences Management')
@section('header', 'Absences Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">Track and manage student attendance records</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-modern.button 
                variant="primary"
                size="md"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
            >
                Mark Attendance
            </x-modern.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="Total Absences"
            :value="$absences->total() ?? 0"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
        />
        <x-modern.stat-card
            title="Today's Absences"
            :value="$absences->where('absence_date', today())->count() ?? 0"
            color="warning"
            change="+{{ $absences->where('absence_date', today())->count() }}"
            changeType="increase"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-modern.stat-card
            title="Justified"
            :value="$absences->where('is_justified', true)->count() ?? 0"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-modern.stat-card
            title="Unjustified"
            :value="$absences->where('is_justified', false)->count() ?? 0"
            color="danger"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    <!-- Absences Table -->
    <x-modern.table
        title="All Absence Records"
        :searchable="true"
        :headers="['Student', 'Class', 'Date', 'Period', 'Status', 'Reason', 'Marked By', 'Actions']"
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
            @forelse($absences as $absence)
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-info-500 to-info-600 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($absence->student->name ?? 'N/A', 0, 1)) }}
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $absence->student->name ?? 'Unknown Student' }}</div>
                            <div class="text-sm text-gray-500">ID: #{{ $absence->student_id }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $absence->class->name ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ $absence->class->code ?? '' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($absence->absence_date)->format('M d, Y') }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($absence->absence_date)->format('l') }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <x-modern.badge
                        type="info"
                        size="sm"
                    >
                        {{ $absence->period ?? 'Full Day' }}
                    </x-modern.badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <x-modern.badge
                        :type="$absence->is_justified ? 'success' : 'danger'"
                        size="sm"
                        :dot="true"
                    >
                        {{ $absence->is_justified ? 'Justified' : 'Unjustified' }}
                    </x-modern.badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        {{ Str::limit($absence->reason ?? 'No reason provided', 30) }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $absence->markedBy->name ?? 'System' }}</div>
                    <div class="text-xs text-gray-500">{{ $absence->created_at->diffForHumans() }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        @if(!$absence->is_justified)
                        <button
                            class="text-success-600 hover:text-success-900 transition-colors duration-200"
                            title="Justify Absence">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        @endif
                        <button
                            class="text-warning-600 hover:text-warning-900 transition-colors duration-200"
                            title="Edit Absence">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button
                            class="text-danger-600 hover:text-danger-900 transition-colors duration-200"
                            title="Delete Absence"
                            onclick="return confirm('Are you sure you want to delete this absence record?')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No absence records found</p>
                    <p class="text-gray-400 text-sm mt-1">All students have perfect attendance!</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-modern.table>

    <!-- Pagination -->
    @if($absences->hasPages())
    <div class="bg-white rounded-xl shadow-soft border border-gray-200 px-4 py-3 sm:px-6">
        {{ $absences->links() }}
    </div>
    @endif
</div>
@endsection
