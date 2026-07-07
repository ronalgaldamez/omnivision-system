<div class="max-w-6xl mx-auto" x-data="{ hasChanges: @entangle('hasUnsavedChanges') }"
    @beforeunload.window="if (hasChanges) { event.preventDefault(); event.returnValue = ''; }">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">
                    {{ $editingId ? 'edit' : 'add_box' }}
                </span>
                {{ $editingId ? 'Editar Producto' : 'Nuevos Productos' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $editingId ? 'Modifica los datos del producto seleccionado' : 'Agrega uno o varios productos y guárdalos juntos' }}
            </p>
        </div>

        <div class="p-6 space-y-6">
            @if($editingId)
                <!-- Formulario de edición -->
                <form wire:submit.prevent="confirmUpdate" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                Nombre *
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="currentName"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                            </div>
                            @error('currentName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Stock mínimo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">arrow_downward</span>
                                Stock mínimo
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="currentStockMin"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">remove</span>
                            </div>
                        </div>
                        <!-- Stock máximo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">arrow_upward</span>
                                Stock máximo
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="currentStockMax"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">add</span>
                            </div>
                        </div>
                        <!-- Categoría -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">category</span>
                                Categoría
                            </label>
                            @if($currentCategoryId && $selCat = \App\Models\Category::find($currentCategoryId))
                            <div class="flex items-center gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                                <span class="material-symbols-outlined text-blue-600 text-sm">check_circle</span>
                                <span class="text-sm text-blue-800 flex-1">{{ $selCat->name }}</span>
                                <button type="button" wire:click="clearCategory" class="p-1 text-blue-600 hover:text-red-600 rounded"><span class="material-symbols-outlined text-lg">close</span></button>
                            </div>
                            @else
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="categorySearch" placeholder="Buscar categoría..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                                    @if(count($categoryResults) > 0)
                                    <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-48 overflow-auto">
                                        @foreach($categoryResults as $cat)
                                        <li wire:click="selectCategory({{ $cat->id }})" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm flex items-center justify-between">
                                            <span>{{ $cat->name }}</span>
                                            @if($cat->requires_device_registration)
                                                <span class="text-xs text-blue-600 font-medium">Requiere MAC</span>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                                <button type="button" wire:click="openCategoryModal"
                                    class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 transition whitespace-nowrap">
                                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                </button>
                            </div>
                            @endif
                        </div>
                        <!-- Marca -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">brand_awareness</span>
                                Marca
                            </label>
                            @if($currentBrandId && $selBrand = \App\Models\Brand::find($currentBrandId))
                            <div class="flex items-center gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                                <span class="material-symbols-outlined text-blue-600 text-sm">check_circle</span>
                                <span class="text-sm text-blue-800 flex-1">{{ $selBrand->name }}</span>
                                <button type="button" wire:click="clearBrand" class="p-1 text-blue-600 hover:text-red-600 rounded"><span class="material-symbols-outlined text-lg">close</span></button>
                            </div>
                            @else
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="brandSearch" placeholder="Buscar marca..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                                    @if(count($brandResults) > 0)
                                    <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-48 overflow-auto">
                                        @foreach($brandResults as $b)
                                        <li wire:click="selectBrand({{ $b->id }})" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{{ $b->name }}</li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                                <button type="button" wire:click="openBrandModal"
                                    class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 transition whitespace-nowrap">
                                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                </button>
                            </div>
                            @endif
                        </div>
                        <!-- Modelo -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                Modelo
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" readonly wire:model="selectedModelDisplay"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 text-sm"
                                        placeholder="Ninguno seleccionado">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                </div>
                                <button type="button" wire:click="openModelModal"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-base">travel_explore</span> Buscar
                                </button>
                                <button type="button" wire:click="clearModelSelection"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-medium hover:bg-gray-600 transition shadow-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-base">close</span> Limpiar
                                </button>
                            </div>
                        </div>
                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                Descripción
                            </label>
                            <div class="relative">
                                <textarea wire:model="currentDescription" rows="3"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                            </div>
                        </div>
                    </div>
                    {{-- Empaques --}}
                    <div class="border-t border-gray-200 pt-5">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500 text-base">package_2</span>
                            Empaques del producto
                        </h3>

                        @if(count($currentPackagings) > 0)
                            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm mb-3">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-gray-600 font-medium text-xs">Tipo</th>
                                            <th class="px-3 py-2 text-left text-gray-600 font-medium text-xs">Nombre</th>
                                            <th class="px-3 py-2 text-center text-gray-600 font-medium text-xs">Unid. base</th>
                                            <th class="px-3 py-2 text-center text-gray-600 font-medium text-xs">Default</th>
                                            <th class="px-3 py-2 text-center text-gray-600 font-medium text-xs"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($currentPackagings as $pkg)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="px-3 py-2 text-gray-700 text-xs">{{ $pkg->packagingType?->name ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-800 text-xs">{{ $pkg->name }}</td>
                                                <td class="px-3 py-2 text-center text-gray-700 text-xs font-mono">
                                                    {{ rtrim(rtrim(number_format($pkg->quantity_in_base_unit, 4), '0'), '.') }}
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    @if($pkg->is_default_for_purchase)
                                                        <span
                                                            class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs">
                                                            <span class="material-symbols-outlined text-xs">check</span>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <div class="flex items-center justify-center gap-0.5">
                                                        <button type="button" wire:click="editPackaging({{ $pkg->id }})"
                                                            class="p-1 text-blue-600 hover:bg-blue-50 rounded transition"
                                                            title="Editar">
                                                            <span class="material-symbols-outlined text-sm">edit</span>
                                                        </button>
                                                        <button type="button" wire:click="deletePackaging({{ $pkg->id }})"
                                                            class="p-1 text-red-500 hover:bg-red-50 rounded transition"
                                                            title="Eliminar" onclick="return confirm('¿Eliminar este empaque?')">
                                                            <span class="material-symbols-outlined text-sm">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($showPackagingForm)
                            <div class="space-y-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                <p class="text-xs font-medium text-blue-700">
                                    {{ $editingPackagingId ? 'Editando empaque' : 'Nuevo empaque' }}
                                </p>
                                <div class="flex items-end gap-2 flex-wrap">
                                    <div class="flex-1 min-w-[120px]">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                        <select wire:model.live="newPackagingTypeId"
                                            class="w-full px-2 py-1.5 rounded border border-gray-300 bg-white text-xs">
                                            <option value="">Seleccioná...</option>
                                            @foreach($packagingTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Unidades</label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="newPackagingQuantity"
                                            class="w-36 px-2 py-1.5 rounded border border-gray-300 bg-white text-xs" min="1">
                                    </div>
                                    <button type="button" wire:click="savePackaging"
                                        class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition whitespace-nowrap">
                                        {{ $editingPackagingId ? 'Actualizar' : 'Guardar' }}
                                    </button>
                                    <button type="button" wire:click="cancelEditPackaging"
                                        class="px-3 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-50 transition">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        @else
                            <button type="button" wire:click="editPackaging(0)"
                                class="text-xs text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Agregar empaque
                            </button>
                        @endif
                    </div>
                    {{-- Fin empaques --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('products.index') }}"
                            class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">save</span> Actualizar
                        </button>
                    </div>
                </form>
            @else
                <!-- Formulario para agregar producto a la lista -->
                <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500 text-lg">add_box</span>
                        <h2 class="font-medium text-gray-700">Agregar nuevo producto</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                Nombre *
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="currentName"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                            </div>
                            @error('currentName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Stock mínimo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">arrow_downward</span>
                                Stock mínimo
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="currentStockMin"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">inventory</span>
                            </div>
                        </div>
                        <!-- Stock máximo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">arrow_upward</span>
                                Stock máximo
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="currentStockMax"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">exposure_plus_1</span>
                            </div>
                        </div>
                        <!-- Modelo -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">category</span>
                                Modelo (Marca / Modelo / Categoría)
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" readonly wire:model="selectedModelDisplay"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 text-sm"
                                        placeholder="Ninguno seleccionado">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                </div>
                                <button type="button" wire:click="openModelModal"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-base">travel_explore</span> Buscar
                                </button>
                                <button type="button" wire:click="clearModelSelection"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-medium hover:bg-gray-600 transition shadow-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-base">close</span> Limpiar
                                </button>
                            </div>
                        </div>
                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                Descripción
                            </label>
                            <div class="relative">
                                <textarea wire:model="currentDescription" rows="2"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="button" wire:click="addToList"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-green-700 transition">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            Agregar producto
                        </button>
                    </div>
                </div>

                <!-- Tabla de productos agregados -->
                @if(count($productList) > 0)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-500 text-base">list_alt</span>
                                Productos pendientes ({{ count($productList) }})
                            </h3>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                                Nombre
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">arrow_downward</span>
                                                Stock min
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">arrow_upward</span>
                                                Stock max
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-gray-400 text-base">category</span>
                                                Modelo
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">description</span>
                                                Descripción
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                                Acciones
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($productList as $index => $prod)
                                        <tr class="hover:bg-gray-50/80 transition">
                                            <td class="px-4 py-3 text-gray-800">{{ $prod['name'] }}</td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $prod['stock_min'] }}</td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $prod['stock_max'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-gray-700">
                                                @if($prod['model_id'])
                                                    @php $model = \App\Models\ProductModel::with('brand', 'category')->find($prod['model_id']); @endphp
                                                    <span
                                                        class="text-xs bg-gray-100 px-2 py-1 rounded-full">{{ $model->brand->name ?? '' }}
                                                        - {{ $model->name ?? '' }} - {{ $model->category->name ?? '' }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate"
                                                title="{{ $prod['description'] }}">
                                                {{ $prod['description'] ?? '—' }}
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
                            </table>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <a href="{{ route('products.index') }}"
                                class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                Cancelar
                            </a>
                            <button type="button" wire:click="confirmSaveAll"
                                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">save</span>
                                Guardar todos ({{ count($productList) }})
                            </button>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-12 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                        <p class="text-gray-500">No hay productos agregados.</p>
                        <p class="text-sm text-gray-400 mt-1">Completa el formulario y haz clic en "Agregar producto".</p>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de categorías --}}
    <div x-data="{ show: @entangle('showCategoryModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Seleccionar categoría</h3>
                    <button type="button" wire:click="closeCategoryModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="categoryListSearch"
                            placeholder="Filtrar categorías..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-72 overflow-y-auto">
                    @forelse($categoryList as $cat)
                        <button type="button" wire:click="selectCategory({{ $cat->id }})"
                            class="w-full text-left px-4 py-2.5 hover:bg-blue-50 rounded-lg transition text-sm flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="material-symbols-outlined text-gray-400 text-lg flex-shrink-0">category</span>
                                <span class="text-gray-800 truncate">{{ $cat->name }}</span>
                            </div>
                            @if($cat->requires_device_registration)
                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-xs">settings_ethernet</span>
                                    Requiere MAC
                                </span>
                            @endif
                        </button>
                    @empty
                        <div class="py-8 text-center text-gray-500 text-sm">No se encontraron categorías</div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeCategoryModal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de marcas --}}
    <div x-data="{ show: @entangle('showBrandModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Seleccionar marca</h3>
                    <button type="button" wire:click="closeBrandModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="brandListSearch"
                            placeholder="Filtrar marcas..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-72 overflow-y-auto">
                    @forelse($brandList as $b)
                        <button type="button" wire:click="selectBrand({{ $b->id }})"
                            class="w-full text-left px-4 py-2.5 hover:bg-blue-50 rounded-lg transition text-sm flex items-center gap-3">
                            <span class="material-symbols-outlined text-gray-400 text-lg">brand_awareness</span>
                            <span class="text-gray-800">{{ $b->name }}</span>
                        </button>
                    @empty
                        <div class="py-8 text-center text-gray-500 text-sm">No se encontraron marcas</div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeBrandModal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de búsqueda de modelos -->
    @if($showModelModal)
        <div
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-3xl">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">search</span>
                            Seleccionar Modelo
                        </h3>
                        <button wire:click="$set('showModelModal', false)" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5">
                        <div class="relative mb-4">
                            <input type="text" wire:model.live="modelSearchTerm"
                                placeholder="Buscar por marca, modelo o categoría..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Marca</th>
                                        <th class="px-4 py-2 text-left">Modelo</th>
                                        <th class="px-4 py-2 text-left">Categoría</th>
                                        <th class="px-4 py-2 text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($modelSearchResults as $res)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2">{{ $res->brand->name ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $res->name }}</td>
                                            <td class="px-4 py-2">{{ $res->category->name ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <button wire:click="selectModel({{ $res->id }})"
                                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                                    Seleccionar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button wire:click="$set('showModelModal', false)"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

    <!-- Modal de confirmación de guardado (creación múltiple o actualización) -->
    <div x-data="{ showSaveModal: @entangle('showSaveConfirmModal') }" x-show="showSaveModal" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar registro</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        @if($editingId)
                            ¿Estás seguro de actualizar este producto? Los datos se modificarán permanentemente.
                        @else
                            ¿Estás seguro de guardar estos productos? Los movimientos de stock se actualizarán
                            automáticamente.
                        @endif
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button @if($editingId) wire:click="update" @else wire:click="saveAll" @endif
                        class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                        Sí, guardar
                    </button>
                    <button @click="showSaveModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
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
        <div x-show="toastType === 'info'"
            class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>