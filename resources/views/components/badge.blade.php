@props([
    'type' => 'default',
    'size' => 'md',
    'icon' => null,
    'rounded' => false,
])

@php
$types = [
    'default' => 'bg-gray-100 text-gray-800',
    'primary' => 'bg-primary-100 text-primary-800',
    'secondary' => 'bg-secondary-100 text-secondary-800',
    'success' => 'bg-success-100 text-success-800',
    'danger' => 'bg-danger-100 text-danger-800',
    'warning' => 'bg-warning-100 text-warning-800',
    'info' => 'bg-blue-100 text-blue-800',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base',
];

$typeClass = $types[$type] ?? $types['default'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
$roundedClass = $rounded ? 'rounded-full' : 'rounded-md';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium $typeClass $sizeClass $roundedClass"]) }}>
    @if($icon)
        <i class="{{ $icon }} mr-1.5"></i>
    @endif
    {{ $slot }}
</span>
