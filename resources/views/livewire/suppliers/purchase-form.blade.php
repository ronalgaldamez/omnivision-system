<div class="max-w-6xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">add_shopping_cart</span>
                        Nueva Compra
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Registra una nueva factura de adquisición</p>
                </div>
                <a href="{{ route('purchases.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6">
            <form wire:submit.prevent="save" class="space-y-6">
                <!-- Datos de compra -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Proveedor con búsqueda -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">warehouse</span>
                            Proveedor *
                        </label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                                placeholder="Buscar por nombre, NIT o NRC..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        </div>
                        @if(count($supplierResults) > 0)
                            <ul
                                class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach($supplierResults as $supplier)
                                    <li wire:click="selectSupplier({{ $supplier->id }})"
                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-gray-800">{{ $supplier->name }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">NIT: {{ $supplier->nit ?? 'N/A' }} | NRC:
                                            {{ $supplier->nrc ?? 'N/A' }}</span>
                                    </li>
                                 @endforeach
                             </ul>
                        @endif
                        @error('supplier_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Número de factura -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">receipt</span>
                            Número de factura *
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="invoice_number"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="Ej: FAC-001">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                        </div>
                        @error('invoice_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Fecha de compra -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                            Fecha de compra *
                        </label>
                        <div class="relative">
                            <input type="date" wire:model="purchase_date"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                        </div>
                        @error('purchase_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                            Notas
                        </label>
                        <div class="relative">
                            <textarea wire:model="notes" rows="1"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                                placeholder="Notas o comentarios de la compra"></textarea>
                            <span
                                class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                        </div>
                    </div>
                </div>

                <!-- Agregar productos -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Productos de la compra
                        </h2>
                        @if(!$createMode)
                        <button type="button" wire:click="activateCreateMode"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                            <span class="material-symbols-outlined text-base">add_box</span>
                            Nuevo Producto
                        </button>
                        @endif
                    </div>

                    <!-- Campos para agregar un producto -->
                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        {{-- Fila 1: Producto (buscar o crear) --}}
                        @if($createMode)
                        <div class="space-y-2 p-3 bg-green-50 rounded-lg border border-green-100 mb-3">
                            <p class="text-xs font-medium text-green-700">Nuevo producto</p>
                            <div class="flex gap-2">
                                <input type="text" wire:model.live.debounce.500ms="newProductName" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm" placeholder="Nombre del producto">
                                <button type="button" wire:click="createProduct" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition whitespace-nowrap">Crear</button>
                                <button type="button" wire:click="cancelCreateMode" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cancelar</button>
                            </div>
                            @error('newProductName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @else
                        <div class="relative mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Buscar producto</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                    placeholder="Buscar por nombre o SKU..."
                                    class="w-full px-3 pr-9 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                @if($currentProductId)
                                <button type="button" wire:click="clearProductSelection"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition"
                                    title="Limpiar selección">
                                    <span class="material-symbols-outlined text-lg">close</span>
                                </button>
                                @endif
                            </div>
                            @if(count($productSearchResults) > 0)
                                <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                    @foreach($productSearchResults as $result)
                                        <li wire:click="selectProduct({{ $result->id }})"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                            <span class="font-medium text-gray-800">{{ $result->name }}</span>
                                            <span class="text-xs text-gray-500">({{ $result->sku }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            @error('currentProductId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        {{-- Fila 2: Empaque --}}
                        @if($currentProductId)
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Empaque</label>
                            @if(count($currentPackagings) > 0)
                            <div class="space-y-2">
                                <select wire:model.live="currentPackagingId" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                                    @foreach($currentPackagings as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }} → x{{ rtrim(rtrim(number_format($pkg->quantity_in_base_unit, 4), '0'), '.') }}</option>
                                    @endforeach
                                </select>
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="editPackaging({{ $currentPackagingId }})"
                                        class="text-xs text-blue-600 hover:text-blue-800 transition inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">edit</span> Editar
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" wire:click="deletePackaging({{ $currentPackagingId }})"
                                        class="text-xs text-red-500 hover:text-red-700 transition inline-flex items-center gap-1"
                                        onclick="return confirm('¿Eliminar este empaque?')">
                                        <span class="material-symbols-outlined text-sm">delete</span> Eliminar
                                    </button>
                                </div>
                            </div>

                            @if($showPackagingForm)
                            <div class="space-y-2 p-3 bg-blue-50 rounded-lg border border-blue-100 mt-2">
                                <p class="text-xs font-medium text-blue-700">{{ $editingPackagingId ? 'Editando empaque' : 'Nuevo empaque' }}</p>
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                        <select wire:model.live="newPackagingTypeId" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                            <option value="">Seleccioná...</option>
                                            @foreach($packagingTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">¿Cuántas unidades trae?</label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="newPackagingQuantity" class="w-44 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm" min="1">
                                    </div>
                                    <button type="button" wire:click="savePackaging" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap mb-0.5">{{ $editingPackagingId ? 'Actualizar' : 'Guardar' }}</button>
                                    <button type="button" wire:click="cancelEditPackaging" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition mb-0.5">Cancelar</button>
                                </div>
                            </div>
                            @else
                            <button type="button" wire:click="editPackaging(0)"
                                class="mt-1 text-xs text-blue-600 hover:text-blue-800 transition">
                                + Agregar empaque
                            </button>
                            @endif
                            @else
                            <div class="space-y-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-blue-700">Sin empaques. Definí uno:</p>
                                    <button type="button" x-data @click="$dispatch('open-help-modal')" class="text-blue-500 hover:text-blue-700 transition inline-flex items-center gap-1 text-xs">
                                        <span class="material-symbols-outlined text-sm">help</span> ¿Cómo empaquetar?
                                    </button>
                                </div>
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                        <select wire:model.live="newPackagingTypeId" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                            <option value="">Seleccioná...</option>
                                            @foreach($packagingTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">¿Cuántas unidades trae?</label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="newPackagingQuantity" class="w-44 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm" min="1">
                                    </div>
                                    <button type="button" wire:click="savePackaging" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap mb-0.5">Guardar</button>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Fila 3: Cantidad y Costo --}}
                        @if($currentProductId)
                        @php $pkg = $this->selectedPackaging; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Cantidad {{ $pkg ? '(' . strtolower($pkg->packagingType?->name ?? 'paquetes') . 's)' : '' }}
                                </label>
                                <input type="number" step="1" wire:model.live.debounce.500ms="currentQuantity"
                                    x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                @if($pkg)
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span class="font-mono" x-data="{ qty: @entangle('currentQuantity') }" x-text="(qty || 0) + ' × ' + {{ $pkg->quantity_in_base_unit }} + ' = ' + ((qty || 0) * {{ $pkg->quantity_in_base_unit }})"></span> unidades totales
                                    </p>
                                @endif
                                @error('currentQuantity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Costo unitario</label>
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="currentUnitCost"
                                    x-on:keydown="if(!/^[0-9.]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                @error('currentUnitCost') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        {{-- Empaque fraccionado --}}
                        @if($pkg)
                        <div class="bg-amber-50/60 rounded-lg border border-amber-200 p-3 space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="hasFractional"
                                    class="rounded border-amber-300 text-amber-600 shadow-sm focus:ring-2 focus:ring-amber-500/20">
                                <span class="text-sm font-medium text-amber-800">Empaque fraccionado</span>
                                <span class="text-xs text-amber-600">(algunas {{ strtolower($pkg->packagingType?->name ?? 'cajas') }} vienen con menos unidades)</span>
                            </label>
                            @if($hasFractional)
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        Cajas fraccionadas <span class="text-amber-600">(de {{ $currentQuantity }})</span>
                                    </label>
                                    <input type="number" step="1" min="1" wire:model.live.debounce.500ms="fractionalQuantity"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                    @error('fractionalQuantity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        ¿Cuántas unidades faltan en cada una?
                                    </label>
                                    <input type="number" step="1" min="1" wire:model.live.debounce.500ms="fractionalUnits"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                    <p class="text-xs text-amber-600 mt-1 font-mono" x-data="{ units: @entangle('fractionalUnits'), pkgUnits: {{ $pkg->quantity_in_base_unit }} }"
                                        x-text="units > 0 ? 'Le faltan ' + units + ' unidades (trae ' + (pkgUnits - units) + ' en vez de ' + pkgUnits + ')' : ''"></p>
                                    @error('fractionalUnits') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <p class="text-xs text-amber-700 font-mono" x-data="{ cqty: @entangle('currentQuantity'), fqty: @entangle('fractionalQuantity'), funits: @entangle('fractionalUnits'), pkgUnits: {{ $pkg->quantity_in_base_unit }} }"
                                x-text="'Total: ' + cqty + ' × ' + pkgUnits + ' - ' + fqty + ' × ' + funits + ' = ' + (cqty * pkgUnits - fqty * funits) + ' unidades'"></p>
                            @endif
                        </div>
                        @endif
                        {{-- Fin empaque fraccionado --}}
                        <div class="mt-3 flex justify-end gap-2">
                            @if($editingIndex !== null)
                            <button type="button" wire:click="cancelEdit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg bg-white hover:bg-gray-50 transition shadow-sm">
                                <span class="material-symbols-outlined text-base">undo</span>
                                Cancelar edición
                            </button>
                            @endif
                            <button type="button" wire:click="addItem"
                                wire:loading.attr="disabled" wire:target="addItem"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                {{ $editingIndex !== null ? 'Actualizar producto' : 'Agregar producto' }}
                            </button>
                        </div>
                        @endif

                    <!-- Tabla de productos agregados -->
                    @if(count($items) > 0)
                        <div class="mt-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-500 text-base">list_alt</span>
                                    Productos agregados ({{ count($items) }})
                                </h3>
                            </div>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Empaque</th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Costo unit.</th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Total</th>
                                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($items as $index => $item)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="px-4 py-3 text-gray-800">
                                                    {{ $item['product_name'] }}
                                                    <span class="text-gray-500 text-xs ml-1">({{ $item['product_sku'] }})</span>
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-700">{{ $item['quantity'] }}</td>
                                                <td class="px-4 py-3 text-center text-gray-700 text-xs">
                                                    {{ $item['packaging_name'] ?? '—' }}
                                                    @if(!empty($item['fractional_quantity']))
                                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs font-medium ml-1">
                                                            <span class="material-symbols-outlined" style="font-size:12px">broken_image</span>
                                                            Fraccionado
                                                        </span>
                                                    @endif
                                                    @if(!empty($item['base_quantity']) && $item['base_quantity'] != $item['quantity'])
                                                        <span class="block text-gray-400">→ {{ rtrim(rtrim(number_format($item['base_quantity'], 4), '0'), '.') }} un.</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-700">
                                                    ${{ number_format($item['unit_cost'], 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-medium text-gray-800">
                                                    ${{ number_format(($item['base_quantity'] ?? $item['quantity']) * $item['unit_cost'], 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button type="button" wire:click="confirmAction('edit', {{ $index }})"
                                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                            title="Editar">
                                                            <span class="material-symbols-outlined text-lg">edit</span>
                                                        </button>
                                                        <button type="button" wire:click="confirmAction('delete', {{ $index }})"
                                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                                            title="Eliminar">
                                                            <span class="material-symbols-outlined text-lg">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50 border-t border-gray-200">
                                            <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-700">
                                                Total compra:
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold text-gray-800">
                                                ${{ number_format(array_sum(array_map(fn($i) => ($i['base_quantity'] ?? $i['quantity']) * $i['unit_cost'], $items)), 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Resumen de pago -->
                            <div class="flex justify-end">
                                <div class="w-full sm:w-72 space-y-3 p-4 bg-gray-50/80 rounded-xl border border-gray-200">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium">${{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="includeIva"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                            <span class="text-gray-600">Incluye IVA (13%)</span>
                                        </label>
                                        <span class="font-medium">${{ number_format($ivaAmount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-base font-semibold border-t border-gray-200 pt-3">
                                        <span>Total a Pagar</span>
                                        <span>${{ number_format($total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-12 text-center mt-5">
                            <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                            <p class="text-gray-500">No hay productos agregados.</p>
                            <p class="text-sm text-gray-400 mt-1">Usa el formulario para agregar productos a la compra.</p>
                        </div>
                    @endif
                    @error('items') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="confirmAction('reset_form')"
                        class="px-5 py-2.5 border border-red-200 rounded-lg text-sm font-medium text-red-600 bg-white hover:bg-red-50 transition shadow-sm">
                        Limpiar todo
                    </button>
                    <a href="{{ route('purchases.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación de acción (editar/eliminar) -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $modalMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="executeAction"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                        Sí, continuar
                    </button>
                    <button @click="show = false" wire:click="closeModal"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de guardado -->
    <div x-data="{ showSaveModal: false }" x-on:confirm-save.window="showSaveModal = true" x-show="showSaveModal"
        x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">shopping_cart_checkout</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar registro</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        ¿Estás seguro de registrar esta compra? Los movimientos de stock se actualizarán
                        automáticamente.
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="confirmSave"
                        class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                        Sí, registrar compra
                    </button>
                    <button @click="showSaveModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal asignación a estanterías --}}
    @if($showShelfModal)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-2xl" @@click.away="open = false">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">shelves</span>
                        Asignar productos a estanterías
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Seleccioná dónde se guardará cada producto. Podés omitir este paso.</p>
                </div>
                <div class="px-6 pt-3">
                    <p class="text-xs text-gray-400">Las estanterías raíz son solo referencia. Seleccioná un contenedor hijo (con código indentado) para asignar.</p>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto space-y-4">
                    @foreach($shelfAssignments as $index => $assign)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $assign['product_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $assign['product_sku'] }} · {{ $assign['quantity'] }} unidades</p>
                            </div>
                            @if($assign['current_shelves'])
                            <span class="text-xs text-gray-400">Ya en: {{ $assign['current_shelves'] }}</span>
                            @endif
                        </div>
                        <select wire:model="shelfAssignments.{{ $index }}.shelf_id"
                            class="w-full py-2 rounded-lg border border-gray-300 bg-white text-sm">
                            <option value="">— Sin asignar —</option>
                            @foreach($allShelves as $shelf)
                            @if($shelf['is_root'])
                            <option disabled class="text-gray-400 bg-gray-50">{{ $shelf['display'] }}</option>
                            @else
                            <option value="{{ $shelf['id'] }}" {{ $shelf['is_full'] ? 'disabled' : '' }}>{{ $shelf['display'] }}</option>
                            @endif
                            @endforeach
                        </select>
                        @php $selectedShelf = collect($allShelves)->firstWhere('id', $shelfAssignments[$index]['shelf_id']); @endphp
                        @if($selectedShelf && $selectedShelf['is_full'])
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">warning</span>
                            Esta ubicación está marcada como llena. Seleccioná otra.
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-between">
                    <button type="button" wire:click="skipShelfAssignment"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                        Omitir — asignar después
                    </button>
                    <button type="button" wire:click="saveShelfAssignments"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Guardar asignación
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
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

    {{-- Modal guía de empaques --}}
    <div x-data="{ show: false }" x-on:open-help-modal.window="show = true" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" style="display:none">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6 max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto" @click.away="show = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Guía de empaques</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <p class="text-sm text-gray-500 mb-4">Cada empaque define cuántas unidades base contiene. Al comprar, el sistema multiplica automáticamente.</p>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left text-gray-600"><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Ejemplo</th><th class="px-3 py-2">Trae</th><th class="px-3 py-2">Resultado</th></tr></thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr><td class="px-3 py-1.5 font-medium">Caja</td><td class="px-3 py-1.5">Router TP-Link</td><td class="px-3 py-1.5">20</td><td class="px-3 py-1.5 text-xs text-gray-500">Caja x20</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Bolsa</td><td class="px-3 py-1.5">Conectores RJ45</td><td class="px-3 py-1.5">100</td><td class="px-3 py-1.5 text-xs text-gray-500">Bolsa x100</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Rollo</td><td class="px-3 py-1.5">Cable UTP Cat6</td><td class="px-3 py-1.5">305</td><td class="px-3 py-1.5 text-xs text-gray-500">Rollo x305 (metros)</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Paquete</td><td class="px-3 py-1.5">Filtros de señal</td><td class="px-3 py-1.5">50</td><td class="px-3 py-1.5 text-xs text-gray-500">Paquete x50</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Pallet</td><td class="px-3 py-1.5">Antenas parabólicas</td><td class="px-3 py-1.5">12</td><td class="px-3 py-1.5 text-xs text-gray-500">Pallet x12</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Unidad</td><td class="px-3 py-1.5">Modem individual</td><td class="px-3 py-1.5">1</td><td class="px-3 py-1.5 text-xs text-gray-500">Unidad x1</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Saco</td><td class="px-3 py-1.5">Aislantes térmicos</td><td class="px-3 py-1.5">30</td><td class="px-3 py-1.5 text-xs text-gray-500">Saco x30</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Bobina</td><td class="px-3 py-1.5">Fibra óptica</td><td class="px-3 py-1.5">1000</td><td class="px-3 py-1.5 text-xs text-gray-500">Bobina x1000 (metros)</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Blister</td><td class="px-3 py-1.5">Adaptadores USB</td><td class="px-3 py-1.5">5</td><td class="px-3 py-1.5 text-xs text-gray-500">Blister x5</td></tr>
                    <tr><td class="px-3 py-1.5 font-medium">Sobre</td><td class="px-3 py-1.5">Tornillería fina</td><td class="px-3 py-1.5">200</td><td class="px-3 py-1.5 text-xs text-gray-500">Sobre x200</td></tr>
                </tbody>
            </table>
            <p class="text-xs text-gray-400 mt-3">Ejemplo: comprás 3 Cajas de Router, cada Caja trae 20 → entran 60 routers al inventario.</p>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>