@props([
    'for' => null,
    'required' => false,
    'icon' => null,
])

<label @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2']) }}>
    @if($icon)
        <span class="material-symbols-outlined text-gray-500 text-sm">{{ $icon }}</span>
    @endif
    {{ $slot }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>
