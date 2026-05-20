<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">receipt</span>
                    Requisición #{{ $requisition->id }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $requisition->created_at->format('d/m/Y') }} - Estado: {{ $requisition->status }}
                </p>
            </div>
            <a href="{{ route('technician.requisitions.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                ← Volver al listado
            </a>
        </div>

        <div class="p-6">
            @if($requisition->workOrders->isNotEmpty())
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Órdenes de Trabajo relacionadas:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($workOrders as $wo)
                            <button wire:click="selectWorkOrder({{ $wo['id'] }})"
                                class="px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition
                                            {{ $wo['hasConsumption'] ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                {{ $wo['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <h3 class="text-md font-semibold text-gray-800 mb-3">Materiales de la Requisición</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Solicitado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Utilizado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">En inventario</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($requisition->items as $item)
                            @php
                                // Inventario actual del técnico para este producto
                                $inventory = \App\Models\TechnicianInventory::where('technician_id', auth()->id())
                                    ->where('product_id', $item->product_id)
                                    ->value('quantity_in_hand') ?? 0;

                                $disponible = $item->quantity_requested - $item->quantity_used;
                                if ($disponible <= 0) {
                                    $badgeColor = 'bg-red-100 text-red-700';
                                    $estado = 'Agotado en esta req.';
                                } elseif ($disponible <= ($item->quantity_requested * 0.2)) {
                                    $badgeColor = 'bg-yellow-100 text-yellow-700';
                                    $estado = 'Stock bajo en esta req.';
                                } else {
                                    $badgeColor = 'bg-green-100 text-green-700';
                                    $estado = 'Disponible en esta req.';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-4 py-3 text-gray-800">{{ $item->product->name }} ({{ $item->product->sku }})
                                </td>
                                <td class="px-4 py-3 text-center font-mono">{{ $item->quantity_requested }}</td>
                                <td class="px-4 py-3 text-center font-mono">{{ $item->quantity_used }}</td>
                                <td
                                    class="px-4 py-3 text-center font-mono font-semibold {{ $inventory > 0 ? 'text-blue-700' : 'text-gray-400' }}">
                                    {{ $inventory }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                <p>* Selecciona una OT para registrar o editar el material consumido en ella. Rojo = ya tiene consumo,
                    Verde = sin consumo.</p>
                <p>* El inventario en tu poder se actualiza automáticamente.</p>
            </div>
        </div>
    </div>

    {{-- Modal de consumo por OT --}}
    @if($showConsumptionModal && $selectedWorkOrder)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-2xl">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Material usado en OT #{{ $selectedWorkOrder }}
                        </h3>
                        <button wire:click="closeConsumptionModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        @if($editMode)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de la modificación *</label>
                                <input type="text" wire:model="editReason"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                    placeholder="¿Por qué modificas el consumo?">
                                @error('editReason') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="space-y-3">
                            @foreach($consumptionProducts as $index => $product)
                                <div class="flex items-center justify-between gap-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">{{ $product['product_name'] }}</p>
                                        <div class="flex gap-3 text-xs text-gray-500 mt-1">
                                            <span>Solicitado: {{ $product['quantity_requested'] }}</span>
                                            <span>En inventario: {{ $product['inventory'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <label class="text-xs text-gray-500">Usado</label>
                                        <input type="number" min="0" max="{{ $product['max_allowed'] }}" step="any"
                                            wire:model="consumptionQuantities.{{ $index }}"
                                            class="w-20 text-center rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm py-1.5">
                                        @error("consumptionQuantities.{$index}")
                                            <span class="text-xs text-red-500 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="saveConsumption"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Guardar
                        </button>
                        <button wire:click="closeConsumptionModal"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300" style="display: none;">
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
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>