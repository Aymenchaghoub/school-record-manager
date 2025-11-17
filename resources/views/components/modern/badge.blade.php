@props([
    'type' => 'default', // default, primary, success, warning, danger, info
    'size' => 'md', // sm, md, lg
    'rounded' => 'full', // full, lg, md
    'icon' => null,
    'dot' => false,
])

@php
    $typeClasses = match($type) {
        'primary' => 'bg-primary-100 text-primary-800 border-primary-200',
        'success' => 'bg-success-100 text-success-800 border-success-200',
        'warning' => 'bg-warning-100 text-warning-800 border-warning-200',
        'danger' => 'bg-danger-100 text-danger-800 border-danger-200',
        'info' => 'bg-blue-100 text-blue-800 border-blue-200',
        default => 'bg-gray-100 text-gray-800 border-gray-200',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        'lg' => 'px-4 py-1.5 text-base',
        default => 'px-2.5 py-1 text-sm',
    };
    
    $roundedClasses = match($rounded) {
        'lg' => 'rounded-lg',
        'md' => 'rounded-md',
        default => 'rounded-full',
    };
    
    $dotColor = match($type) {
        'primary' => 'bg-primary-500',
        'success' => 'bg-success-500',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-500',
        'info' => 'bg-blue-500',
        default => 'bg-gray-500',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium border transition-all duration-200 hover:shadow-md $typeClasses $sizeClasses $roundedClasses"]) }}>
    @if($dot)
        <span class="flex-shrink-0 w-2 h-2 {{ $dotColor }} rounded-full mr-1.5 animate-pulse"></span>
    @endif
    
    @if($icon)
        <span class="flex-shrink-0 mr-1.5">
            {!! $icon !!}
        </span>
    @endif
    
    {{ $slot }}
</span>
