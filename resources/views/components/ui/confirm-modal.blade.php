@props([
    'variant' => 'danger',
    'icon' => null,
    'title' => null,
    'message' => null,
    'confirmLabel' => 'Sí, continuar',
    'cancelLabel' => 'Cancelar',
    'confirmAction' => 'executeConfirmedAction',
    'cancelAction' => 'cancelConfirmation',
    'maxWidth' => 'max-w-md',
    'id' => null,
])

@php
    $circles = [
        'danger'  => 'bg-red-100 text-red-600',
        'warning' => 'bg-amber-100 text-amber-600',
        'success' => 'bg-green-100 text-green-600',
        'primary' => 'bg-blue-100 text-blue-600',
    ];
    $defaultIcons = [
        'danger'  => 'warning',
        'warning' => 'warning',
        'success' => 'check_circle',
        'primary' => 'help',
    ];
    $icon = $icon ?: ($defaultIcons[$variant] ?? 'warning');
    $buttonVariant = isset($circles[$variant]) ? $variant : 'primary';
    $id = $id ?: 'confirm-modal-' . $variant;
    $titleId = $id . '-title';
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
    ];
    $maxClass = $sizes[$maxWidth] ?? $maxWidth;
@endphp

<div x-data="{ open: true }" x-show="open" x-cloak
    x-init="$nextTick(() => { if ($refs.confirmButton) $refs.confirmButton.focus(); })"
    @keydown.escape.window="open = false; @if($cancelAction)$wire.call('{{ $cancelAction }}');@endif"
    role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}"
    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto z-50 flex items-center justify-center p-4"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative mx-auto w-full {{ $maxClass }}"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        <x-ui.card>
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full {{ $circles[$variant] ?? $circles['danger'] }} mb-4">
                    <span class="material-symbols-outlined text-2xl">{{ $icon }}</span>
                </div>
                <h3 id="{{ $titleId }}" class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($message)
                    <p class="text-sm text-gray-600 mt-2">{{ $message }}</p>
                @endif
                {{ $slot }}
            </div>
            <x-slot:footer>
                <div class="flex flex-col sm:flex-row-reverse gap-3">
                    <x-ui.button variant="{{ $buttonVariant }}" icon="{{ $icon }}" x-ref="confirmButton" wire:click="{{ $confirmAction }}">
                        {{ $confirmLabel }}
                    </x-ui.button>
                    <x-ui.button variant="secondary" wire:click="{{ $cancelAction }}">{{ $cancelLabel }}</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </div>
</div>
