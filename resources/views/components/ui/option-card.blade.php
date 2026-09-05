@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'active' => false,
])

<button type="button" role="radio"
    @if($active) aria-checked="true" @else aria-checked="false" @endif
    {{ $attributes->merge(['class' => 'w-full text-left rounded-xl border-2 p-4 transition flex items-start gap-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 '
        . ($active
            ? 'border-blue-600 bg-blue-50/60 shadow-sm'
            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50')]) }}>
    <span class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center transition {{ $active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500' }}">
        <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
    </span>
    <span class="flex-1 min-w-0">
        @if($title)
            <span class="block text-sm font-semibold {{ $active ? 'text-blue-900' : 'text-gray-800' }}">{{ $title }}</span>
        @endif
        @if($description)
            <span class="block text-xs {{ $active ? 'text-blue-700' : 'text-gray-500' }} mt-0.5">{{ $description }}</span>
        @endif
    </span>
    <span class="flex-shrink-0 w-5 h-5 mt-0.5 rounded-full border-2 flex items-center justify-center transition {{ $active ? 'border-blue-600 bg-blue-600' : 'border-gray-300 bg-white' }}">
        @if($active)
            <span class="material-symbols-outlined text-white text-xs" style="font-size: 12px;">check</span>
        @endif
    </span>
</button>
