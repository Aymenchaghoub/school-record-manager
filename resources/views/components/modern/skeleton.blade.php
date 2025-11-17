@props([
    'type' => 'text', // text, card, table-row, stat-card
    'count' => 1,
    'animate' => true
])

@php
$animationClass = $animate ? 'animate-pulse' : '';
@endphp

@switch($type)
    @case('text')
        @for($i = 0; $i < $count; $i++)
            <div class="space-y-2 {{ $animationClass }}">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
        @endfor
        @break
        
    @case('card')
        @for($i = 0; $i < $count; $i++)
            <div class="bg-white rounded-xl shadow-soft border border-gray-200 p-6 {{ $animationClass }}">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-6 bg-gray-200 rounded w-32"></div>
                    <div class="h-8 w-8 bg-gray-200 rounded-full"></div>
                </div>
                <div class="space-y-3">
                    <div class="h-4 bg-gray-200 rounded"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    <div class="h-4 bg-gray-200 rounded w-4/6"></div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <div class="h-3 bg-gray-200 rounded w-20"></div>
                    <div class="h-8 bg-gray-200 rounded-lg w-24"></div>
                </div>
            </div>
        @endfor
        @break
        
    @case('table-row')
        @for($i = 0; $i < $count; $i++)
            <tr class="{{ $animationClass }}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-10 w-10 bg-gray-200 rounded-full"></div>
                        <div class="ml-4 space-y-2">
                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                            <div class="h-3 bg-gray-200 rounded w-24"></div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="h-4 bg-gray-200 rounded w-28"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                        <div class="h-8 w-8 bg-gray-200 rounded"></div>
                        <div class="h-8 w-8 bg-gray-200 rounded"></div>
                    </div>
                </td>
            </tr>
        @endfor
        @break
        
    @case('stat-card')
        @for($i = 0; $i < $count; $i++)
            <div class="bg-white rounded-xl shadow-soft border border-gray-200 p-6 {{ $animationClass }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                    <div class="h-10 w-10 bg-gray-200 rounded-lg"></div>
                </div>
                <div class="h-8 bg-gray-200 rounded w-32 mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-20"></div>
            </div>
        @endfor
        @break
        
    @default
        <div class="h-4 bg-gray-200 rounded {{ $animationClass }}"></div>
@endswitch
