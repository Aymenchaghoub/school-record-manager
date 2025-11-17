@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, subtle
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false,
    'fullWidth' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-gradient-to-r from-primary-600 to-primary-700 text-white hover:from-primary-700 hover:to-primary-800 focus:ring-primary-500 shadow-lg hover:shadow-xl',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-primary-500',
        'success' => 'bg-gradient-to-r from-success-600 to-success-700 text-white hover:from-success-700 hover:to-success-800 focus:ring-success-500 shadow-lg hover:shadow-xl',
        'danger' => 'bg-gradient-to-r from-danger-600 to-danger-700 text-white hover:from-danger-700 hover:to-danger-800 focus:ring-danger-500 shadow-lg hover:shadow-xl',
        'warning' => 'bg-gradient-to-r from-warning-500 to-warning-600 text-white hover:from-warning-600 hover:to-warning-700 focus:ring-warning-500 shadow-lg hover:shadow-xl',
        'subtle' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500',
        default => 'bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-500',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm rounded-lg',
        'md' => 'px-4 py-2 text-sm rounded-lg',
        'lg' => 'px-6 py-3 text-base rounded-xl',
        default => 'px-4 py-2 text-sm rounded-lg',
    };
    
    $widthClass = $fullWidth ? 'w-full' : '';
    
    $classes = "$baseClasses $variantClasses $sizeClasses $widthClass";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <span class="ml-2">{!! $icon !!}</span>
        @endif
    </a>
@else
    <button 
        type="{{ $type }}" 
        {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled || $loading]) }}
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <span class="ml-2">{!! $icon !!}</span>
        @endif
    </button>
@endif
