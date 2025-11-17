@props([
    'label' => null,
    'name' => '',
    'value' => '',
    'options' => [],
    'placeholder' => '-- Select --',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,
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
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <span class="text-gray-400">
                    {!! $icon !!}
                </span>
            </div>
        @endif
        
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('class') }}
            class="block w-full {{ $icon ? 'pl-10' : 'pl-3' }} pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg appearance-none transition-all duration-200 @error($name) border-danger-300 text-danger-900 focus:ring-danger-500 focus:border-danger-500 @enderror {{ $disabled ? 'bg-gray-50' : 'bg-white' }}"
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            
            @foreach($options as $key => $option)
                @if(is_array($option))
                    <optgroup label="{{ $key }}">
                        @foreach($option as $subKey => $subOption)
                            <option value="{{ $subKey }}" {{ old($name, $value) == $subKey ? 'selected' : '' }}>
                                {{ $subOption }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $key }}" {{ old($name, $value) == $key ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endif
            @endforeach
        </select>
        
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
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
