@extends('layouts.app')

@section('title', 'My Grades')
@section('header', 'My Grades')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Grades</h2>
            <p class="mt-1 text-sm text-gray-600">View all your academic performance records</p>
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="Average Grade"
            :value="number_format($statistics['average'] ?? 0, 1) . '%'"
            color="{{ ($statistics['average'] ?? 0) >= 70 ? 'success' : (($statistics['average'] ?? 0) >= 50 ? 'warning' : 'danger') }}"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
        />
        <x-modern.stat-card
            title="Highest Grade"
            :value="number_format($statistics['highest'] ?? 0, 1) . '%'"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
        />
        <x-modern.stat-card
            title="Lowest Grade"
            :value="number_format($statistics['lowest'] ?? 0, 1) . '%'"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>'
        />
        <x-modern.stat-card
            title="Total Subjects"
            :value="$statistics['total_subjects'] ?? 0"
            color="info"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        />
    </div>

    <!-- Grades Table -->
    <x-modern.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($grades as $grade)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                    {{ substr($grade->subject->name ?? 'N/A', 0, 2) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $grade->subject->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $grade->subject->code ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-modern.badge type="info" size="sm">
                                {{ ucfirst($grade->type ?? 'Exam') }}
                            </x-modern.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-lg font-bold {{ $grade->value >= 90 ? 'text-success-600' : ($grade->value >= 70 ? 'text-info-600' : ($grade->value >= 50 ? 'text-warning-600' : 'text-danger-600')) }}">
                                    {{ $grade->value }}%
                                </span>
                                <x-modern.badge
                                    :type="$grade->value >= 90 ? 'success' : ($grade->value >= 70 ? 'info' : ($grade->value >= 50 ? 'warning' : 'danger'))"
                                    size="sm"
                                    class="ml-2"
                                >
                                    {{ $grade->value >= 90 ? 'A' : ($grade->value >= 80 ? 'B' : ($grade->value >= 70 ? 'C' : ($grade->value >= 60 ? 'D' : 'F'))) }}
                                </x-modern.badge>
                            </div>
                            @if($grade->comment)
                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($grade->comment, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $grade->class->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $grade->teacher->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($grade->grade_date)->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12">
                            <x-modern.empty-state
                                title="No grades yet"
                                description="Your grades will appear here once your teachers record them."
                                icon='<svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($grades->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $grades->links() }}
        </div>
        @endif
    </x-modern.card>
</div>
@endsection
