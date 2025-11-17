@extends('layouts.app')

@section('title', 'My Absences')
@section('header', 'My Absences')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Absences</h2>
            <p class="mt-1 text-sm text-gray-600">View your attendance records</p>
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

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-modern.stat-card
            title="Total Absences"
            :value="$statistics['total_absences'] ?? 0"
            color="{{ ($statistics['total_absences'] ?? 0) > 10 ? 'danger' : 'info' }}"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
        />
        <x-modern.stat-card
            title="Justified"
            :value="$statistics['justified'] ?? 0"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-modern.stat-card
            title="Unjustified"
            :value="$statistics['unjustified'] ?? 0"
            color="{{ ($statistics['unjustified'] ?? 0) > 5 ? 'danger' : 'warning' }}"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    <!-- Absences Table -->
    <x-modern.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($absences as $absence)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 {{ $absence->is_justified ? 'bg-success-100' : 'bg-danger-100' }} rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 {{ $absence->is_justified ? 'text-success-600' : 'text-danger-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $absence->is_justified ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($absence->absence_date)->format('l, M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($absence->absence_date)->diffForHumans() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $absence->class->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $absence->period ?? 'All Day' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-modern.badge
                                :type="$absence->is_justified ? 'success' : 'danger'"
                                size="sm"
                            >
                                {{ $absence->is_justified ? 'Justified' : 'Unjustified' }}
                            </x-modern.badge>
                        </td>
                        <td class="px-6 py-4">
                            @if($absence->reason)
                            <div class="text-sm text-gray-900">{{ $absence->reason }}</div>
                            @else
                            <span class="text-sm text-gray-400 italic">No reason provided</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12">
                            <x-modern.empty-state
                                title="Perfect attendance!"
                                description="You have no recorded absences. Keep up the great work!"
                                icon='<svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($absences->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $absences->links() }}
        </div>
        @endif
    </x-modern.card>
</div>
@endsection
