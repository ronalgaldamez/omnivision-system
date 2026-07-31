@foreach ($serviceTypeResults as $st)
<li wire:click="selectServiceType({{ $st->id }})"
    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
    <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ str_replace('_', ' ', $st->name) }}</span>
    @if($showBadges ?? true)
        @if($st->requires_contract)
            <x-ui.badge variant="success" size="sm">Requiere Contrato</x-ui.badge>
        @elseif($st->requires_potential)
            <x-ui.badge variant="warning" size="sm">Cliente Potencial</x-ui.badge>
        @elseif($st->requires_ot)
            <x-ui.badge variant="warning" size="sm">Requiere OT</x-ui.badge>
        @elseif($st->requires_noc)
            <x-ui.badge variant="info" size="sm">Requiere NOC</x-ui.badge>
        @endif
    @endif
</li>
@endforeach
