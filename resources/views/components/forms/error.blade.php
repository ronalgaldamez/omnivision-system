@props([
    'name' => null,
])

@php
    $hasError = $name ? $errors->has($name) : true;
    $errorMsg = $name ? $errors->first($name) : $slot;
@endphp

@if($hasError && $errorMsg)
    <p {{ $attributes->merge(['class' => 'text-xs text-red-600 mt-1.5 block font-medium']) }}>
        {{ $errorMsg }}
    </p>
@endif
