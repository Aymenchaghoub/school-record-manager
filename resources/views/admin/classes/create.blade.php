@extends('layouts.app')

@section('title', 'Create Class')
@section('header', 'Create Class')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Class</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new academic class to the system</p>
        </div>
        <x-modern.button
            href="{{ route('admin.classes.index') }}"
            variant="subtle"
            size="sm"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
        >
            Back to List
        </x-modern.button>
    </div>

    <!-- Form Card -->
    <x-modern.card>
        <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Class Details
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-modern.input
                        label="Class Name"
                        name="name"
                        type="text"
                        :value="old('name')"
                        placeholder="e.g., Class 10A"
                        :required="true"
                        hint="The display name for the class"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>'
                    />
                    
                    <x-modern.input
                        label="Level"
                        name="level"
                        type="text"
                        :value="old('level')"
                        placeholder="e.g., Grade 10"
                        :required="true"
                        hint="The academic level"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>'
                    />
                    
                    <x-modern.input
                        label="Section"
                        name="section"
                        type="text"
                        :value="old('section')"
                        placeholder="e.g., A, B, C"
                        hint="Optional section identifier"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'
                    />
                    
                    <x-modern.input
                        label="Academic Year"
                        name="academic_year"
                        type="text"
                        :value="old('academic_year', '2024-2025')"
                        placeholder="e.g., 2024-2025"
                        :required="true"
                        hint="Current academic year"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                    />
                    
                    <x-modern.input
                        label="Capacity"
                        name="capacity"
                        type="number"
                        :value="old('capacity', 30)"
                        placeholder="30"
                        min="1"
                        hint="Maximum number of students"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
                    />
                    
                    <x-modern.select
                        label="Class Teacher"
                        name="teacher_id"
                        :value="old('teacher_id')"
                        hint="Assign a responsible teacher"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
                    >
                        <option value="">Select Teacher</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->email }})
                            </option>
                        @endforeach
                    </x-modern.select>
                    
                    <x-modern.select
                        label="Status"
                        name="status"
                        :value="old('status', 'active')"
                        hint="Class availability status"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </x-modern.select>
                </div>
            </div>
            
            <!-- Description -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Additional Information
                </h3>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        name="description" 
                        id="description"
                        rows="4" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 resize-none @error('description') border-danger-500 @enderror"
                        placeholder="Enter any additional information about this class..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <x-modern.button
                    href="{{ route('admin.classes.index') }}"
                    variant="subtle"
                    size="md"
                >
                    Cancel
                </x-modern.button>
                
                <x-modern.button
                    type="submit"
                    variant="primary"
                    size="md"
                    icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                >
                    Create Class
                </x-modern.button>
            </div>
        </form>
    </x-modern.card>
</div>
@endsection
