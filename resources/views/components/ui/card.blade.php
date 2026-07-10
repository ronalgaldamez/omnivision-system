@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'overflow' => 'hidden',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-lg border border-gray-100 overflow-' . $overflow]) }}>
    @if($title || $icon || !empty($headerActions))
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($icon)
                        <span class="material-symbols-outlined text-gray-500">{{ $icon }}</span>
                    @endif
                    <div>
                        @if($title)
                            <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @isset($headerActions)
                    <div class="flex items-center gap-2">
                        {{ $headerActions }}
                    </div>
                @endisset
            </div>
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $footer }}
        </div>
    @endisset
</div>
