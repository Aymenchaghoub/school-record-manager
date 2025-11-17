@props([
    'icon' => null,
    'title' => 'No data available',
    'description' => null,
    'actionUrl' => null,
    'actionText' => null,
    'actionIcon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-6']) }}>
    {{-- Icon --}}
    @if($icon)
        <div class="w-20 h-20 mb-4 text-gray-300">
            {!! $icon !!}
        </div>
    @else
        <svg class="w-20 h-20 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    @endif
    
    {{-- Title --}}
    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $title }}</h3>
    
    {{-- Description --}}
    @if($description)
        <p class="text-sm text-gray-500 text-center max-w-md mb-6">{{ $description }}</p>
    @endif
    
    {{-- Action Button --}}
    @if($actionUrl && $actionText)
        <a href="{{ $actionUrl }}" 
           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200 font-medium text-sm">
            @if($actionIcon)
                <span class="mr-2">{!! $actionIcon !!}</span>
            @endif
            {{ $actionText }}
        </a>
    @endif
    
    {{-- Custom slot for additional content --}}
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
