@props([
    'title' => '',
    'value' => '0',
    'icon' => null,
    'color' => 'primary',
    'trend' => null,
    'trendValue' => null,
])

@php
$colors = [
    'primary' => 'from-primary-500 to-primary-600',
    'secondary' => 'from-secondary-500 to-secondary-600',
    'success' => 'from-success-500 to-success-600',
    'danger' => 'from-danger-500 to-danger-600',
    'warning' => 'from-warning-500 to-warning-600',
    'info' => 'from-blue-500 to-blue-600',
    'purple' => 'from-purple-500 to-purple-600',
];

$bgColor = $colors[$color] ?? $colors['primary'];
@endphp

<div class="bg-white rounded-xl shadow-soft border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
            
            @if($trend)
                <div class="flex items-center mt-2 text-sm">
                    @if($trend === 'up')
                        <i class="fas fa-arrow-up text-success-500 mr-1"></i>
                        <span class="text-success-600 font-medium">{{ $trendValue }}</span>
                    @elseif($trend === 'down')
                        <i class="fas fa-arrow-down text-danger-500 mr-1"></i>
                        <span class="text-danger-600 font-medium">{{ $trendValue }}</span>
                    @else
                        <i class="fas fa-minus text-gray-500 mr-1"></i>
                        <span class="text-gray-600 font-medium">{{ $trendValue }}</span>
                    @endif
                    <span class="text-gray-500 ml-1">from last month</span>
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="w-12 h-12 bg-gradient-to-br {{ $bgColor }} rounded-xl flex items-center justify-center shadow-soft">
                <i class="{{ $icon }} text-white text-lg"></i>
            </div>
        @endif
    </div>
</div>
