@props([
    'variant' => 'neutral',
    'size' => 'sm',
    'icon' => null,
])

@php
    $variants = [
        'success' => 'bg-green-100 text-green-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger'  => 'bg-red-100 text-red-700',
        'info'    => 'bg-blue-100 text-blue-700',
        'neutral' => 'bg-gray-100 text-gray-700',
        'green'   => 'bg-green-100 text-green-700',
        'yellow'  => 'bg-yellow-100 text-yellow-700',
        'orange'  => 'bg-orange-100 text-orange-700',
        'red'     => 'bg-red-100 text-red-700',
        'gray'    => 'bg-gray-100 text-gray-700',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $classes = "inline-flex items-center gap-1 rounded-full font-medium {$sizes[$size]} {$variants[$variant]}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="material-symbols-outlined" style="font-size: 14px;">{{ $icon }}</span>
    @endif
    {{ $slot }}
</span>
