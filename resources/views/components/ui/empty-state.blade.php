@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
])

<div class="py-12 text-center">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
        <span class="material-symbols-outlined text-gray-400 text-3xl">{{ $icon }}</span>
    </div>
    @if($title)
        <h3 class="text-base font-semibold text-gray-800">{{ $title }}</h3>
    @endif
    @if($description)
        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5 flex justify-center gap-3">
            {{ $action }}
        </div>
    @endisset
</div>
