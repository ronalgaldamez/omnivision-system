<div class="max-w-6xl mx-auto py-6">
    {{-- Stepper de progreso --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold">
                    1</div>
                <span class="text-sm font-medium text-gray-800 hidden sm:inline">Datos de compra</span>
            </div>
            <div class="w-12 h-px bg-gray-300 mx-2"></div>
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-full {{ count($items) > 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center text-sm font-semibold transition-colors">
                    2</div>
                <span
                    class="text-sm font-medium {{ count($items) > 0 ? 'text-gray-800' : 'text-gray-500' }} hidden sm:inline transition-colors">Productos</span>
            </div>
            <div class="w-12 h-px bg-gray-300 mx-2"></div>
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-full {{ count($items) > 0 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center text-sm font-semibold transition-colors">
                    3</div>
                <span
                    class="text-sm font-medium {{ count($items) > 0 ? 'text-gray-800' : 'text-gray-500' }} hidden sm:inline transition-colors">Confirmar</span>
            </div>
        </div>
        <a href="{{ route('purchases.index') }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-blue-600 transition group">
            <span
                class="material-symbols-outlined text-base group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
            <span class="hidden sm:inline">Volver</span>
        </a>
    </div>

    {{-- Tarjeta principal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
        {{-- Encabezado --}}
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">add_shopping_cart</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Nueva Compra</h1>
                    <p class="text-sm text-gray-500">Registra una nueva factura de adquisición</p>
                </div>
            </div>
        </div>

        {{-- Contenido --}}
        <form wire:submit.prevent="save" class="p-6 space-y-6">

            {{-- ═══════════ SECCIÓN 1: DATOS DE COMPRA ═══════════ --}}
            <section x-data="{ open: true }" class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-gray-50/80 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-gray-500">description</span>
                        <h2 class="text-sm font-semibold text-gray-800">Datos de la compra</h2>
                        <span
                            class="text-xs text-gray-400 bg-white px-2 py-0.5 rounded-full border border-gray-200">Paso
                            1</span>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 transition-transform"
                        :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="open" x-collapse class="p-5 space-y-4">
                    {{-- Fila 1: Proveedor + Factura --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Proveedor --}}
                        <div class="relative" x-data="{ focused: false }">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">warehouse</span>
                                Proveedor <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="supplierSearch"
                                    @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                    placeholder="Buscar por nombre, NIT o NRC..."
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                @if ($supplier_id)
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                @endif
                            </div>
                            @if (count($supplierResults) > 0)
                                <ul x-show="focused" x-transition
                                    class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                    @foreach ($supplierResults as $supplier)
                                        <li wire:click="selectSupplier({{ $supplier->id }})"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                            <div>
                                                <span
                                                    class="font-medium text-gray-800 group-hover:text-blue-700">{{ $supplier->name }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">NIT:
                                                {{ $supplier->nit ?? 'N/A' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            @error('supplier_id')
                                <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                        class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Número de factura --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">receipt_long</span>
                                Número de factura <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="invoice_number"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                    placeholder="Ej: FAC-001">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                            </div>
                            @error('invoice_number')
                                <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                        class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Fila 2: Fecha + Notas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                Fecha de compra <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="date" wire:model="purchase_date"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                            </div>
                            @error('purchase_date')
                                <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                        class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                                Notas <span class="text-xs text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <div class="relative">
                                <textarea wire:model="notes" rows="1"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                                    placeholder="Notas o comentarios de la compra"></textarea>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ═══════════ SECCIÓN 2: PRODUCTOS ═══════════ --}}
            <section class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3.5 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        <h2 class="text-sm font-semibold text-gray-800">Productos de la compra</h2>
                        <span
                            class="text-xs text-gray-400 bg-white px-2 py-0.5 rounded-full border border-gray-200">Paso
                            2</span>
                        @if (count($items) > 0)
                            <span
                                class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ count($items) }}
                                agregado(s)</span>
                        @endif
                    </div>
                    @if (!$createMode)
                        <button type="button" wire:click="activateCreateMode"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Nuevo Producto
                        </button>
                    @endif
                </div>

                <div class="p-5">
                    {{-- Panel de agregar producto --}}
                    <div
                        class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 p-4 space-y-4">
                        {{-- Modo crear producto --}}
                        @if ($createMode)
                            <div class="space-y-3 p-4 bg-green-50 rounded-lg border border-green-200 ring-1 ring-green-100">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-600 text-lg">add_circle</span>
                                    <p class="text-sm font-semibold text-green-800">Crear nuevo producto</p>
                                </div>
                                <div class="flex gap-2">
                                    <input type="text" wire:model.live.debounce.500ms="newProductName"
                                        class="flex-1 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition"
                                        placeholder="Nombre del producto">
                                    <button type="button" wire:click="createProduct"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition whitespace-nowrap shadow-sm">
                                        Crear
                                    </button>
                                    <button type="button" wire:click="cancelCreateMode"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">
                                        Cancelar
                                    </button>
                                </div>
                                @error('newProductName')
                                    <span class="text-xs text-red-500 flex items-center gap-1"><span
                                            class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                @enderror
                            </div>
                        @else
                            {{-- Buscar producto --}}
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-600 mb-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">search</span>
                                    Buscar producto existente
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                        placeholder="Buscar por nombre o SKU..."
                                        class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if ($currentProductId)
                                        <button type="button" wire:click="clearProductSelection"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition"
                                            title="Limpiar selección">
                                            <span class="material-symbols-outlined text-lg">close</span>
                                        </button>
                                    @endif
                                </div>
                                @if (count($productSearchResults) > 0)
                                    <ul
                                        class="absolute z-20 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                        @foreach ($productSearchResults as $result)
                                            <li wire:click="selectProduct({{ $result->id }})"
                                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                <span
                                                    class="font-medium text-gray-800 group-hover:text-blue-700">{{ $result->name }}</span>
                                                <span
                                                    class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $result->sku }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @error('currentProductId')
                                    <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                            class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Campos de producto seleccionado --}}
                        @if ($currentProductId)
                            <div class="space-y-4 pt-2 border-t border-gray-200">
                                {{-- Producto seleccionado badge --}}
                                <div
                                    class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-blue-600">check_circle</span>
                                        <span class="text-sm font-medium text-blue-900">Producto seleccionado</span>
                                    </div>
                                    <button type="button" wire:click="clearProductSelection"
                                        class="text-xs text-blue-600 hover:text-blue-800 transition">Cambiar</button>
                                </div>

                                {{-- Empaque --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">inventory</span>
                                        Empaque
                                    </label>
                                    @if (count($currentPackagings) > 0)
                                        <div class="space-y-2">
                                            <select wire:model.live="currentPackagingId"
                                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                                @foreach ($currentPackagings as $pkg)
                                                    <option value="{{ $pkg->id }}">{{ $pkg->name }} →
                                                        x{{ rtrim(rtrim(number_format($pkg->quantity_in_base_unit, 4), '0'), '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="flex items-center gap-3">
                                                <button type="button" wire:click="editPackaging({{ $currentPackagingId }})"
                                                    class="text-xs text-blue-600 hover:text-blue-800 transition inline-flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">edit</span> Editar
                                                </button>
                                                <span class="text-gray-300">|</span>
                                                <button type="button" wire:click="deletePackaging({{ $currentPackagingId }})"
                                                    class="text-xs text-red-500 hover:text-red-700 transition inline-flex items-center gap-1"
                                                    onclick="return confirm('¿Eliminar este empaque?')">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Form empaque --}}
                                        @if ($showPackagingForm)
                                            <div
                                                class="space-y-3 p-4 bg-blue-50 rounded-lg border border-blue-200 mt-3 ring-1 ring-blue-100">
                                                <p class="text-xs font-semibold text-blue-800 flex items-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-sm">{{ $editingPackagingId ? 'edit' : 'add' }}</span>
                                                    {{ $editingPackagingId ? 'Editando empaque' : 'Nuevo empaque' }}
                                                </p>
                                                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-2 items-end">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                                        <select wire:model.live="newPackagingTypeId"
                                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                                            <option value="">Seleccioná...</option>
                                                            @foreach ($packagingTypes as $pt)
                                                                <option value="{{ $pt->id }}">
                                                                    {{ $pt->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Unidades</label>
                                                        <input type="number" step="1"
                                                            wire:model.live.debounce.500ms="newPackagingQuantity"
                                                            class="w-32 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm"
                                                            min="1">
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <button type="button" wire:click="savePackaging"
                                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap shadow-sm">
                                                            {{ $editingPackagingId ? 'Actualizar' : 'Guardar' }}
                                                        </button>
                                                        <button type="button" wire:click="cancelEditPackaging"
                                                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <button type="button" wire:click="editPackaging(0)"
                                                class="mt-2 text-xs text-blue-600 hover:text-blue-800 transition inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">add</span> Agregar
                                                empaque
                                            </button>
                                        @endif
                                    @else
                                        {{-- Sin empaques --}}
                                        <div
                                            class="space-y-3 p-4 bg-blue-50 rounded-lg border border-blue-200 ring-1 ring-blue-100">
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs font-semibold text-blue-800 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-sm">info</span>
                                                    Sin empaques. Definí uno:
                                                </p>
                                                <button type="button" x-data @click="$dispatch('open-help-modal')"
                                                    class="text-blue-600 hover:text-blue-800 transition inline-flex items-center gap-1 text-xs">
                                                    <span class="material-symbols-outlined text-sm">help</span> ¿Cómo
                                                    empaquetar?
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-2 items-end">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                                    <select wire:model.live="newPackagingTypeId"
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                                        <option value="">Seleccioná...</option>
                                                        @foreach ($packagingTypes as $pt)
                                                            <option value="{{ $pt->id }}">{{ $pt->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Unidades</label>
                                                    <input type="number" step="1"
                                                        wire:model.live.debounce.500ms="newPackagingQuantity"
                                                        class="w-32 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm"
                                                        min="1">
                                                </div>
                                                <button type="button" wire:click="savePackaging"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap shadow-sm">
                                                    Guardar
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                {{-- Stock thresholds --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1.5 flex items-center gap-1">
                                            <span
                                                class="material-symbols-outlined text-gray-400 text-sm">arrow_downward</span>
                                            Stock mínimo
                                        </label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="stockMin"
                                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                            placeholder="Dejar vacío para mantener actual">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1.5 flex items-center gap-1">
                                            <span
                                                class="material-symbols-outlined text-gray-400 text-sm">arrow_upward</span>
                                            Stock máximo
                                        </label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="stockMax"
                                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                            placeholder="Dejar vacío para mantener actual">
                                    </div>
                                </div>

                                {{-- Cantidad y Costo --}}
                                @php $pkg = $this->selectedPackaging; @endphp
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1.5 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-gray-400 text-sm">123</span>
                                            Cantidad
                                            {{ $pkg ? '(' . strtolower($pkg->packagingType?->name ?? 'paquetes') . 's)' : '' }}
                                        </label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="currentQuantity"
                                            x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        @if ($pkg)
                                            <p
                                                class="text-xs text-gray-500 mt-1.5 flex items-center gap-1 bg-blue-50 px-2 py-1 rounded border border-blue-100">
                                                <span class="material-symbols-outlined text-blue-500 text-sm">calculate</span>
                                                <span class="font-mono" x-data="{ qty: @entangle('currentQuantity') }"
                                                    x-text="(qty || 0) + ' × ' + {{ $pkg->quantity_in_base_unit }} + ' = ' + ((qty || 0) * {{ $pkg->quantity_in_base_unit }})"></span>
                                                <span class="text-blue-700 font-medium">unidades totales</span>
                                            </p>
                                        @endif
                                        @error('currentQuantity')
                                            <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                                    class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1.5 flex items-center gap-1">
                                            <span
                                                class="material-symbols-outlined text-gray-400 text-sm">attach_money</span>
                                            Costo unitario
                                        </label>
                                        <input type="number" step="0.01" wire:model.live.debounce.500ms="currentUnitCost"
                                            x-on:keydown="if(!/^[0-9.]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        @error('currentUnitCost')
                                            <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                                    class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Empaque fraccionado --}}
                                @if ($pkg)
                                    <div
                                        class="bg-amber-50/70 rounded-lg border border-amber-200 p-4 space-y-3 ring-1 ring-amber-100">
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" wire:model.live="hasFractional"
                                                class="rounded border-amber-400 text-amber-600 shadow-sm focus:ring-2 focus:ring-amber-500/30">
                                            <span class="material-symbols-outlined text-amber-600 text-lg">broken_image</span>
                                            <div class="flex-1">
                                                <span
                                                    class="text-sm font-semibold text-amber-900 group-hover:text-amber-700 transition">Empaque
                                                    fraccionado</span>
                                                <p class="text-xs text-amber-700">Algunas
                                                    {{ strtolower($pkg->packagingType?->name ?? 'cajas') }} vienen con
                                                    menos unidades
                                                </p>
                                            </div>
                                        </label>
                                        @if ($hasFractional)
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-amber-200">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Cajas fraccionadas <span class="text-amber-700">(de
                                                            {{ $currentQuantity }})</span>
                                                    </label>
                                                    <input type="number" step="1" min="1"
                                                        wire:model.live.debounce.500ms="fractionalQuantity"
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                                    @error('fractionalQuantity')
                                                        <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                                                class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        ¿Cuántas unidades faltan en cada una?
                                                    </label>
                                                    <input type="number" step="1" min="1"
                                                        wire:model.live.debounce.500ms="fractionalUnits"
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                                    <p class="text-xs text-amber-700 mt-1.5 font-mono bg-amber-100/50 px-2 py-1 rounded"
                                                        x-data="{ units: @entangle('fractionalUnits'), pkgUnits: {{ $pkg->quantity_in_base_unit }} }"
                                                        x-text="units > 0 ? 'Trae ' + (pkgUnits - units) + ' en vez de ' + pkgUnits : ''">
                                                    </p>
                                                    @error('fractionalUnits')
                                                        <span class="text-xs text-red-500 mt-1 flex items-center gap-1"><span
                                                                class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <p class="text-xs text-amber-800 font-mono bg-amber-100/50 px-3 py-2 rounded border border-amber-200"
                                                x-data="{ cqty: @entangle('currentQuantity'), fqty: @entangle('fractionalQuantity'), funits: @entangle('fractionalUnits'), pkgUnits: {{ $pkg->quantity_in_base_unit }} }"
                                                x-text="'Total: ' + cqty + ' × ' + pkgUnits + ' - ' + fqty + ' × ' + funits + ' = ' + (cqty * pkgUnits - fqty * funits) + ' unidades'">
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Botones agregar/actualizar --}}
                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                                    @if ($editingIndex !== null)
                                        <button type="button" wire:click="cancelEdit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg bg-white hover:bg-gray-50 transition shadow-sm">
                                            <span class="material-symbols-outlined text-base">undo</span>
                                            Cancelar
                                        </button>
                                    @endif
                                    <button type="button" wire:click="addItem" wire:loading.attr="disabled"
                                        wire:target="addItem"
                                        class="inline-flex items-center gap-1.5 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span class="material-symbols-outlined text-base" wire:loading.remove
                                            wire:target="addItem">
                                            {{ $editingIndex !== null ? 'update' : 'add_circle' }}
                                        </span>
                                        <span wire:loading wire:target="addItem"
                                            class="material-symbols-outlined text-base animate-spin">progress_activity</span>
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
                                    <span
                                        class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ count($items) }}</span>
                                </h3>
                            </div>

                            {{-- Desktop: Tabla --}}
                            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th
                                                class="px-4 py-3 text-left text-gray-600 font-semibold text-xs uppercase tracking-wider">
                                                Producto</th>
                                            <th
                                                class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">
                                                Cantidad</th>
                                            <th
                                                class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">
                                                Empaque</th>
                                            <th
                                                class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">
                                                Costo unit.</th>
                                            <th
                                                class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">
                                                Total</th>
                                            <th
                                                class="px-4 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wider">
                                                Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach ($items as $index => $item)
                                            <tr class="hover:bg-blue-50/30 transition group">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                            <span
                                                                class="material-symbols-outlined text-blue-600 text-base">inventory_2</span>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-800 font-medium">
                                                                {{ $item['product_name'] }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 font-mono">
                                                                {{ $item['product_sku'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-800 rounded-md text-sm font-medium">
                                                        {{ $item['quantity'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex flex-col items-center gap-1">
                                                        <span
                                                            class="text-xs text-gray-700">{{ $item['packaging_name'] ?? '—' }}</span>
                                                        @if (!empty($item['fractional_quantity']))
                                                            <span
                                                                class="inline-flex items-center gap-0.5 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                                                <span class="material-symbols-outlined"
                                                                    style="font-size:12px">broken_image</span>
                                                                Fracc.
                                                            </span>
                                                        @endif
                                                        @if (!empty($item['base_quantity']) && $item['base_quantity'] != $item['quantity'])
                                                            <span class="text-xs text-gray-500 font-mono">→
                                                                {{ rtrim(rtrim(number_format($item['base_quantity'], 4), '0'), '.') }}
                                                                un.</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-700 font-mono">
                                                    ${{ number_format($item['unit_cost'], 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-800 font-mono">
                                                    ${{ number_format(($item['base_quantity'] ?? $item['quantity']) * $item['unit_cost'], 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div
                                                        class="flex items-center justify-center gap-1 opacity-60 group-hover:opacity-100 transition">
                                                        <button type="button" wire:click="confirmAction('edit', {{ $index }})"
                                                            class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                                                            title="Editar">
                                                            <span class="material-symbols-outlined text-lg">edit</span>
                                                        </button>
                                                        <button type="button" wire:click="confirmAction('delete', {{ $index }})"
                                                            class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition"
                                                            title="Eliminar">
                                                            <span class="material-symbols-outlined text-lg">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gradient-to-r from-blue-50 to-white border-t-2 border-blue-200">
                                            <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700">
                                                Total compra:
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold text-blue-700 text-base font-mono">
                                                ${{ number_format(array_sum(array_map(fn($i) => ($i['base_quantity'] ?? $i['quantity']) * $i['unit_cost'], $items)), 2) }}
                                            </td>
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
                                                    <p class="font-medium text-gray-800 text-sm">
                                                        {{ $item['product_name'] }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 font-mono">
                                                        {{ $item['product_sku'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button type="button" wire:click="confirmAction('edit', {{ $index }})"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button type="button" wire:click="confirmAction('delete', {{ $index }})"
                                                    class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div class="bg-gray-50 rounded p-2">
                                                <p class="text-gray-500">Cantidad</p>
                                                <p class="font-semibold text-gray-800">{{ $item['quantity'] }}
                                                    {{ $item['packaging_name'] ?? '' }}
                                                </p>
                                            </div>
                                            <div class="bg-gray-50 rounded p-2">
                                                <p class="text-gray-500">Costo unit.</p>
                                                <p class="font-semibold text-gray-800 font-mono">
                                                    ${{ number_format($item['unit_cost'], 2) }}</p>
                                            </div>
                                            <div class="col-span-2 bg-blue-50 rounded p-2 flex items-center justify-between">
                                                <span class="text-gray-700 font-medium">Total</span>
                                                <span
                                                    class="font-bold text-blue-700 font-mono">${{ number_format(($item['base_quantity'] ?? $item['quantity']) * $item['unit_cost'], 2) }}</span>
                                            </div>
                                        </div>
                                        @if (!empty($item['fractional_quantity']))
                                            <span
                                                class="inline-flex items-center gap-1 mt-2 px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined" style="font-size:12px">broken_image</span>
                                                Empaque fraccionado
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-white border-2 border-blue-200 rounded-xl p-4 flex items-center justify-between">
                                    <span class="font-semibold text-gray-700">Total compra</span>
                                    <span class="font-bold text-blue-700 text-lg font-mono">
                                        ${{ number_format(array_sum(array_map(fn($i) => ($i['base_quantity'] ?? $i['quantity']) * $i['unit_cost'], $items)), 2) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Resumen de pago --}}
                            <div class="flex justify-end">
                                <div
                                    class="w-full sm:w-80 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-5 py-3 bg-gray-100/60 border-b border-gray-200">
                                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-500 text-base">receipt</span>
                                            Resumen
                                        </h4>
                                    </div>
                                    <div class="p-5 space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotal</span>
                                            <span
                                                class="font-medium text-gray-800 font-mono">${{ number_format($subtotal, 2) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="checkbox" wire:model.live="includeIva"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                                <span class="text-gray-600 group-hover:text-blue-600 transition">Incluye
                                                    IVA (13%)</span>
                                            </label>
                                            <span
                                                class="font-medium text-gray-800 font-mono">${{ number_format($ivaAmount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between pt-3 border-t-2 border-gray-200">
                                            <span class="font-bold text-gray-800">Total a Pagar</span>
                                            <span
                                                class="font-bold text-blue-700 text-lg font-mono">${{ number_format($total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="mt-6 bg-gradient-to-br from-gray-50 to-white rounded-xl border-2 border-dashed border-gray-300 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-400 text-3xl">inventory_2</span>
                            </div>
                            <p class="text-gray-600 font-medium">No hay productos agregados</p>
                            <p class="text-sm text-gray-400 mt-1">Usa el formulario de arriba para agregar productos a
                                la compra.</p>
                        </div>
                    @endif
                    @error('items')
                        <span class="text-xs text-red-500 mt-2 flex items-center gap-1"><span
                                class="material-symbols-outlined text-sm">error</span>{{ $message }}</span>
                    @enderror
                </div>
            </section>

            {{-- ═══════════ BOTONES DE ACCIÓN ═══════════ --}}
            <div
                class="flex flex-col-reverse sm:flex-row justify-between items-center gap-3 pt-4 border-t border-gray-200">
                <button type="button" wire:click="confirmAction('reset_form')"
                    class="w-full sm:w-auto px-5 py-2.5 border border-red-200 rounded-lg text-sm font-medium text-red-600 bg-white hover:bg-red-50 transition shadow-sm inline-flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">delete_sweep</span>
                    Limpiar todo
                </button>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <a href="{{ route('purchases.index') }}"
                        class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm text-center">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Registrar Compra
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ═══════════ MODALES (sin cambios de lógica) ═══════════ --}}
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
                        <p
                            class="text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500 text-sm">info</span>
                            Las estanterías raíz son solo referencia. Seleccioná un contenedor hijo (con código
                            indentado).
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
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $assign['product_name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $assign['product_sku'] }} ·
                                                {{ $assign['quantity'] }} unidades
                                            </p>
                                        </div>
                                    </div>
                                    @if ($assign['current_shelves'])
                                        <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">Ya
                                            en: {{ $assign['current_shelves'] }}</span>
                                    @endif
                                </div>
                                <select wire:model="shelfAssignments.{{ $index }}.shelf_id"
                                    class="w-full py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">— Sin asignar —</option>
                                    @foreach ($allShelves as $shelf)
                                        @if ($shelf['is_root'])
                                            <option disabled class="text-gray-400 bg-gray-50">{{ $shelf['display'] }}
                                            </option>
                                        @else
                                            <option value="{{ $shelf['id'] }}" {{ $shelf['is_full'] ? 'disabled' : '' }}>
                                                {{ $shelf['display'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @php $selectedShelf = collect($allShelves)->firstWhere('id', $shelfAssignments[$index]['shelf_id']); @endphp
                                @if ($selectedShelf && $selectedShelf['is_full'])
                                    <p
                                        class="text-xs text-red-600 mt-2 flex items-center gap-1 bg-red-50 px-2 py-1 rounded border border-red-100">
                                        <span class="material-symbols-outlined text-sm">warning</span>
                                        Esta ubicación está marcada como llena.
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-between border-t border-gray-200">
                        <button type="button" wire:click="skipShelfAssignment"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            Omitir
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

    {{-- Toast mejorado --}}
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50" x-transition:enter="transform ease-out duration-300"
        x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-4 opacity-0" style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-gradient-to-r from-green-600 to-green-700 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-green-500/50">
            <span class="material-symbols-outlined animate-bounce">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-gradient-to-r from-red-600 to-red-700 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-red-500/50">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

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
            <div
                class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">help</span>
                    <h3 class="text-base font-semibold text-gray-800">Guía de empaques</h3>
                </div>
                <button @click="show = false"
                    class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-100 rounded transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <p class="text-sm text-gray-600 mb-4 bg-blue-50 border border-blue-100 rounded-lg p-3">
                    Cada empaque define cuántas unidades base contiene. Al comprar, el sistema multiplica
                    automáticamente.
                </p>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider">
                                <th class="px-3 py-2 font-semibold">Tipo</th>
                                <th class="px-3 py-2 font-semibold">Ejemplo</th>
                                <th class="px-3 py-2 font-semibold text-center">Trae</th>
                                <th class="px-3 py-2 font-semibold">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Caja</td>
                                <td class="px-3 py-2">Router TP-Link</td>
                                <td class="px-3 py-2 text-center font-mono">20</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Caja x20</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Bolsa</td>
                                <td class="px-3 py-2">Conectores RJ45</td>
                                <td class="px-3 py-2 text-center font-mono">100</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Bolsa x100</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Rollo</td>
                                <td class="px-3 py-2">Cable UTP Cat6</td>
                                <td class="px-3 py-2 text-center font-mono">305</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Rollo x305 (m)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Paquete</td>
                                <td class="px-3 py-2">Filtros de señal</td>
                                <td class="px-3 py-2 text-center font-mono">50</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Paquete x50</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Pallet</td>
                                <td class="px-3 py-2">Antenas parabólicas</td>
                                <td class="px-3 py-2 text-center font-mono">12</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Pallet x12</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Unidad</td>
                                <td class="px-3 py-2">Modem individual</td>
                                <td class="px-3 py-2 text-center font-mono">1</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Unidad x1</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Saco</td>
                                <td class="px-3 py-2">Aislantes térmicos</td>
                                <td class="px-3 py-2 text-center font-mono">30</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Saco x30</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Bobina</td>
                                <td class="px-3 py-2">Fibra óptica</td>
                                <td class="px-3 py-2 text-center font-mono">1000</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Bobina x1000 (m)</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Blister</td>
                                <td class="px-3 py-2">Adaptadores USB</td>
                                <td class="px-3 py-2 text-center font-mono">5</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Blister x5</td>
                            </tr>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-3 py-2 font-medium">Sobre</td>
                                <td class="px-3 py-2">Tornillería fina</td>
                                <td class="px-3 py-2 text-center font-mono">200</td>
                                <td class="px-3 py-2 text-xs text-gray-500">Sobre x200</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p
                    class="text-xs text-gray-500 mt-3 bg-amber-50 border border-amber-100 rounded-lg p-3 flex items-start gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-base flex-shrink-0">lightbulb</span>
                    <span><strong>Ejemplo:</strong> comprás 3 Cajas de Router, cada Caja trae 20 → entran 60 routers al
                        inventario.</span>
                </p>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>