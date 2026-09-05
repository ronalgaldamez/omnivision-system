<div class="max-w-6xl mx-auto">
    <x-ui.card title="Nueva Cotización" icon="request_quote" subtitle="Solicitud de compra a proveedor (queda pendiente de aprobación)">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('bodega.quotations.index') }}">Volver</x-ui.button>
        </x-slot:headerActions>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-gray-500">tune</span>
                    <h2 class="text-sm font-semibold text-gray-800">Tipo de cotización</h2>
                </div>
                <div class="p-5">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('mode', 'single')"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $mode === 'single' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Cotización normal
                        </button>
                        <button type="button" wire:click="$set('mode', 'multiple')"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $mode === 'multiple' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Cotización múltiple (varios proveedores)
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        @if($mode === 'single')
                            Una cotización para un solo proveedor.
                        @else
                            Elegís el proveedor de cada producto y al guardar se crea una cotización separada por proveedor.
                        @endif
                    </p>
                </div>
            </div>

            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-gray-500">warehouse</span>
                    <h2 class="text-sm font-semibold text-gray-800">{{ $mode === 'multiple' ? 'Proveedor (del próximo producto)' : 'Proveedor' }}</h2>
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
                        <div>
                            <x-forms.label icon="warehouse" required>Proveedor</x-forms.label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                                        placeholder="Buscar proveedor por nombre o NIT..."
                                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border shadow-sm focus:ring-2 focus:ring-blue-500/20 transition text-sm {{ $errors->has('supplier_id') ? 'border-red-300 bg-red-50 focus:border-red-400 focus:bg-white' : 'border-gray-300 bg-white focus:border-blue-500' }}">
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
                        </div>
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
                    @if(!$createMode)
                        <x-ui.button variant="secondary" size="sm" icon="add" wire:click="activateCreateMode">Nuevo producto</x-ui.button>
                    @endif
                </div>
                <div class="p-5 space-y-4">
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 space-y-3">
                        @if($createMode)
                            <div class="space-y-3 p-4 bg-green-50 rounded-lg border border-green-200 ring-1 ring-green-100">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-600 text-lg">add_circle</span>
                                    <p class="text-sm font-semibold text-green-800">Nuevo producto (aún no existe en el catálogo)</p>
                                </div>
                                <div class="grid grid-cols-1 gap-3">
                                    <input type="text" wire:model.live.debounce.500ms="newProductName"
                                        class="w-full px-3 py-2 rounded-lg border bg-white text-sm focus:ring-2 transition {{ $errors->has('newProductName') ? 'border-red-300 bg-red-50 focus:ring-red-500/20 focus:border-red-400' : 'border-gray-300 focus:ring-green-500/20 focus:border-green-500' }}"
                                        placeholder="Nombre del producto">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad de medida</label>
                                            <select wire:model="newProductUnit"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition">
                                                @foreach ($units as $u)
                                                    <option value="{{ $u->code }}">{{ $u->name }}{{ $u->symbol ? ' ('.$u->symbol.')' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Categoría</label>
                                            @if($newProductCategoryId && $selCat = \App\Models\Category::find($newProductCategoryId))
                                                <div class="flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                                                    <span class="text-sm text-blue-800 flex-1">{{ $selCat->name }}</span>
                                                    <button type="button" wire:click="clearNewProductCategory" class="p-1 text-blue-600 hover:text-red-600 rounded">
                                                        <span class="material-symbols-outlined text-lg">close</span>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="relative">
                                                    <input type="text" wire:model.live.debounce.300ms="newProductCategorySearch" placeholder="Buscar categoría..."
                                                        class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition">
                                                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                                                    @if(count($newProductCategoryResults) > 0)
                                                        <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-48 overflow-auto">
                                                            @foreach($newProductCategoryResults as $cat)
                                                                <li wire:click="selectNewProductCategory({{ $cat->id }})" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{{ $cat->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-ui.input type="number" icon="123" wire:model="currentQuantity" label="Cantidad" min="1" step="1" />
                                    <x-ui.input type="number" icon="attach_money" wire:model="currentUnitCost" label="Costo unitario ($)" min="0" step="0.01" />
                                </div>
                                <div class="flex justify-end gap-2">
                                    <x-ui.button variant="secondary" size="sm" wire:click="cancelCreateMode">Cancelar</x-ui.button>
                                    <x-ui.button variant="primary" size="sm" icon="add_circle" wire:click="addItem">Agregar a la cotización</x-ui.button>
                                </div>
                            </div>
                        @else
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
                        @endif
                    </div>

                    @if (count($items) > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-gray-600 font-semibold text-xs uppercase tracking-wider">Producto</th>
                                        @if($mode === 'multiple')
                                            <th class="px-4 py-3 text-left text-gray-600 font-semibold text-xs uppercase tracking-wider">Proveedor</th>
                                        @endif
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Costo unit.</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($items as $index => $item)
                                        <tr>
                                            <td class="px-4 py-3 text-gray-800">
                                                {{ $item['product_name'] }}
                                                @if(empty($item['product_id']))
                                                    <span class="text-xs text-amber-600 font-medium">· nuevo</span>
                                                @endif
                                            </td>
                                            @if($mode === 'multiple')
                                                <td class="px-4 py-3 text-gray-600 text-xs">{{ \App\Models\Supplier::find($item['supplier_id'] ?? null)?->name ?? '—' }}</td>
                                            @endif
                                            <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format($item['quantity'], 4), '0'), '.') }}</td>
                                            <td class="px-4 py-3 text-center font-mono">${{ number_format($item['unit_cost'], 2) }}</td>
                                            <td class="px-4 py-3 text-center font-medium font-mono">${{ number_format($item['quantity'] * $item['unit_cost'], 2) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <x-ui.button variant="ghost" size="sm" icon="edit" wire:click="editItem({{ $index }})">Editar</x-ui.button>
                                                    <x-ui.button variant="ghost" size="sm" icon="delete" wire:click="askRemoveItem({{ $index }})">Eliminar</x-ui.button>
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
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">warehouse</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar proveedor</h3>
                            <p class="text-xs text-gray-500">Elegí un proveedor de la lista</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSupplierModal" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="supplierListSearch"
                            placeholder="Filtrar por nombre, NIT o NRC..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($supplierList as $supplier)
                        <button type="button" wire:click="selectSupplier({{ $supplier->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-blue-600">business</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ $supplier->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if($supplier->nit)NIT: {{ $supplier->nit }}@endif
                                        @if($supplier->email) · {{ $supplier->email }}@endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                @if($supplier->phones && count($supplier->phones) > 0)
                                    <span class="text-xs text-gray-400 hidden sm:inline">{{ $supplier->phones[0] }}</span>
                                @endif
                                <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-lg">chevron_right</span>
                            </div>
                        </button>
                    @empty
                        <div class="py-12 text-center">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">search_off</span>
                            <p class="text-gray-500 text-sm">No se encontraron proveedores</p>
                            <p class="text-xs text-gray-400 mt-1">Probá con otro término de búsqueda</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <x-ui.button variant="secondary" wire:click="closeSupplierModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmación eliminar producto --}}
    @if($confirmingRemoveIndex !== null)
        <x-ui.confirm-modal variant="danger" icon="delete" title="Eliminar producto de la cotización"
            message="¿Eliminar este producto de la lista? Podrás volver a agregarlo después."
            confirmLabel="Sí, eliminar" cancelLabel="Cancelar"
            confirmAction="removeItem" cancelAction="cancelRemoveItem" id="confirm-remove-item" />
    @endif

    {{-- Modal confirmación de guardado --}}
    @if($confirmingSave)
        <x-ui.confirm-modal variant="primary" icon="request_quote" title="Guardar cotización"
            message="¿Estás seguro de generar esta cotización? Quedará pendiente de aprobación."
            confirmLabel="Sí, guardar" cancelLabel="Cancelar"
            confirmAction="confirmSave" cancelAction="cancelSave" id="confirm-save-quotation" />
    @endif
</div>
