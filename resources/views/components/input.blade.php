@props([
    'type' => 'text',
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

<div class="space-y-1">
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
                <i class="{{ $icon }} text-gray-400"></i>
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge(['class' => 'block w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors duration-200 ' . ($icon ? 'pl-10' : '') . ($error || $errors->has($name) ? ' border-danger-300 text-danger-900 placeholder-danger-300 focus:ring-danger-500 focus:border-danger-500' : '')]) }}
        >

        @if($error || $errors->has($name))
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-exclamation-circle text-danger-500"></i>
            </div>
        @endif
    </div>

    @if($hint && !($error || $errors->has($name)))
        <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
    @enderror

    @if($error)
        <p class="mt-1 text-sm text-danger-600">{{ $error }}</p>
    @endif
</div>
