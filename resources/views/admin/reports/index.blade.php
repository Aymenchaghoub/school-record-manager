@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('header', 'Reports & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Reports & Analytics</h2>
            <p class="mt-1 text-sm text-gray-600">Comprehensive insights and academic performance metrics</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <x-modern.button 
                variant="subtle"
                size="md"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'
            >
                Export Report
            </x-modern.button>
            <x-modern.button 
                variant="primary"
                size="md"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            >
                Generate Report
            </x-modern.button>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="Total Users"
            :value="$statistics['total_users'] ?? 0"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        />
        <x-modern.stat-card
            title="Total Students"
            :value="$statistics['total_students'] ?? 0"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7"/></svg>'
        />
        <x-modern.stat-card
            title="Total Teachers"
            :value="$statistics['total_teachers'] ?? 0"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
        />
        <x-modern.stat-card
            title="Total Classes"
            :value="$statistics['total_classes'] ?? 0"
            color="info"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
        />
    </div>

    <!-- Analytics Dashboard Placeholder -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Analytics -->
        <x-modern.placeholder 
            title="Performance Analytics"
            description="Advanced charts and graphs showing academic performance trends across all classes and subjects."
            :showProgress="true"
            :progress="75"
        />
        
        <!-- Attendance Analytics -->
        <x-modern.placeholder 
            title="Attendance Analytics"
            description="Detailed attendance patterns, absence trends, and predictive analytics for student attendance."
            :showProgress="true"
            :progress="60"
        />
    </div>

    <!-- Report Cards Section -->
    <x-modern.card
        title="Recent Report Cards"
        subtitle="Latest generated academic reports"
        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
    >
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reportCards ?? [] as $reportCard)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($reportCard->student->name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $reportCard->student->name ?? 'Unknown' }}</div>
                                <div class="text-sm text-gray-500">ID: #{{ $reportCard->student_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $reportCard->class->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">Semester {{ $reportCard->semester }}</div>
                        <div class="text-sm text-gray-500">{{ $reportCard->academic_year }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-lg font-semibold {{ $reportCard->average_grade >= 70 ? 'text-success-600' : ($reportCard->average_grade >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                                {{ number_format($reportCard->average_grade, 1) }}%
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-modern.badge
                            :type="$reportCard->status == 'published' ? 'success' : 'warning'"
                            size="sm"
                        >
                            {{ ucfirst($reportCard->status) }}
                        </x-modern.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="#" class="text-primary-600 hover:text-primary-900">View</a>
                            <span class="text-gray-300">|</span>
                            <a href="#" class="text-primary-600 hover:text-primary-900">Download</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12">
                        <x-modern.empty-state
                            title="No report cards found"
                            description="Report cards will appear here once they are generated for students."
                            icon='<svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                        />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-modern.card>
</div>
@endsection
