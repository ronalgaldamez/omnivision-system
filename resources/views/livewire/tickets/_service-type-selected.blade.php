<div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
    <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
        <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-gray-900 truncate">{{ $serviceTypes->firstWhere('id', $service_type_id)?->name ?? '—' }}</p>
        @php $_st = $serviceTypes->firstWhere('id', $service_type_id); @endphp
        @if($_st?->requires_contract)
            <x-ui.badge variant="success" size="sm" class="mt-1">Requiere Contrato</x-ui.badge>
        @elseif($_st?->requires_potential)
            <x-ui.badge variant="warning" size="sm" class="mt-1">Cliente Potencial</x-ui.badge>
        @elseif($_st?->requires_ot)
            <x-ui.badge variant="warning" size="sm" class="mt-1">Requiere OT</x-ui.badge>
        @elseif($_st?->requires_noc)
            <x-ui.badge variant="info" size="sm" class="mt-1">Requiere NOC</x-ui.badge>
        @endif
    </div>
    <div class="flex items-center gap-1 flex-shrink-0">
        @if ($showChange ?? false)
            <button type="button" wire:click="openServiceTypeModal"
                class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
        @endif
        @if ($showClose ?? false)
            <button type="button" wire:click="clearServiceType"
                class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar tipo de servicio">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        @endif
    </div>
</div>
