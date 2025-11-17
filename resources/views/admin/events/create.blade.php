@extends('layouts.app')

@section('title', 'Create Event')
@section('header', 'Create Event')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Event</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new event to the school calendar</p>
        </div>
        <x-modern.button
            href="{{ route('admin.events.index') }}"
            variant="subtle"
            size="sm"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
        >
            Back to List
        </x-modern.button>
    </div>

    <!-- Form Card -->
    <x-modern.card>
        <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Event Details
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-modern.input
                            label="Event Title"
                            name="title"
                            type="text"
                            :value="old('title')"
                            placeholder="e.g., Annual Sports Day, Parent-Teacher Meeting"
                            :required="true"
                            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>'
                        />
                    </div>
                    
                    <x-modern.select
                        label="Event Type"
                        name="type"
                        :value="old('type')"
                        :required="true"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'
                    >
                        <option value="">Select event type</option>
                        <option value="exam">Examination</option>
                        <option value="meeting">Meeting</option>
                        <option value="holiday">Holiday</option>
                        <option value="sports">Sports Event</option>
                        <option value="cultural">Cultural Event</option>
                        <option value="parent_meeting">Parent Meeting</option>
                        <option value="other">Other</option>
                    </x-modern.select>
                    
                    <x-modern.input
                        label="Location"
                        name="location"
                        type="text"
                        :value="old('location')"
                        placeholder="e.g., Main Hall, Sports Field, Room 101"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                    />
                </div>
            </div>
            
            <!-- Date & Time -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Schedule
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-modern.input
                        label="Event Date"
                        name="event_date"
                        type="date"
                        :value="old('event_date')"
                        :required="true"
                        hint="When will the event take place?"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                    />
                    
                    <x-modern.input
                        label="Start Time"
                        name="start_time"
                        type="time"
                        :value="old('start_time')"
                        hint="Event start time"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    />
                    
                    <x-modern.input
                        label="End Time"
                        name="end_time"
                        type="time"
                        :value="old('end_time')"
                        hint="Event end time (optional)"
                        icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    />
                </div>
            </div>
            
            <!-- Target Audience -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Audience & Visibility
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="target_audience[]" value="all" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ in_array('all', old('target_audience', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Everyone</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="target_audience[]" value="student" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ in_array('student', old('target_audience', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Students</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="target_audience[]" value="teacher" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ in_array('teacher', old('target_audience', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Teachers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="target_audience[]" value="parent" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ in_array('parent', old('target_audience', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Parents</span>
                            </label>
                        </div>
                        @error('target_audience')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <x-modern.select
                            label="Event Color"
                            name="color"
                            :value="old('color', '#3B82F6')"
                            hint="For calendar display"
                            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>'
                        >
                            <option value="#3B82F6">Blue</option>
                            <option value="#10B981">Green</option>
                            <option value="#F59E0B">Yellow</option>
                            <option value="#EF4444">Red</option>
                            <option value="#8B5CF6">Purple</option>
                            <option value="#EC4899">Pink</option>
                        </x-modern.select>
                        
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_published', true) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Publish immediately</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Description
                </h3>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Event Description
                    </label>
                    <textarea 
                        name="description" 
                        id="description"
                        rows="4" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 resize-none @error('description') border-danger-500 @enderror"
                        placeholder="Provide details about the event, agenda, requirements, etc."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <x-modern.button
                    href="{{ route('admin.events.index') }}"
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
                    Create Event
                </x-modern.button>
            </div>
        </form>
    </x-modern.card>
</div>
@endsection
