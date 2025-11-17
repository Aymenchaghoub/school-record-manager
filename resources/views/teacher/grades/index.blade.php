@extends('layouts.app')

@section('title', 'My Grades')
@section('header', 'Grade Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Grades</h2>
            <p class="mt-1 text-sm text-gray-600">Manage and record student grades for your classes</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-modern.button 
                href="{{ route('teacher.grades.create') }}"
                variant="primary"
                size="md"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
            >
                Record Grades
            </x-modern.button>
        </div>
    </div>

    <!-- Placeholder for grades functionality -->
    <x-modern.placeholder 
        title="Grade Recording System"
        description="This section will allow you to view and manage all grades for your assigned classes and students."
        :showProgress="true"
        :progress="85"
    >
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-primary-600">0</div>
                <div class="text-sm text-gray-600">Grades Recorded</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-success-600">0</div>
                <div class="text-sm text-gray-600">Students Graded</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-info-600">0</div>
                <div class="text-sm text-gray-600">Pending Grades</div>
            </div>
        </div>
    </x-modern.placeholder>
</div>
@endsection
