<div class="max-w-5xl mx-auto">
    <x-ui.card icon="inventory_2" title="Mis Requisiciones" subtitle="Material solicitado para tus órdenes de trabajo">
        <x-slot:headerActions>
            <a href="{{ $hasPending ? '#' : route('technician.requisitions.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 {{ $hasPending ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-sm font-medium rounded-lg shadow-sm transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                {{ $hasPending ? 'Pendiente de aprobación' : 'Nueva Requisición' }}
            </a>
            <x-ui.button variant="success" icon="event_available" wire:click="closeWeek">
                Cierre Semanal
            </x-ui.button>
        </x-slot:headerActions>

        @if($requisitions->isEmpty())
            <div class="text-center py-12 bg-gray-50/50 rounded-xl border border-dashed border-gray-300">
                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inventory</span>
                <p class="text-gray-500">No tienes requisiciones registradas</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($requisitions as $req)
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div>
                                <span class="font-mono text-sm font-semibold text-gray-700">#{{ $req->id }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $req->created_at->format('d/m/Y') }}</span>
                                @php
                                    $statusMap = ['closed' => ['Cerrada', 'success'], 'heredada' => ['Heredada', 'warning'], 'pending' => ['Pendiente', 'warning'], 'approved' => ['Activa', 'success'], 'rejected' => ['Rechazada', 'danger']];
                                    $s = $statusMap[$req->status] ?? [$req->status, 'neutral'];
                                @endphp
                                <x-ui.badge :variant="$s[1]">{{ $s[0] }}</x-ui.badge>
                            </div>
                            <a href="{{ route('technician.requisitions.show', $req->id) }}"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ajustar consumo →
                            </a>
                        </div>
                        @if($req->workOrders->isNotEmpty())
                            <p class="text-xs text-gray-500 mb-2">Órdenes:
                                @foreach($req->workOrders as $wo)
                                    #{{ $wo->id }}@if(!$loop->last), @endif
                                @endforeach
                            </p>
                        @endif
                        <div class="text-sm text-gray-700">
                            <span class="font-medium">Productos:</span>
                            @foreach($req->items as $item)
                                <span class="ml-1">{{ $item->product->name }}
                                    ({{ $item->quantity_requested }})</span>@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    {{-- Modal de cierre semanal --}}
    <div x-data="{ show: @entangle('showCloseModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600">event_available</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Cierre semanal</h3>
                            <p class="text-xs text-gray-500">Confirmá el material que vas a devolver a bodega</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cancelClose" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <div class="p-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-3 py-2 text-left text-gray-600 font-medium">Producto</th>
                                <th class="px-3 py-2 text-center text-gray-600 font-medium">Entregado</th>
                                <th class="px-3 py-2 text-center text-gray-600 font-medium">Usado</th>
                                <th class="px-3 py-2 text-center text-gray-600 font-medium">Sobrante a devolver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($closeSummary as $row)
                                <tr>
                                    <td class="px-3 py-2.5 text-gray-800">{{ $row['product_name'] }}
                                        @if(!empty($row['unit']) && $row['unit'] !== 'unidad')
                                            <span class="text-xs text-gray-400">· {{ $row['unit'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono">{{ $row['entregado'] }}</td>
                                    <td class="px-3 py-2.5 text-center font-mono text-green-600">{{ $row['usado'] }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" step="any" min="0"
                                            wire:model="closeReturnQty.{{ $row['product_id'] }}"
                                            class="w-24 px-2 py-1.5 text-center rounded-lg border border-gray-300 bg-white text-sm font-mono">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="success" icon="check_circle" wire:click="confirmClose">Confirmar devolución</x-ui.button>
                    <button @click="show = false" wire:click="cancelClose"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>