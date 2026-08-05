@props([
    'type' => 'area',
    'title' => null,
    'subtitle' => null,
    'series' => [],
    'categories' => [],
    'height' => 300,
    'colors' => null,
    'labels' => null,
])

@php
    $chartKey = (string) $attributes->get('wire:key', '');
    $chartId = 'chart-' . md5($title . $type . $chartKey . json_encode($series));
    $chartColors = $colors ?? ['#2563eb', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4'];
    $chartType = $type;
    $chartHeight = $height;
    $chartSeries = json_encode($series);
    $chartCategories = json_encode($categories);
    $chartLabels = json_encode($labels ?? []);
    $chartColorsJson = json_encode($chartColors);
@endphp

<div {{ $attributes }}>
    @if ($title)
        <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-800">{{ $title }}</h3>
            @if ($subtitle)
                <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div wire:ignore x-data="apexChart('{{ $chartId }}', '{{ $chartType }}', {{ $chartHeight }}, {{ $chartSeries }}, {{ $chartCategories }}, {{ $chartLabels }}, {{ $chartColorsJson }})">
        <div id="{{ $chartId }}"></div>
    </div>
</div>

