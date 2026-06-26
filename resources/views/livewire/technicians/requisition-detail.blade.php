<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">receipt</span>
                    Requisición #{{ $requisition->id }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $requisition->created_at->format('d/m/Y') }} — Estado:
                    <span class="font-medium @switch($requisition->status) @case('open') text-green-600 @break @case('heredada') text-yellow-600 @break @default text-gray-600 @endswitch">
                        @switch($requisition->status) @case('open') Abierta @break @case('heredada') Heredada @break @default Cerrada @endswitch
                    </span>
                    @if($requisition->technician)
                        — Técnico: {{ $requisition->technician->name }}
                    @endif
                </p>
            </div>
            <a href="{{ route('technician.requisitions.index') }}"
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Volver</a>
        </div>

        <div class="p-6 space-y-6">

            {{-- 1. Resumen general --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-600 font-medium">Solicitado</p>
                    <p class="text-lg font-mono font-bold text-blue-700 mt-1">{{ $totalRequested }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-xs text-green-600 font-medium">Utilizado</p>
                    <p class="text-lg font-mono font-bold text-green-700 mt-1">{{ $totalUsed }}</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <p class="text-xs text-amber-600 font-medium">En inventario</p>
                    <p class="text-lg font-mono font-bold text-amber-700 mt-1">{{ $totalInHand }}</p>
                </div>
                <div class="p-3 {{ $hasWeeklyClose ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border">
                    <p class="text-xs {{ $hasWeeklyClose ? 'text-green-600' : 'text-gray-500' }} font-medium">Cierre semanal</p>
                    <p class="text-lg font-mono font-bold {{ $hasWeeklyClose ? 'text-green-700' : 'text-gray-400' }} mt-1">
                        {{ $hasWeeklyClose ? 'Realizado' : 'Pendiente' }}
                    </p>
                </div>
            </div>

            {{-- 2. Notas --}}
            @if($requisition->notes)
                <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200 text-sm text-yellow-800">
                    <span class="font-medium">Notas:</span> {{ $requisition->notes }}
                </div>
            @endif

            {{-- 3. OTs Vinculadas --}}
            @if($linkedWorkOrders->isNotEmpty())
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-500 text-base">link</span>
                        OTs vinculadas
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($linkedWorkOrders as $wo)
                            <button wire:click="selectWorkOrder({{ $wo->id }})"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition border
                                {{ $selectedWorkOrderId === $wo->id
                                    ? 'bg-blue-100 text-blue-700 border-blue-300 shadow-sm'
                                    : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' }}">
                                {{ $wo->code ?? 'OT-'.$wo->id }} — {{ $wo->client->name ?? 'N/A' }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4. OTS sin vincular --}}
            @if($unlinkedWorkOrders->isNotEmpty())
                <div>
                    <h3 class="text-sm font-medium text-red-700 mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-red-500 text-base">link_off</span>
                        OTs disponibles para vincular
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($unlinkedWorkOrders as $wo)
                            <div class="inline-flex items-center gap-1 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium border border-red-200">
                                <span>{{ $wo->code ?? 'OT-'.$wo->id }} — {{ $wo->client->name ?? 'N/A' }}</span>
                                <button wire:click="linkWorkOrder({{ $wo->id }})"
                                    class="ml-1 p-0.5 text-red-500 hover:text-green-600 hover:bg-green-100 rounded transition"
                                    title="Vincular a esta requisición">
                                    <span class="material-symbols-outlined text-sm">add_circle</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($linkedWorkOrders->isEmpty() && $unlinkedWorkOrders->isEmpty())
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-500 text-center">
                    No hay órdenes de trabajo asociadas.
                </div>
            @endif

            {{-- 5. Material de la OT seleccionada --}}
            @if($selectedWorkOrderId && !empty($selectedWoMaterials))
                @php $selectedWo = $linkedWorkOrders->firstWhere('id', $selectedWorkOrderId); @endphp
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-md font-semibold text-gray-800">
                            Material de {{ $selectedWo->code ?? 'OT-'.$selectedWo->id }}
                        </h3>
                        @if($requisition->status === 'open')
                            <button wire:click="openEditModal"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-yellow-600 transition">
                                <span class="material-symbols-outlined text-base">edit</span>
                                Ajustar
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Solicitado</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Utilizado</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($selectedWoMaterials as $mat)
                                    @php
                                        $stockQty = \App\Models\TechnicianInventory::where('technician_id', Auth::id())
                                            ->where('product_id', $mat['product_id'])->value('quantity_in_hand') ?? 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/80">
                                        <td class="px-4 py-3 text-gray-800">{{ $mat['product_name'] }} ({{ $mat['product_sku'] }})</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ $mat['quantity_requested'] }}</td>
                                        <td class="px-4 py-3 text-center font-mono {{ $mat['quantity_used'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $mat['quantity_used'] > 0 ? $mat['quantity_used'] : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-mono text-sm {{ $stockQty > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                            {{ $stockQty > 0 ? $stockQty : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($selectedWorkOrderId && empty($selectedWoMaterials))
                @php $selectedWo = $linkedWorkOrders->firstWhere('id', $selectedWorkOrderId); @endphp
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-center text-gray-400">
                    No se registró consumo de material en {{ $selectedWo->code ?? 'OT-'.$selectedWo->id }}
                </div>
            @else
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-center text-gray-400">
                    Seleccioná una OT vinculada arriba para ver su material
                </div>
            @endif

            {{-- 6. Timeline de cambios --}}
            @if(!empty($selectedWoMaterials) && collect($selectedWoMaterials)->pluck('notes')->filter()->isNotEmpty())
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">history</span>
                        Historial de ajustes
                    </h3>
                    <div class="space-y-2">
                        @foreach($selectedWoMaterials as $mat)
                            @if(!empty($mat['notes']))
                                <div class="flex items-start gap-2 p-2 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                                    <span class="material-symbols-outlined text-gray-400 text-base mt-0.5">edit_note</span>
                                    <div>
                                        <p class="text-gray-800">
                                            <span class="font-medium">{{ $mat['product']['name'] }}</span>:
                                            {{ $mat['notes'] }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($mat['updated_at'])->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($showEditModal && $requisition->status === 'open')
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-2xl">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">edit</span>
                            Ajustar material
                        </h3>
                        <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                        @foreach($editingMaterials as $index => $edit)
                            @php
                                $pct = $edit['quantity_requested'] > 0
                                    ? round(($edit['quantity_used'] / $edit['quantity_requested']) * 100)
                                    : 0;
                            @endphp
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800">{{ $edit['product_name'] }} ({{ $edit['product_sku'] }})</p>
                                    <span class="text-xs text-gray-500">Solicitado: {{ $edit['quantity_requested'] }}</span>
                                </div>

                                {{-- Barra de progreso --}}
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-300
                                        {{ $pct >= 100 ? 'bg-green-500' : ($pct > 0 ? 'bg-blue-500' : 'bg-gray-300') }}"
                                        style="width: {{ min(100, $pct) }}%">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">{{ $pct }}% utilizado</p>

                                <div class="grid grid-cols-2 gap-3 mt-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Cantidad utilizada</label>
                                        <input type="number" wire:model="editingMaterials.{{ $index }}.quantity_used"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                            min="0">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Motivo del ajuste</label>
                                        <input type="text" wire:model="editingMaterials.{{ $index }}.motivo"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                            placeholder="¿Por qué se ajusta?">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                        <button wire:click="closeEditModal"
                            class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                            Cancelar
                        </button>
                        <button wire:click="saveEditModal"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">save</span>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>
