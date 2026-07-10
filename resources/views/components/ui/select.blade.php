@props([
    'label' => null,
    'icon' => null,
    'error' => null,
    'disabled' => false,
    'required' => false,
    'name' => null,
    'id' => null,
    'placeholder' => 'Seleccionar',
])

@php
    $id = $id ?? $name ?? 'select-' . md5($attributes->wire('model')->value() ?? uniqid());
    $hasError = $error ? true : ($errors->has($name) ? true : false);
    $errorMsg = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="space-y-1.5">
    @if($label)
        <x-forms.label :for="$id" :required="$required" :icon="$icon">
            {{ $label }}
        </x-forms.label>
    @endif

    <div class="relative">
        @if($icon)
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none">{{ $icon }}</span>
        @endif
        <select id="{{ $id }}" name="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full rounded-lg border text-sm transition appearance-none '
                . ($icon ? 'pl-10 ' : 'pl-4 ') . 'pr-8 py-2.5 '
                . ($hasError ? 'border-red-300 bg-red-50 text-red-900 focus:border-red-400 focus:bg-white '
                    : 'border-gray-200 bg-gray-50 text-gray-900 focus:border-gray-400 focus:bg-white ')
                . ($disabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '')
            ]) }}
            @if($disabled) disabled @endif
            @if($required) required @endif
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
    </div>

    @if($hasError && $errorMsg)
        <x-forms.error>{{ $errorMsg }}</x-forms.error>
    @endif
</div>
