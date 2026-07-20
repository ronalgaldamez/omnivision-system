@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'disabled' => false,
    'loading' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition focus:outline-none';

    $variants = [
        'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm',
        'success'   => 'bg-green-600 text-white hover:bg-green-700 shadow-sm',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
        'warning'   => 'bg-amber-600 text-white hover:bg-amber-700 shadow-sm',
        'info'      => 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm',
        'secondary' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50',
        'ghost'     => 'bg-transparent text-gray-700 hover:bg-gray-50',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-6 py-2.5 text-sm',
        'lg' => 'px-8 py-3 text-base',
    ];

    $classes = trim("{$base} {$variants[$variant]} {$sizes[$size]}"
        . ($disabled || $loading ? ' opacity-50 cursor-not-allowed' : ''));
@endphp

@if (!empty($attributes->get('href')))
    <a href="{{ $attributes->get('href') }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        @elseif($icon)
            <span class="material-symbols-outlined text-base">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif>
        @if ($loading)
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        @elseif($icon)
            <span class="material-symbols-outlined text-base">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </button>
@endif
