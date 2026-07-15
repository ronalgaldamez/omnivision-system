<div class="max-w-5xl mx-auto">
    <x-ui.card>
        <x-slot:headerActions>
            <a href="{{ route('technician.requisitions.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Volver</a>
        </x-slot:headerActions>

        {{-- 1. Header info --}}
        <div class="flex items-center gap-4 mb-6">
            <span class="material-symbols-outlined text-gray-500">receipt</span>
            <div>
                <h1 class="text-lg font-semibold text-gray-800">Requisición #{{ $requisition->id }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $requisition->created_at->format('d/m/Y') }} — Estado:
                    @php
                        $statusMap = ['open' => ['Abierta', 'success'], 'heredada' => ['Heredada', 'warning'], 'closed' => ['Cerrada', 'neutral'], 'pending' => ['Pendiente', 'warning'], 'approved' => ['Aprobada', 'info'], 'rejected' => ['Rechazada', 'danger']];
                        $s = $statusMap[$requisition->status] ?? [$requisition->status, 'neutral'];
                    @endphp
                    <x-ui.badge :variant="$s[1]">{{ $s[0] }}</x-ui.badge>
                    @if($requisition->technician)
                        — Técnico: {{ $requisition->technician->name }}
                    @endif
                </p>
            </div>
        </div>

        {{-- 2. Resumen general --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
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
            <div
                class="p-3 {{ $hasWeeklyClose ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border">
                <p class="text-xs {{ $hasWeeklyClose ? 'text-green-600' : 'text-gray-500' }} font-medium">Cierre semanal
                </p>
                <p class="text-lg font-mono font-bold {{ $hasWeeklyClose ? 'text-green-700' : 'text-gray-400' }} mt-1">
                    {{ $hasWeeklyClose ? 'Realizado' : 'Pendiente' }}
                </p>
            </div>
        </div>

        {{-- 3. Notas --}}
        @if($requisition->notes)
            <x-ui.alert variant="warning" class="mb-6">
                <span class="font-medium">Notas:</span> {{ $requisition->notes }}
            </x-ui.alert>
        @endif

        {{-- 4. OTs Vinculadas --}}
        @if($linkedWorkOrders->isNotEmpty())
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-500 text-base">link</span>
                    OTs vinculadas
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($linkedWorkOrders as $wo)
                            <button wire:click="selectWorkOrder({{ $wo->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition border
                                                                            {{ $selectedWorkOrderId === $wo->id
                        ? 'bg-blue-100 text-blue-700 border-blue-300 shadow-sm'
                        : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' }}">
                                {{ $wo->code ?? 'OT-' . $wo->id }} — {{ $wo->client->name ?? 'N/A' }}
                            </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 5. OTS sin vincular --}}
        @if($unlinkedWorkOrders->isNotEmpty())
            <div class="mb-4">
                <h3 class="text-sm font-medium text-red-700 mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-red-500 text-base">link_off</span>
                    OTs disponibles para vincular
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($unlinkedWorkOrders as $wo)
                        <div
                            class="inline-flex items-center gap-1 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium border border-red-200">
                            <span>{{ $wo->code ?? 'OT-' . $wo->id }} — {{ $wo->client->name ?? 'N/A' }}</span>
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
            <x-ui.alert variant="neutral" class="mb-4">
                No hay órdenes de trabajo asociadas.
            </x-ui.alert>
        @endif

        {{-- 6. Material de la OT seleccionada --}}
        @if($selectedWorkOrderId && !empty($selectedWoMaterials))
            @php $selectedWo = $linkedWorkOrders->firstWhere('id', $selectedWorkOrderId); @endphp
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-md font-semibold text-gray-800">
                        Material de {{ $selectedWo->code ?? 'OT-' . $selectedWo->id }}
                    </h3>
                    @if($requisition->status === 'open')
                        <x-ui.button variant="warning" icon="edit" wire:click="openEditModal" size="sm">
                            Ajustar
                        </x-ui.button>
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
                                    <td class="px-4 py-3 text-gray-800">{{ $mat['product_name'] }} ({{ $mat['product_sku'] }})
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono">{{ $mat['quantity_requested'] }}</td>
                                    <td
                                        class="px-4 py-3 text-center font-mono {{ $mat['quantity_used'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ $mat['quantity_used'] > 0 ? $mat['quantity_used'] : '—' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center font-mono text-sm {{ $stockQty > 0 ? 'text-amber-600' : 'text-gray-400' }}">
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
            <x-ui.alert variant="neutral" class="mb-6">
                No se registró consumo de material en {{ $selectedWo->code ?? 'OT-' . $selectedWo->id }}
            </x-ui.alert>
        @else
            <x-ui.alert variant="neutral" class="mb-6">
                Seleccioná una OT vinculada arriba para ver su material
            </x-ui.alert>
        @endif

        {{-- 7. Timeline de cambios --}}
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
    </x-ui.card>

    {{-- Edit Modal --}}
    @if($showEditModal && $requisition->status === 'open')
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-2xl">
                <x-ui.card overflow="visible">
                    <x-slot:headerActions>
                        <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>

                    <div class="max-h-[60vh] overflow-y-auto space-y-4">
                        @foreach($editingMaterials as $index => $edit)
                            @php
                                $pct = $edit['quantity_requested'] > 0
                                    ? round(($edit['quantity_used'] / $edit['quantity_requested']) * 100)
                                    : 0;
                            @endphp
                            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800">{{ $edit['product_name'] }}
                                        ({{ $edit['product_sku'] }})</p>
                                    <span class="text-xs text-gray-500">Solicitado: {{ $edit['quantity_requested'] }}</span>
                                </div>

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
                                        <x-ui.input type="number" wire:model="editingMaterials.{{ $index }}.quantity_used"
                                            min="0" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Motivo del ajuste</label>
                                        <x-ui.input type="text" wire:model="editingMaterials.{{ $index }}.motivo"
                                            placeholder="¿Por qué se ajusta?" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <x-slot:footer>
                        <div class="flex justify-end gap-3">
                            <x-ui.button variant="secondary" wire:click="closeEditModal">Cancelar</x-ui.button>
                            <x-ui.button variant="primary" icon="save" wire:click="saveEditModal">Guardar
                                cambios</x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"
                class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"
                class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'info'"
            class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span> <span x-text="toastMessage"
                class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>