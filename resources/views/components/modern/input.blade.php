@props([
    'label' => null,
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,
    'addon' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-400">
                    {!! $icon !!}
                </span>
            </div>
        @endif
        
        @if($addon)
            <div class="flex">
                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                    {{ $addon }}
                </span>
                <input
                    type="{{ $type }}"
                    name="{{ $name }}"
                    id="{{ $name }}"
                    value="{{ old($name, $value) }}"
                    placeholder="{{ $placeholder }}"
                    {{ $required ? 'required' : '' }}
                    {{ $disabled ? 'disabled' : '' }}
                    {{ $readonly ? 'readonly' : '' }}
                    {{ $attributes->except('class') }}
                    class="flex-1 block w-full rounded-none rounded-r-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors duration-200 @error($name) border-danger-300 text-danger-900 placeholder-danger-300 focus:ring-danger-500 focus:border-danger-500 @enderror {{ $disabled || $readonly ? 'bg-gray-50' : '' }}"
                >
            </div>
        @else
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $readonly ? 'readonly' : '' }}
                {{ $attributes->except('class') }}
                class="block w-full {{ $icon ? 'pl-10' : '' }} pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-all duration-200 @error($name) border-danger-300 text-danger-900 placeholder-danger-300 focus:ring-danger-500 focus:border-danger-500 @enderror {{ $disabled || $readonly ? 'bg-gray-50' : '' }}"
            >
        @endif
    </div>
    
    @if($hint && !$errors->has($name))
        <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
    @endif
    
    @error($name)
        <p class="mt-1 text-sm text-danger-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
