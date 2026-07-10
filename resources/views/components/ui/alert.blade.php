@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $variants = [
        'info'    => 'bg-blue-50 border-blue-200 text-blue-900',
        'success' => 'bg-green-50 border-green-200 text-green-900',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-900',
        'danger'  => 'bg-red-50 border-red-200 text-red-900',
    ];

    $icons = [
        'info'    => 'info',
        'success' => 'check_circle',
        'warning' => 'warning',
        'danger'  => 'error',
    ];

    $classes = "rounded-lg border p-4 {$variants[$variant]}";
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-cloak @endif>
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-xl flex-shrink-0">{{ $icons[$variant] }}</span>
        <div class="flex-1 text-xs space-y-1.5">
            @if($title)
                <p class="font-semibold">{{ $title }}</p>
            @endif
            <div class="[&_ul]:list-disc [&_ul]:list-inside [&_ul]:space-y-1">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <button type="button" @click="show = false" class="flex-shrink-0 hover:opacity-70 transition">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        @endif
    </div>
</div>
