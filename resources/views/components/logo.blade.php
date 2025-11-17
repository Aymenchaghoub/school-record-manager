@props(['size' => 'default', 'textColor' => 'text-gray-900'])

@php
$sizeClasses = [
    'sm' => 'h-8 w-8',
    'default' => 'h-12 w-12',
    'lg' => 'h-16 w-16',
    'xl' => 'h-24 w-24'
];
$textSizes = [
    'sm' => 'text-base',
    'default' => 'text-xl',
    'lg' => 'text-3xl',
    'xl' => 'text-4xl'
];
$logoSize = $sizeClasses[$size] ?? $sizeClasses['default'];
$textSize = $textSizes[$size] ?? $textSizes['default'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center space-x-3']) }}>
    <!-- SchoolSphere Logo SVG -->
    <svg class="{{ $logoSize }}" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Black Diamond Background -->
        <path d="M50 5 L95 50 L50 95 L5 50 Z" fill="#000000"/>
        
        <!-- Blue Wave Top -->
        <path d="M50 5 L95 50 L70 50 Q60 40 50 40 Q40 40 30 50 L5 50 Z" fill="#0066FF"/>
        
        <!-- White Separator -->
        <ellipse cx="50" cy="50" rx="35" ry="8" fill="#FFFFFF"/>
        
        <!-- Blue Wave Bottom -->
        <path d="M5 50 L30 50 Q40 60 50 60 Q60 60 70 50 L95 50 L50 95 Z" fill="#0066FF"/>
    </svg>
    
    <!-- SchoolSphere Text -->
    @if(!isset($noText) || !$noText)
    <span class="{{ $textSize }} font-bold {{ $textColor }}">
        School<span class="text-blue-600">Sphere</span>
    </span>
    @endif
</div>
