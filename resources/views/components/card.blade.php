@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'actions' => null,
    'padding' => true,
    'shadow' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 ' . ($shadow ? 'shadow-soft' : '')]) }}>
    @if($title || $subtitle || $icon || $actions)
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if($icon)
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-100 to-primary-200 rounded-lg flex items-center justify-center">
                            <i class="{{ $icon }} text-primary-600"></i>
                        </div>
                    @endif
                    <div>
                        @if($title)
                            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-gray-500">{{ $subtitle }}</p>
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

    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
