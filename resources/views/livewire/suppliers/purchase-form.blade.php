<div class="max-w-6xl mx-auto py-6">
    {{-- Stepper de progreso --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold">1</div>
                <span class="text-sm font-medium text-gray-800 hidden sm:inline">Datos de compra</span>
            </div>
            <div class="w-12 h-px bg-gray-300 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full {{ count($items) > 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center text-sm font-semibold transition-colors">2</div>
                <span class="text-sm font-medium {{ count($items) > 0 ? 'text-gray-800' : 'text-gray-500' }} hidden sm:inline transition-colors">Productos</span>
            </div>
            <div class="w-12 h-px bg-gray-300 mx-2"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full {{ count($items) > 0 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center text-sm font-semibold transition-colors">3</div>
                <span class="text-sm font-medium {{ count($items) > 0 ? 'text-gray-800' : 'text-gray-500' }} hidden sm:inline transition-colors">Confirmar</span>
            </div>
        </div>
        <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('purchases.index') }}">Volver</x-ui.button>
    </div>

    <x-ui.card title="Nueva Compra" icon="add_shopping_cart" subtitle="Registra una nueva factura de adquisición">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- ═══════════ SECCIÓN 1: DATOS DE COMPRA ═══════════ --}}
            <section x-data="{ open: true }" class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-gray-50/80 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-gray-500">description</span>
                        <h2 class="text-sm font-semibold text-gray-800">Datos de la compra</h2>
                        <span class="text-xs text-gray-400 bg-white px-2 py-0.5 rounded-full border border-gray-200">Paso 1</span>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="open" x-collapse class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Proveedor --}}
                        <div>
                            <x-forms.label icon="warehouse" required>Proveedor</x-forms.label>
                            @if ($supplier_id && $supplier = \App\Models\Supplier::find($supplier_id))
                            <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                                <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $supplier->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        NIT: {{ $supplier->nit ?? 'N/A' }}
                                        @if($supplier->phones && count($supplier->phones) > 0) · {{ $supplier->phones[0] }} @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button type="button" wire:click="openSupplierModal"
                                        class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
                                    <button type="button" wire:click="clearSupplier"
                                        class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar proveedor">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="flex gap-2" x-data="{ focused: false }">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                                        @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                        placeholder="Buscar por nombre, NIT o NRC..."
                                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if (count($supplierResults) > 0)
                                        <ul x-show="focused" x-transition
                                            class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                            @foreach ($supplierResults as $supplier)
                                                <li wire:click="selectSupplier({{ $supplier->id }})"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                    <div>
                                                        <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $supplier->name }}</span>
                                                    </div>
                                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">NIT: {{ $supplier->nit ?? 'N/A' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <button type="button" wire:click="openSupplierModal"
                                    class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                    title="Ver todos los proveedores">
                                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                    <span class="hidden sm:inline">Ver todos</span>
                                </button>
                            </div>
                            @endif
                            @error('supplier_id')
                                <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                            @enderror
                        </div>

                        <x-ui.input type="text" icon="receipt_long" wire:model="invoice_number" label="Número de factura" required placeholder="Ej: FAC-001" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input type="date" icon="event" wire:model="purchase_date" label="Fecha de compra" required />
                        <x-ui.textarea icon="edit_note" wire:model="notes" label="Notas" placeholder="Notas o comentarios de la compra" />
                    </div>
                </div>
            </section>

            {{-- ═══════════ SECCIÓN 2: PRODUCTOS ═══════════ --}}
            <section class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        <h2 class="text-sm font-semibold text-gray-800">Productos de la compra</h2>
                        <span class="text-xs text-gray-400 bg-white px-2 py-0.5 rounded-full border border-gray-200">Paso 2</span>
                        @if (count($items) > 0)
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ count($items) }} agregado(s)</span>
                        @endif
                    </div>
                    @if (!$createMode)
                        <x-ui.button variant="primary" icon="add" wire:click="activateCreateMode">Nuevo Producto</x-ui.button>
                    @endif
                </div>

                <div class="p-5">
                    {{-- Panel de agregar producto --}}
                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 p-4 space-y-4">
                        {{-- Modo crear producto --}}
                        @if ($createMode)
                            <div class="space-y-3 p-4 bg-green-50 rounded-lg border border-green-200 ring-1 ring-green-100">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-600 text-lg">add_circle</span>
                                    <p class="text-sm font-semibold text-green-800">Crear nuevo producto</p>
                                </div>
                                <div class="grid grid-cols-1 gap-3">
                                    <input type="text" wire:model.live.debounce.500ms="newProductName"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition"
                                        placeholder="Nombre del producto">
                                    @error('newProductName')
                                        <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                    @enderror
                                    <div>
                                        @if($newProductCategoryId && $selCat = \App\Models\Category::find($newProductCategoryId))
                                        <div class="flex items-center gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                                            <span class="material-symbols-outlined text-blue-600 text-sm">check_circle</span>
                                            <span class="text-sm text-blue-800 flex-1">{{ $selCat->name }}</span>
                                            <button type="button" wire:click="clearNewProductCategory" class="p-1 text-blue-600 hover:text-red-600 rounded">
                                                <span class="material-symbols-outlined text-lg">close</span>
                                            </button>
                                        </div>
                                        @else
                                        <div class="flex gap-2">
                                            <div class="relative flex-1">
                                                <input type="text" wire:model.live.debounce.300ms="newProductCategorySearch" placeholder="Buscar categoría..."
                                                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition">
                                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                                                @if(count($newProductCategoryResults) > 0)
                                                <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-48 overflow-auto">
                                                    @foreach($newProductCategoryResults as $cat)
                                                    <li wire:click="selectNewProductCategory({{ $cat->id }})" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm flex items-center justify-between">
                                                        <span>{{ $cat->name }}</span>
                                                        @if($cat->requires_device_registration)
                                                            <span class="text-xs text-blue-600 font-medium">Requiere MAC</span>
                                                        @endif
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="openNewProductCategoryModal"
                                                class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 transition whitespace-nowrap">
                                                <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                            </button>
                                        </div>
                                        @endif
                                        @error('newProductCategoryId')
                                            <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <x-ui.button variant="secondary" wire:click="cancelCreateMode">Cancelar</x-ui.button>
                                    <x-ui.button variant="primary" wire:click="createProduct">Crear producto</x-ui.button>
                                </div>
                            </div>
                        @else
                            {{-- Buscar producto --}}
                            <div>
                                <x-forms.label icon="search">Buscar producto existente</x-forms.label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                            placeholder="Buscar por nombre o SKU..."
                                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                        @if (count($productSearchResults) > 0)
                                            <ul class="absolute z-20 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100">
                                                @foreach ($productSearchResults as $result)
                                                    <li wire:click="selectProduct({{ $result->id }})"
                                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                        <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $result->name }}</span>
                                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $result->sku }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="openProductListModal"
                                        class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                        title="Ver todos los productos">
                                        <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                        <span class="hidden sm:inline">Ver todos</span>
                                    </button>
                                </div>
                                @error('currentProductId')
                                    <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Campos de producto seleccionado --}}
                        @if ($currentProductId)
                            <div class="space-y-4 pt-2 border-t border-gray-200">
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-blue-600">check_circle</span>
                                        <span class="text-sm font-medium text-blue-900">Producto seleccionado</span>
                                    </div>
                                    <button type="button" wire:click="clearProductSelection" class="text-xs text-blue-600 hover:text-blue-800 transition">Cambiar</button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-forms.label icon="123">Cantidad</x-forms.label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="currentQuantity"
                                            x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        @error('currentQuantity')
                                            <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <x-forms.label icon="attach_money">Costo unitario</x-forms.label>
                                        <input type="number" step="0.01" wire:model.live.debounce.500ms="currentUnitCost"
                                            x-on:keydown="if(!/^[0-9.]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        @error('currentUnitCost')
                                            <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                                    @if ($editingIndex !== null)
                                        <x-ui.button variant="secondary" icon="undo" wire:click="cancelEdit">Cancelar</x-ui.button>
                                    @endif
                                    <button type="button" wire:click="addItem" wire:loading.attr="disabled" wire:target="addItem"
                                        class="inline-flex items-center gap-1.5 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span class="material-symbols-outlined text-base" wire:loading.remove wire:target="addItem">
                                            {{ $editingIndex !== null ? 'update' : 'add_circle' }}
                                        </span>
                                        <span wire:loading wire:target="addItem" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                                        {{ $editingIndex !== null ? 'Actualizar producto' : 'Agregar producto' }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════ TABLA DE PRODUCTOS ═══════════ --}}
                    @if (count($items) > 0)
                        <div class="mt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-500 text-base">list_alt</span>
                                    Productos agregados
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ count($items) }}</span>
                                </h3>
                            </div>

                            {{-- Desktop: Tabla --}}
                            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
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
                                            <tr class="hover:bg-blue-50/30 transition group">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                            <span class="material-symbols-outlined text-blue-600 text-base">inventory_2</span>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-800 font-medium">{{ $item['product_name'] }}</p>
                                                            <p class="text-xs text-gray-500 font-mono">{{ $item['product_sku'] }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-800 rounded-md text-sm font-medium">{{ $item['quantity'] }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-700 font-mono">${{ number_format($item['unit_cost'], 2) }}</td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-800 font-mono">${{ number_format($item['quantity'] * $item['unit_cost'], 2) }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-1 opacity-60 group-hover:opacity-100 transition">
                                                        <button type="button" wire:click="confirmAction('edit', {{ $index }})"
                                                            class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Editar">
                                                            <span class="material-symbols-outlined text-lg">edit</span>
                                                        </button>
                                                        <button type="button" wire:click="confirmAction('delete', {{ $index }})"
                                                            class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition" title="Eliminar">
                                                            <span class="material-symbols-outlined text-lg">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gradient-to-r from-blue-50 to-white border-t-2 border-blue-200">
                                            <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">Total compra:</td>
                                            <td class="px-4 py-3 text-center font-bold text-blue-700 text-base font-mono">${{ number_format(array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $items)), 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Mobile: Cards --}}
                            <div class="md:hidden space-y-3">
                                @foreach ($items as $index => $item)
                                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800 text-sm">{{ $item['product_name'] }}</p>
                                                    <p class="text-xs text-gray-500 font-mono">{{ $item['product_sku'] }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" wire:click="confirmAction('edit', {{ $index }})" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button type="button" wire:click="confirmAction('delete', {{ $index }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div class="bg-gray-50 rounded p-2">
                                                <p class="text-gray-500">Cantidad</p>
                                                <p class="font-semibold text-gray-800">{{ $item['quantity'] }}</p>
                                            </div>
                                            <div class="bg-gray-50 rounded p-2">
                                                <p class="text-gray-500">Costo unit.</p>
                                                <p class="font-semibold text-gray-800 font-mono">${{ number_format($item['unit_cost'], 2) }}</p>
                                            </div>
                                            <div class="col-span-2 bg-blue-50 rounded p-2 flex items-center justify-between">
                                                <span class="text-gray-700 font-medium">Total</span>
                                                <span class="font-bold text-blue-700 font-mono">${{ number_format($item['quantity'] * $item['unit_cost'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="bg-gradient-to-r from-blue-50 to-white border-2 border-blue-200 rounded-xl p-4 flex items-center justify-between">
                                    <span class="font-semibold text-gray-700">Total compra</span>
                                    <span class="font-bold text-blue-700 text-lg font-mono">${{ number_format(array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $items)), 2) }}</span>
                                </div>
                            </div>

                            {{-- Resumen de pago --}}
                            <div class="flex justify-end">
                                <div class="w-full sm:w-80 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-5 py-3 bg-gray-100/60 border-b border-gray-200">
                                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-500 text-base">receipt</span> Resumen
                                        </h4>
                                    </div>
                                    <div class="p-5 space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotal</span>
                                            <span class="font-medium text-gray-800 font-mono">${{ number_format($subtotal, 2) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="checkbox" wire:model.live="includeIva"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                                <span class="text-gray-600 group-hover:text-blue-600 transition">Incluye IVA (13%)</span>
                                            </label>
                                            <span class="font-medium text-gray-800 font-mono">${{ number_format($ivaAmount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between pt-3 border-t-2 border-gray-200">
                                            <span class="font-bold text-gray-800">Total a Pagar</span>
                                            <span class="font-bold text-blue-700 text-lg font-mono">${{ number_format($total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 bg-gradient-to-br from-gray-50 to-white rounded-xl border-2 border-dashed border-gray-300 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-400 text-3xl">inventory_2</span>
                            </div>
                            <p class="text-gray-600 font-medium">No hay productos agregados</p>
                            <p class="text-sm text-gray-400 mt-1">Usa el formulario de arriba para agregar productos a la compra.</p>
                        </div>
                    @endif
                    @error('items')
                        <span class="text-xs text-red-500 mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                    @enderror
                </div>
            </section>

            {{-- ═══════════ BOTONES DE ACCIÓN ═══════════ --}}
            <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-3 pt-4 border-t border-gray-200">
                <button type="button" wire:click="confirmAction('reset_form')"
                    class="w-full sm:w-auto px-5 py-2.5 border border-red-200 rounded-lg text-sm font-medium text-red-600 bg-white hover:bg-red-50 transition shadow-sm inline-flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">delete_sweep</span> Limpiar todo
                </button>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <x-ui.button variant="secondary" href="{{ route('purchases.index') }}">Cancelar</x-ui.button>
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm hover:from-blue-700 hover:to-blue-800 transition inline-flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span> Registrar Compra
                    </button>
                </div>
            </div>
        </form>
    </x-ui.card>

    {{-- ═══════════ MODALES ═══════════ --}}
    {{-- Modal de lista de proveedores --}}
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

    {{-- Modal de confirmación --}}
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $modalMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="primary" wire:click="executeAction">Sí, continuar</x-ui.button>
                    <button @click="show = false" wire:click="closeModal"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de productos --}}
    <div x-data="{ show: @entangle('showProductListModal') }" x-show="show" x-cloak
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
                            <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar producto</h3>
                            <p class="text-xs text-gray-500">Elegí un producto de la lista</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeProductListModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="productListSearch"
                            placeholder="Filtrar por nombre o SKU..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($productList as $p)
                        <button type="button" wire:click="selectProductFromList({{ $p->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-blue-600">inventory_2</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ $p->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $p->sku }}</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-lg flex-shrink-0">chevron_right</span>
                        </button>
                    @empty
                        <div class="py-12 text-center text-gray-500 text-sm">No se encontraron productos</div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <x-ui.button variant="secondary" wire:click="closeProductListModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de categorías --}}
    <div x-data="{ show: @entangle('newProductShowCategoryModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Seleccionar categoría</h3>
                    <button type="button" wire:click="closeNewProductCategoryModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="newProductCategoryListSearch"
                            placeholder="Filtrar categorías..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-72 overflow-y-auto">
                    @forelse($newProductCategoryList as $cat)
                        <button type="button" wire:click="selectNewProductCategory({{ $cat->id }})"
                            class="w-full text-left px-4 py-2.5 hover:bg-blue-50 rounded-lg transition text-sm flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="material-symbols-outlined text-gray-400 text-lg flex-shrink-0">category</span>
                                <span class="text-gray-800 truncate">{{ $cat->name }}</span>
                            </div>
                            @if($cat->requires_device_registration)
                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-xs">settings_ethernet</span> Requiere MAC
                                </span>
                            @endif
                        </button>
                    @empty
                        <div class="py-8 text-center text-gray-500 text-sm">No se encontraron categorías</div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <x-ui.button variant="secondary" wire:click="closeNewProductCategoryModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación de guardado --}}
    <div x-data="{ showSaveModal: false }" x-on:confirm-save.window="showSaveModal = true" x-show="showSaveModal"
        x-cloak x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="showSaveModal" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">shopping_cart_checkout</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar registro</h3>
                    <p class="text-sm text-gray-600 mt-2">¿Estás seguro de registrar esta compra? Los movimientos de stock se actualizarán automáticamente.</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="primary" wire:click="confirmSave" class="!bg-green-600 hover:!bg-green-700">Sí, registrar compra</x-ui.button>
                    <button @click="showSaveModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal asignación a estanterías --}}
    @if ($showShelfModal)
        <div x-data="{ open: true }" x-show="open" x-cloak x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            style="display: none;">
            <div x-show="open" x-transition:enter="ease-out duration-200 delay-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-2xl" @@click.away="open = false">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">shelves</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Asignar productos a estanterías</h3>
                                <p class="text-xs text-gray-500">Seleccioná dónde se guardará cada producto</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pt-3">
                        <p class="text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500 text-sm">info</span>
                            Las estanterías raíz son solo referencia. Seleccioná un contenedor hijo (con código indentado).
                        </p>
                    </div>
                    <div class="p-6 max-h-96 overflow-y-auto space-y-3">
                        @foreach ($shelfAssignments as $index => $assign)
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 hover:border-blue-300 transition">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-blue-600 text-base">inventory_2</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $assign['product_name'] }}</p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $assign['product_sku'] }} · {{ $assign['quantity'] }} unidades</p>
                                        </div>
                                    </div>
                                    @if ($assign['current_shelves'])
                                        <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">Ya en: {{ $assign['current_shelves'] }}</span>
                                    @endif
                                </div>
                                <select wire:model="shelfAssignments.{{ $index }}.shelf_id"
                                    class="w-full py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">— Sin asignar —</option>
                                    @foreach ($allShelves as $shelf)
                                        @if ($shelf['is_root'])
                                            <option disabled class="text-gray-400 bg-gray-50">{{ $shelf['display'] }}</option>
                                        @else
                                            <option value="{{ $shelf['id'] }}" {{ $shelf['is_full'] ? 'disabled' : '' }}>{{ $shelf['display'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @php $selectedShelf = collect($allShelves)->firstWhere('id', $shelfAssignments[$index]['shelf_id']); @endphp
                                @if ($selectedShelf && $selectedShelf['is_full'])
                                    <p class="text-xs text-red-600 mt-2 flex items-center gap-1 bg-red-50 px-2 py-1 rounded border border-red-100">
                                        <span class="material-symbols-outlined text-sm">warning</span> Esta ubicación está marcada como llena.
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-between border-t border-gray-200">
                        <x-ui.button variant="secondary" wire:click="skipShelfAssignment">Omitir</x-ui.button>
                        <x-ui.button variant="primary" icon="save" wire:click="saveShelfAssignments">Guardar asignación</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal guía de empaques --}}
    <div x-data="{ show: false }" x-on:open-help-modal.window="show = true" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display:none">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl shadow-2xl border border-gray-200 max-w-lg w-full max-h-[80vh] overflow-hidden flex flex-col"
            @click.away="show = false">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">help</span>
                    <h3 class="text-base font-semibold text-gray-800">Guía de empaques</h3>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-100 rounded transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <p class="text-sm text-gray-600 mb-4 bg-blue-50 border border-blue-100 rounded-lg p-3">
                    Cada empaque define cuántas unidades base contiene. Al comprar, el sistema multiplica automáticamente.
                </p>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider">
                                <th class="px-3 py-2 font-semibold">Tipo</th>
                                <th class="px-3 py-2 font-semibold">Ejemplo</th>
                                <th class="px-3 py-2 font-semibold text-center">Unidad</th>
                                <th class="px-3 py-2 font-semibold text-center">Trae</th>
                                <th class="px-3 py-2 font-semibold">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($packagingTypes as $pt)
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">{{ $pt->name }}</td>
                                <td class="px-3 py-2">Ejemplo de {{ strtolower($pt->name) }}</td>
                                <td class="px-3 py-2 text-center font-mono">{{ $pt->unit_of_measure !== 'unidad' ? $pt->unit_of_measure : '—' }}</td>
                                <td class="px-3 py-2 text-center font-mono">{{ rtrim(rtrim(number_format($pt->packagings->first()?->quantity_in_base_unit ?? 0, 4), '0'), '.') }}</td>
                                <td class="px-3 py-2 text-xs text-gray-500">{{ $pt->name }} x{{ rtrim(rtrim(number_format($pt->packagings->first()?->quantity_in_base_unit ?? 0, 4), '0'), '.') }}{{ $pt->unit_of_measure !== 'unidad' ? ' ('.$pt->unit_of_measure.')' : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-3 bg-amber-50 border border-amber-100 rounded-lg p-3 flex items-start gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-base flex-shrink-0">lightbulb</span>
                    <span><strong>Ejemplo:</strong> comprás 3 Cajas de Router, cada Caja trae 20 → entran 60 routers al inventario.</span>
                </p>
            </div>
        </div>
    </div>
</div>
