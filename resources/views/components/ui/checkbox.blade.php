@props([
    'label' => null,
    'description' => null,
    'error' => null,
    'disabled' => false,
    'name' => null,
    'id' => null,
])

@php
    $id = $id ?? $name ?? 'checkbox-' . md5($attributes->wire('model')->value() ?? uniqid());
    $hasError = $error ? true : ($errors->has($name) ? true : false);
    $errorMsg = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="space-y-1.5">
    <div class="flex items-start gap-3">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1"
            {{ $attributes->merge(['class' => 'mt-0.5 w-4 h-4 rounded border-gray-300 text-gray-700 focus:ring-gray-500 '
                . ($disabled ? 'opacity-50 cursor-not-allowed' : '')
            ]) }}
            @if($disabled) disabled @endif
        />
        <div class="flex-1">
            @if($label)
                <label for="{{ $id }}" class="text-sm font-medium text-gray-700 cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
                    {{ $label }}
                </label>
            @endif
            @if($description)
                <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
            @endif
        </div>
    </div>
    @if($hasError && $errorMsg)
        <x-forms.error>{{ $errorMsg }}</x-forms.error>
    @endif
</div>
