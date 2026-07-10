@props([
    'label' => null,
    'description' => null,
    'disabled' => false,
    'onColor' => 'blue',
    'name' => null,
    'id' => null,
])

@php
    $id = $id ?? $name ?? 'toggle-' . md5($attributes->wire('model')->value() ?? uniqid());

    $colors = [
        'blue'  => 'peer-checked:bg-blue-600 focus:ring-blue-300',
        'green' => 'peer-checked:bg-green-600 focus:ring-green-300',
        'amber' => 'peer-checked:bg-amber-500 focus:ring-amber-300',
        'red'   => 'peer-checked:bg-red-600 focus:ring-red-300',
    ];
    $colorClass = $colors[$onColor] ?? $colors['blue'];
@endphp

<div class="flex items-center justify-between gap-4 {{ $disabled ? 'opacity-60' : '' }}">
    <div class="flex-1">
        @if($label)
            <label for="{{ $id }}" class="text-sm font-semibold text-gray-800 cursor-pointer {{ $disabled ? 'cursor-not-allowed' : '' }}">
                {{ $label }}
            </label>
        @endif
        @if($description)
            <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 {{ $disabled ? 'cursor-not-allowed' : '' }}">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
            {{ $attributes->merge(['class' => 'sr-only peer']) }}
            @if($disabled) disabled @endif
        />
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all {{ $colorClass }}">
        </div>
    </label>
</div>
