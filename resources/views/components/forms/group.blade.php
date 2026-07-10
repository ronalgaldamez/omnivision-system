@props([
    'label' => null,
    'icon' => null,
    'required' => false,
    'error' => null,
    'name' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <x-forms.label :icon="$icon" :required="$required">
            {{ $label }}
        </x-forms.label>
    @endif

    {{ $slot }}

    @if($error || ($name && $errors->has($name)))
        <x-forms.error :name="$error ? null : $name">
            {{ $error ?? '' }}
        </x-forms.error>
    @endif
</div>
