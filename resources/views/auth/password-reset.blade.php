@extends('layouts.app')

@section('title', 'Change Password')
@section('header', 'Change Password')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="text-center">
        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-warning-500 to-warning-600 rounded-2xl flex items-center justify-center shadow-lg mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Change Your Password</h2>
        <p class="mt-2 text-sm text-gray-600">Update your password to keep your account secure</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <x-modern.alert type="success" dismissible="true">
            {{ session('success') }}
        </x-modern.alert>
    @endif

    @if(session('error'))
        <x-modern.alert type="danger" dismissible="true">
            {{ session('error') }}
        </x-modern.alert>
    @endif

    <!-- Password Reset Form -->
    <x-modern.card>
        <form method="POST" action="{{ route('password.reset') }}" class="space-y-6">
            @csrf

            <!-- Security Notice -->
            <div class="bg-info-50 border border-info-200 rounded-xl p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-info-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-info-700">
                        <p class="font-medium mb-1">Password Requirements:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-info-600">
                            <li>At least 8 characters long</li>
                            <li>Include uppercase and lowercase letters</li>
                            <li>Include at least one number</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Current Password -->
            <x-modern.input
                label="Current Password"
                name="current_password"
                type="password"
                placeholder="Enter your current password"
                :required="true"
                hint="Verify your identity"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>'
            />

            <!-- New Password -->
            <x-modern.input
                label="New Password"
                name="password"
                type="password"
                placeholder="Enter your new password"
                :required="true"
                hint="Choose a strong password"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>'
            />

            <!-- Confirm New Password -->
            <x-modern.input
                label="Confirm New Password"
                name="password_confirmation"
                type="password"
                placeholder="Re-enter your new password"
                :required="true"
                hint="Must match new password"
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <x-modern.button
                    href="{{ route('dashboard') }}"
                    variant="subtle"
                    size="md"
                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
                >
                    Cancel
                </x-modern.button>
                
                <x-modern.button
                    type="submit"
                    variant="warning"
                    size="md"
                    icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                >
                    Update Password
                </x-modern.button>
            </div>
        </form>
    </x-modern.card>
</div>
@endsection
