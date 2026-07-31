<div>
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <input type="text" wire:model.live="historySearch" placeholder="Buscar por plan o zona..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
            </div>
            <input type="date" wire:model.live="historyDateFrom" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">
            <span class="text-xs text-gray-400">—</span>
            <input type="date" wire:model.live="historyDateTo" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium">Fecha</th>
                        <th class="text-left px-4 py-3 font-medium">Zona</th>
                        <th class="text-left px-4 py-3 font-medium">Plan</th>
                        <th class="text-right px-4 py-3 font-medium">Anterior</th>
                        <th class="text-right px-4 py-3 font-medium">Nuevo</th>
                        <th class="text-center px-4 py-3 font-medium">Cambio</th>
                        <th class="text-left px-4 py-3 font-medium">Usuario</th>
                        <th class="text-center px-4 py-3 font-medium">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($priceHistories as $h)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $h->zone?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $h->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">
                            @if($h->old_price !== null) ${{ number_format($h->old_price, 2) }} @else <span class="text-gray-300">—</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            @if($h->new_price !== null) ${{ number_format($h->new_price, 2) }} @else <span class="text-gray-300">—</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($h->old_price === null && $h->new_price !== null)
                                <x-ui.badge variant="success">Asignado</x-ui.badge>
                            @elseif($h->new_price === null)
                                <x-ui.badge variant="neutral">Restablecido</x-ui.badge>
                            @elseif($h->new_price > $h->old_price)
                                <x-ui.badge variant="danger">Subió</x-ui.badge>
                            @elseif($h->new_price < $h->old_price)
                                <x-ui.badge variant="info">Bajó</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $h->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="loadPriceHistory({{ $h->plan_id }}, {{ $h->zone_id ?? 'null' }})" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-500 hover:bg-gray-200" title="Ver detalle">
                                <span class="material-symbols-outlined text-xs align-text-bottom">visibility</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">history</span>
                            <p>No hay cambios registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($priceHistories->hasPages())
        <div class="mt-4">{{ $priceHistories->links() }}</div>
        @endif
    </div>

    {{-- MODAL HISTORIAL DETALLE --}}
    @if($showHistoryModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-base">history</span>
                        Historial de precios
                    </h3>
                    <button wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-5">
                    @if(count($historyRecords) > 0)
                    <div class="space-y-3">
                        @foreach($historyRecords as $record)
                        <div class="flex items-start gap-3 px-3 py-3 rounded-lg border border-gray-100 bg-gray-50/50">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-medium">${{ number_format($record->new_price ?? 0, 2) }}</span>
                                    @if($record->old_price !== null) <span class="text-xs text-gray-400">(antes ${{ number_format($record->old_price, 2) }})</span> @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $record->created_at?->format('d/m/Y H:i') }} @if($record->user) · {{ $record->user->name }} @endif</div>
                            </div>
                            @if($record->old_price === null) <x-ui.badge variant="success">Asignado</x-ui.badge>
                            @elseif($record->new_price === null) <x-ui.badge variant="neutral">Restablecido</x-ui.badge>
                            @elseif($record->new_price > $record->old_price) <x-ui.badge variant="danger">Subió</x-ui.badge>
                            @elseif($record->new_price < $record->old_price) <x-ui.badge variant="info">Bajó</x-ui.badge>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">history</span>
                        <p>No hay cambios registrados para este plan.</p>
                    </div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                    <x-ui.button variant="ghost" wire:click="closeHistoryModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
