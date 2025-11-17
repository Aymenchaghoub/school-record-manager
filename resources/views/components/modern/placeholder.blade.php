@props([
    'title' => 'Coming Soon',
    'description' => 'This feature is currently under development and will be available soon.',
    'icon' => null,
    'showProgress' => false,
    'progress' => 0,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-soft border border-gray-200 overflow-hidden']) }}>
    <div class="relative">
        {{-- Gradient Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-primary-100 to-secondary-50 opacity-50"></div>
        
        {{-- Content --}}
        <div class="relative p-12 text-center">
            {{-- Icon --}}
            @if($icon)
                <div class="w-24 h-24 mx-auto mb-6 text-primary-500">
                    {!! $icon !!}
                </div>
            @else
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            @endif
            
            {{-- Title --}}
            <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ $title }}</h2>
            
            {{-- Description --}}
            <p class="text-gray-600 max-w-md mx-auto mb-8">{{ $description }}</p>
            
            {{-- Progress Bar (optional) --}}
            @if($showProgress)
                <div class="max-w-xs mx-auto mb-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <span>Development Progress</span>
                        <span class="font-semibold">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-primary-500 to-secondary-500 h-2 rounded-full transition-all duration-500 ease-out"
                             style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @endif
            
            {{-- Custom slot for additional content --}}
            @if($slot->isNotEmpty())
                <div class="mt-8">
                    {{ $slot }}
                </div>
            @endif
            
            {{-- Decorative elements --}}
            <div class="absolute top-4 left-4 w-8 h-8 bg-primary-200 rounded-full opacity-50"></div>
            <div class="absolute top-12 right-8 w-6 h-6 bg-secondary-200 rounded-full opacity-50"></div>
            <div class="absolute bottom-8 left-12 w-10 h-10 bg-primary-200 rounded-full opacity-30"></div>
            <div class="absolute bottom-4 right-4 w-12 h-12 bg-secondary-200 rounded-full opacity-30"></div>
        </div>
    </div>
</div>
