@props([
    'type' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
    'icon' => true,
])

@php
    $typeClasses = match($type) {
        'success' => 'bg-success-50 border-success-200 text-success-800',
        'warning' => 'bg-warning-50 border-warning-200 text-warning-800',
        'danger' => 'bg-danger-50 border-danger-200 text-danger-800',
        default => 'bg-blue-50 border-blue-200 text-blue-800',
    };
    
    $iconSvg = match($type) {
        'success' => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
        'warning' => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
        'danger' => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
        default => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
    };
    
    $iconColor = match($type) {
        'success' => 'text-success-600',
        'warning' => 'text-warning-600',
        'danger' => 'text-danger-600',
        default => 'text-blue-600',
    };
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-90"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-90"
    {{ $attributes->merge(['class' => "rounded-xl border-l-4 p-4 $typeClasses"]) }}
>
    <div class="flex">
        @if($icon)
            <div class="flex-shrink-0 {{ $iconColor }}">
                {!! $iconSvg !!}
            </div>
        @endif
        
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-medium">{{ $title }}</h3>
            @endif
            
            <div class="text-sm {{ $title ? 'mt-1' : '' }}">
                {{ $slot }}
            </div>
        </div>
        
        @if($dismissible)
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button 
                        @click="show = false"
                        type="button" 
                        class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 {{ match($type) {
                            'success' => 'text-success-500 hover:bg-success-100 focus:ring-success-600',
                            'warning' => 'text-warning-500 hover:bg-warning-100 focus:ring-warning-600',
                            'danger' => 'text-danger-500 hover:bg-danger-100 focus:ring-danger-600',
                            default => 'text-blue-500 hover:bg-blue-100 focus:ring-blue-600'
                        } }}"
                    >
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
