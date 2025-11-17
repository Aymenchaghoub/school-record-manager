@extends('layouts.app')

@section('title', 'My Report Cards')
@section('header', 'My Report Cards')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Report Cards</h2>
            <p class="mt-1 text-sm text-gray-600">View your academic performance reports</p>
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

    <!-- Report Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($reportCards as $reportCard)
        <x-modern.card>
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    @if($reportCard->is_final)
                    <x-modern.badge type="success" size="sm">Final</x-modern.badge>
                    @else
                    <x-modern.badge type="info" size="sm">{{ $reportCard->term }}</x-modern.badge>
                    @endif
                </div>

                <!-- Details -->
                <div class="space-y-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $reportCard->term }}</h3>
                        <p class="text-sm text-gray-500">{{ $reportCard->academic_year }}</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Overall Average</span>
                            <span class="font-bold text-lg {{ $reportCard->overall_average >= 70 ? 'text-success-600' : ($reportCard->overall_average >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                                {{ number_format($reportCard->overall_average, 1) }}%
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Class Rank</span>
                            <span class="font-semibold text-gray-900">
                                {{ $reportCard->rank_in_class ?? 'N/A' }}
                                @if($reportCard->total_students)
                                <span class="text-gray-500">/ {{ $reportCard->total_students }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Total Absences</span>
                            <span class="font-semibold text-gray-900">{{ $reportCard->total_absences }}</span>
                        </div>
                        @if($reportCard->conduct_grade)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Conduct</span>
                            <x-modern.badge type="{{ $reportCard->conduct_grade == 'Excellent' ? 'success' : 'info' }}" size="sm">
                                {{ $reportCard->conduct_grade }}
                            </x-modern.badge>
                        </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-3">Issued: {{ \Carbon\Carbon::parse($reportCard->issue_date)->format('M d, Y') }}</p>
                        <div class="flex space-x-2">
                            <x-modern.button
                                href="{{ route('student.report-cards.show', $reportCard) }}"
                                variant="primary"
                                size="sm"
                                class="flex-1"
                            >
                                View
                            </x-modern.button>
                            <x-modern.button
                                href="{{ route('student.report-cards.download', $reportCard) }}"
                                variant="subtle"
                                size="sm"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'
                            >
                                PDF
                            </x-modern.button>
                        </div>
                    </div>
                </div>
            </div>
        </x-modern.card>
        @empty
        <div class="col-span-full">
            <x-modern.empty-state
                title="No Report Cards Yet"
                description="Your report cards will appear here once they are issued by your teachers."
                icon='<svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            />
        </div>
        @endforelse
    </div>

    @if($reportCards->hasPages())
    <div class="mt-6">
        {{ $reportCards->links() }}
    </div>
    @endif
</div>
@endsection
