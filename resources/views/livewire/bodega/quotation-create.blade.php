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
                                <x-ui.button variant="secondary" size="sm" wire:click="openSupplierModal">Cambiar</x-ui.button>
                                <x-ui.button variant="ghost" size="sm" icon="close" wire:click="clearSupplier"></x-ui.button>
                            </div>
                        </div>
                    @else
                        <x-forms.group name="supplier_id" label="Proveedor" required>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                                        placeholder="Buscar proveedor por nombre o NIT..."
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
                                <x-ui.button variant="secondary" icon="format_list_bulleted" wire:click="openSupplierModal">Ver todos</x-ui.button>
                            </div>
                            @error('supplier_id')
                                <x-forms.error>{{ $message }}</x-forms.error>
                            @enderror
                        </x-forms.group>
                    @endif
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
                        <x-forms.group name="productSearch" label="Buscar producto">
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="productSearch"
                                    placeholder="Buscar por nombre o SKU..."
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
                        </x-forms.group>

                        @if ($selectedProductId)
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ $selectedProductName }}</p>
                                    <p class="text-xs text-blue-600 font-mono">{{ $selectedProductSku }}</p>
                                </div>
                                <x-ui.button variant="ghost" size="sm" wire:click="clearSelectedProduct">Cambiar</x-ui.button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-ui.input type="number" icon="123" wire:model="currentQuantity" label="Cantidad" min="1" step="1" />
                                <x-ui.input type="number" icon="attach_money" wire:model="currentUnitCost" label="Costo unitario ($)" min="0" step="0.01" />
                            </div>
                            <div class="flex justify-end">
                                <x-ui.button variant="primary" :icon="$editingIndex !== null ? 'update' : 'add_circle'" wire:click="addItem">
                                    {{ $editingIndex !== null ? 'Actualizar producto' : 'Agregar producto' }}
                                </x-ui.button>
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
                                                    <x-ui.button variant="ghost" size="sm" icon="edit" wire:click="editItem({{ $index }})">Editar</x-ui.button>
                                                    <x-ui.button variant="ghost" size="sm" icon="delete" wire:click="removeItem({{ $index }})">Eliminar</x-ui.button>
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

            <x-ui.textarea icon="sticky_note_2" wire:model="notes" label="Notas" rows="2" placeholder="Comentarios de la cotización" />

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <x-ui.button variant="secondary" href="{{ route('bodega.quotations.index') }}">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="request_quote">Guardar cotización</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    {{-- Modal proveedores --}}
    <div x-data="{ show: @entangle('showSupplierModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">business</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar proveedor</h3>
                            <p class="text-xs text-gray-500">Elegí un proveedor de la lista</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSupplierModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="supplierListSearch" placeholder="Filtrar proveedores..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-80 overflow-y-auto">
                    @forelse($supplierList as $supplier)
                        <button type="button" wire:click="selectSupplier({{ $supplier->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl text-sm flex items-center justify-between">
                            <span class="text-gray-800">{{ $supplier->name }}</span>
                            <span class="text-xs text-gray-500">NIT: {{ $supplier->nit ?? 'N/A' }}</span>
                        </button>
                    @empty
                        <div class="py-8 text-center text-gray-500">Sin resultados</div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <x-ui.button variant="secondary" wire:click="closeSupplierModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
