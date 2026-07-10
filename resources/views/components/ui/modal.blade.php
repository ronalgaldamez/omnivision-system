@props([
    'title' => null,
    'icon' => null,
    'maxWidth' => 'max-w-lg',
    'show' => false,
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ];
    $maxClass = $sizes[$maxWidth] ?? $maxWidth;
@endphp

<div x-data="{ open: @js($show) }" x-show="open" x-cloak
    {{ $attributes->merge(['class' => 'fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4']) }}
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative mx-auto p-5 w-full {{ $maxClass }}"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
            @if($title)
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        @if($icon)
                            <span class="material-symbols-outlined text-gray-500">{{ $icon }}</span>
                        @endif
                        {{ $title }}
                    </h3>
                    @isset($headerActions)
                        <div class="flex items-center gap-2">
                            {{ $headerActions }}
                        </div>
                    @else
                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    @endisset
                </div>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
