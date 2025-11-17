@extends('layouts.app')

@section('title', 'Subjects Management')
@section('header', 'Subjects Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">Manage all subjects in the school system</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-modern.button 
                href="{{ route('admin.subjects.create') }}"
                variant="primary"
                size="md"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            >
                Add New Subject
            </x-modern.button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-modern.stat-card
            title="Total Subjects"
            :value="$subjects->total() ?? 0"
            color="primary"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
        />
        <x-modern.stat-card
            title="Active Subjects"
            :value="$subjects->where('is_active', true)->count() ?? 0"
            color="success"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-modern.stat-card
            title="With Teachers"
            :value="$subjects->whereNotNull('teacher_id')->count() ?? 0"
            color="warning"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        />
        <x-modern.stat-card
            title="Inactive"
            :value="$subjects->where('is_active', false)->count() ?? 0"
            color="danger"
            icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
        />
    </div>

    <!-- Subjects Table -->
    <x-modern.table
        title="All Subjects"
        :searchable="true"
        :headers="['Code', 'Name', 'Teacher', 'Credits', 'Status', 'Actions']"
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
            @forelse($subjects as $subject)
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $subject->code }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $subject->name }}</p>
                        @if($subject->description)
                        <p class="text-xs text-gray-500">{{ Str::limit($subject->description, 50) }}</p>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($subject->teacher)
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-2">
                            {{ substr($subject->teacher->name, 0, 1) }}
                        </div>
                        <span class="text-sm text-gray-700">{{ $subject->teacher->name }}</span>
                    </div>
                    @elseif($subject->assignedTeachers && $subject->assignedTeachers->count() > 0)
                    <div class="text-sm text-gray-700">
                        {{ $subject->assignedTeachers->pluck('name')->take(2)->join(', ') }}
                        @if($subject->assignedTeachers->count() > 2)
                        <span class="text-xs text-gray-500">+{{ $subject->assignedTeachers->count() - 2 }} more</span>
                        @endif
                    </div>
                    @else
                    <span class="text-sm text-gray-500 italic">Not Assigned</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ $subject->credits ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <x-modern.badge
                        :type="$subject->is_active ? 'success' : 'danger'"
                        size="sm"
                        :dot="true"
                    >
                        {{ $subject->is_active ? 'Active' : 'Inactive' }}
                    </x-modern.badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.subjects.show', $subject->id) }}" 
                           class="text-primary-600 hover:text-primary-900 transition-colors duration-200"
                           title="View Details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.subjects.edit', $subject->id) }}" 
                           class="text-warning-600 hover:text-warning-900 transition-colors duration-200"
                           title="Edit Subject">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="inline-block" 
                              onsubmit="return confirm('Are you sure you want to delete this subject?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-danger-600 hover:text-danger-900 transition-colors duration-200"
                                    title="Delete Subject">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No subjects found</p>
                    <p class="text-gray-400 text-sm mt-1">Get started by creating a new subject</p>
                    <div class="mt-4">
                        <x-modern.button 
                            href="{{ route('admin.subjects.create') }}"
                            variant="primary"
                            size="sm"
                        >
                            Create First Subject
                        </x-modern.button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-modern.table>

    <!-- Pagination -->
    @if($subjects->hasPages())
    <div class="bg-white rounded-xl shadow-soft border border-gray-200 px-4 py-3 sm:px-6">
        {{ $subjects->links() }}
    </div>
    @endif
</div>
@endsection
