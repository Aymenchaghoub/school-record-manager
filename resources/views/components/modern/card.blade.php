@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'actions' => null,
    'noPadding' => false,
    'variant' => 'default', // default, gradient, glass
])

@php
    $variantClasses = match($variant) {
        'gradient' => 'bg-gradient-to-br from-white to-gray-50 border border-gray-100',
        'glass' => 'bg-white/80 backdrop-blur-lg border border-gray-200/50',
        default => 'bg-white border border-gray-200',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl shadow-soft overflow-hidden transition-all duration-300 hover:shadow-xl $variantClasses"]) }}>
    @if($title || $subtitle || $icon || $actions)
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if($icon)
                        <div class="flex-shrink-0">
                            <div class="p-2 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl text-white">
                                {!! $icon !!}
                            </div>
                        </div>
                    @endif
                    <div>
                        @if($title)
                            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @if($actions)
                    <div class="flex items-center space-x-2">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="{{ $noPadding ? '' : 'p-6' }}">
        {{ $slot }}
    </div>
</div>
