@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'increase', // increase, decrease, neutral
    'icon' => null,
    'color' => 'primary', // primary, success, warning, danger, info
    'gradient' => true,
])

@php
    $colorClasses = match($color) {
        'success' => 'from-success-500 to-success-600',
        'warning' => 'from-warning-500 to-warning-600',
        'danger' => 'from-danger-500 to-danger-600',
        'info' => 'from-blue-500 to-blue-600',
        default => 'from-primary-500 to-primary-600',
    };
    
    $changeIcon = match($changeType) {
        'increase' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>',
        'decrease' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>',
        default => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"></path></svg>',
    };
    
    $changeColor = match($changeType) {
        'increase' => 'text-success-600 bg-success-100',
        'decrease' => 'text-danger-600 bg-danger-100',
        default => 'text-gray-600 bg-gray-100',
    };
@endphp

<div {{ $attributes->merge(['class' => 'relative bg-white rounded-2xl shadow-soft p-6 overflow-hidden transition-all duration-300 hover:shadow-xl hover:scale-105 group']) }}>
    <!-- Background Decoration -->
    <div class="absolute -right-4 -top-4 w-24 h-24 bg-gradient-to-br {{ $colorClasses }} opacity-10 rounded-full blur-2xl group-hover:w-32 group-hover:h-32 transition-all duration-500"></div>
    
    <div class="relative">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
                <p class="text-3xl font-bold text-gray-900 mb-2">{{ $value }}</p>
                
                @if($change !== null)
                    <div class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg {{ $changeColor }}">
                        {!! $changeIcon !!}
                        <span class="text-sm font-medium">{{ $change }}</span>
                    </div>
                @endif
            </div>
            
            @if($icon)
                <div class="flex-shrink-0 ml-4">
                    <div class="p-3 bg-gradient-to-br {{ $gradient ? $colorClasses : 'from-gray-100 to-gray-200' }} rounded-xl text-white">
                        {!! $icon !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Animated border on hover -->
    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r {{ $colorClasses }} transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
</div>
