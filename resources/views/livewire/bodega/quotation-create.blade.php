<div class="max-w-6xl mx-auto">
    <x-ui.card title="Nueva Cotización" icon="request_quote" subtitle="Solicitud de compra a proveedor (queda pendiente de aprobación)">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('bodega.quotations.index') }}">Volver</x-ui.button>
        </x-slot:headerActions>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-gray-500">warehouse</span>
                    <h2 class="text-sm font-semibold text-gray-800">Proveedor</h2>
                </div>
                <div class="p-5">
                    @if ($supplier_id && $supplier = \App\Models\Supplier::find($supplier_id))
                        <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $supplier->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">NIT: {{ $supplier->nit ?? 'N/A' }}</p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" wire:click="openSupplierModal"
                                    class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 rounded-lg">Cambiar</button>
                                <button type="button" wire:click="clearSupplier"
                                    class="p-1.5 text-green-600 hover:text-red-600 rounded-lg"><span class="material-symbols-outlined text-lg">close</span></button>
                            </div>
                        </div>
                    @else
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" wire:model.live.debounce.300ms="supplierSearch" placeholder="Buscar proveedor por nombre o NIT..."
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                @if (count($supplierResults) > 0)
                                    <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100">
                                        @foreach ($supplierResults as $supplier)
                                            <li wire:click="selectSupplier({{ $supplier->id }})"
                                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm flex items-center justify-between">
                                                <span class="font-medium text-gray-800">{{ $supplier->name }}</span>
                                                <span class="text-xs text-gray-500">NIT: {{ $supplier->nit ?? 'N/A' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <button type="button" wire:click="openSupplierModal"
                                class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 transition whitespace-nowrap">
                                <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                <span class="hidden sm:inline">Ver todos</span>
                            </button>
                        </div>
                    @endif
                    @error('supplier_id')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        <h2 class="text-sm font-semibold text-gray-800">Productos a cotizar</h2>
                        @if (count($items) > 0)
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ count($items) }} agregado(s)</span>
                        @endif
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 space-y-3">
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Buscar producto por nombre o SKU..."
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            @if (count($productResults) > 0)
                                <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100">
                                    @foreach ($productResults as $p)
                                        <li wire:click="selectProduct({{ $p->id }})"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm flex items-center justify-between">
                                            <span class="font-medium text-gray-800">{{ $p->name }}</span>
                                            <span class="text-xs text-gray-500 font-mono">{{ $p->sku }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        @if ($selectedProductId)
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ $selectedProductName }}</p>
                                    <p class="text-xs text-blue-600 font-mono">{{ $selectedProductSku }}</p>
                                </div>
                                <button type="button" wire:click="clearSelectedProduct" class="text-xs text-blue-600 hover:text-blue-800">Cambiar</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                    <input type="number" step="1" min="1" wire:model="currentQuantity"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo unitario ($)</label>
                                    <input type="number" step="0.01" min="0" wire:model="currentUnitCost"
                                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" wire:click="addItem"
                                    class="inline-flex items-center gap-1.5 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                    <span class="material-symbols-outlined text-base">{{ $editingIndex !== null ? 'update' : 'add_circle' }}</span>
                                    {{ $editingIndex !== null ? 'Actualizar producto' : 'Agregar producto' }}
                                </button>
                            </div>
                        @endif
                    </div>

                    @if (count($items) > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-gray-600 font-semibold text-xs uppercase tracking-wider">Producto</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Costo unit.</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($items as $index => $item)
                                        <tr>
                                            <td class="px-4 py-3 text-gray-800">{{ $item['product_name'] }}</td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format($item['quantity'], 4), '0'), '.') }}</td>
                                            <td class="px-4 py-3 text-center font-mono">${{ number_format($item['unit_cost'], 2) }}</td>
                                            <td class="px-4 py-3 text-center font-medium font-mono">${{ number_format($item['quantity'] * $item['unit_cost'], 2) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button" wire:click="editItem({{ $index }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><span class="material-symbols-outlined text-lg">edit</span></button>
                                                    <button type="button" wire:click="removeItem({{ $index }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"><span class="material-symbols-outlined text-lg">delete</span></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end">
                            <div class="w-full sm:w-72 space-y-2 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Subtotal</span><span class="font-mono">${{ number_format($totals['subtotal'], 2) }}</span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">IVA (13%)</span><span class="font-mono">${{ number_format($totals['iva'], 2) }}</span></div>
                                <div class="flex justify-between font-semibold border-t border-gray-200 pt-2"><span>Total</span><span class="font-mono">${{ number_format($totals['total'], 2) }}</span></div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                            <span class="material-symbols-outlined text-gray-300 text-4xl">inventory_2</span>
                            <p class="text-gray-500 mt-2">Agregá productos a la cotización</p>
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea wire:model="notes" rows="2" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <x-ui.button variant="secondary" href="{{ route('bodega.quotations.index') }}">Cancelar</x-ui.button>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm hover:from-blue-700 hover:to-blue-800 transition">
                    <span class="material-symbols-outlined text-base">request_quote</span> Guardar cotización
                </button>
            </div>
        </form>
    </x-ui.card>

    {{-- Modal proveedores --}}
    <div x-data="{ show: @entangle('showSupplierModal') }" x-show="show" x-cloak x-transition:enter="ease-out duration-200"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100" class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold">Seleccionar proveedor</h3>
                    <button type="button" wire:click="closeSupplierModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg"><span class="material-symbols-outlined text-xl">close</span></button>
                </div>
                <div class="p-4 border-b">
                    <input type="text" wire:model.live.debounce.300ms="supplierListSearch" placeholder="Filtrar proveedores..." class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm">
                </div>
                <div class="p-2 max-h-80 overflow-y-auto">
                    @forelse($supplierList as $supplier)
                        <button type="button" wire:click="selectSupplier({{ $supplier->id }})" class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl text-sm flex items-center justify-between">
                            <span class="text-gray-800">{{ $supplier->name }}</span>
                            <span class="text-xs text-gray-500">NIT: {{ $supplier->nit ?? 'N/A' }}</span>
                        </button>
                    @empty
                        <div class="py-8 text-center text-gray-500">Sin resultados</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
