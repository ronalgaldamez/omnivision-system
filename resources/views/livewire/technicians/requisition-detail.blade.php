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
            {{-- OTs Vinculadas (Verde) --}}
            @if($linkedWorkOrders->isNotEmpty())
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-green-700 mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-green-500 text-base">link</span>
                        OTs vinculadas a esta requisición
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($linkedWorkOrders as $wo)
                            <a href="{{ route('work-orders.show', $wo->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium hover:bg-green-100 transition">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                {{ $wo->code ?? 'OT-'.$wo->id }} - {{ $wo->client->name ?? 'N/A' }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- OTs NO Vinculadas (Rojo) --}}
            @if($unlinkedWorkOrders->isNotEmpty())
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-red-700 mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-red-500 text-base">link_off</span>
                        OTs sin vincular (disponibles)
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($unlinkedWorkOrders as $wo)
                            <a href="{{ route('work-orders.show', $wo->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium hover:bg-red-100 transition">
                                <span class="material-symbols-outlined text-sm">radio_button_unchecked</span>
                                {{ $wo->code ?? 'OT-'.$wo->id }} - {{ $wo->client->name ?? 'N/A' }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Si no hay ninguna OT --}}
            @if($linkedWorkOrders->isEmpty() && $unlinkedWorkOrders->isEmpty())
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">No hay órdenes de trabajo asociadas.</p>
                </div>
            @endif

            <h3 class="text-md font-semibold text-gray-800 mb-3">Material utilizado</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Solicitado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Utilizado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Ajuste</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($requisition->items as $item)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-4 py-3 text-gray-800">{{ $item->product->name }} ({{ $item->product->sku }})</td>
                                <td class="px-4 py-3 text-center font-mono">{{ $item->quantity_requested }}</td>
                                <td class="px-4 py-3 text-center font-mono">
                                    <input type="number"
                                           wire:model.lazy="items.{{ $item->id }}.quantity_used"
                                           wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                           class="w-16 text-center rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                           min="0" max="{{ $item->quantity_requested }}">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button wire:click="decrement({{ $item->id }})"
                                                class="p-1 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                                            <span class="material-symbols-outlined text-base">remove</span>
                                        </button>
                                        <button wire:click="increment({{ $item->id }})"
                                                class="p-1 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                                            <span class="material-symbols-outlined text-base">add</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                <p>* El inventario en tu poder se actualiza automáticamente al modificar las cantidades.</p>
            </div>
        </div>
    </div>

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