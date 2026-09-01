<div class="max-w-6xl mx-auto" x-data="{ hasChanges: @entangle('hasUnsavedChanges') }"
    @beforeunload.window="if (hasChanges && !window.__suppressBeforeUnload) { event.preventDefault(); event.returnValue = ''; }"
    @clear-unsaved-changes.window="hasChanges = false">
    <x-ui.card :title="$editingId ? 'Editar Producto' : 'Nuevos Productos'"
        :subtitle="$editingId ? 'Modifica los datos del producto seleccionado' : 'Agrega uno o varios productos y guárdalos juntos'"
        :icon="$editingId ? 'edit' : 'add_box'">

        <div class="space-y-6">
            @if($editingId)
                <form wire:submit.prevent="confirmUpdate" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-ui.input icon="edit_note" wire:model="currentName" label="Nombre *" required />
                        <x-ui.select wire:model="currentUnit" label="Unidad de medida" icon="straighten" placeholder="">
                            @foreach($units as $u)
                                <option value="{{ $u->code }}">{{ $u->name }}{{ $u->symbol ? ' ('.$u->symbol.')' : '' }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input type="number" icon="remove" wire:model="currentStockMin" label="Stock mínimo" />
                        <x-ui.input type="number" icon="add" wire:model="currentStockMax" label="Stock máximo" />

                        <div class="md:col-span-2">
                            <x-forms.label icon="category">Categoría</x-forms.label>
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

                        <div class="md:col-span-2">
                            <x-forms.label icon="brand_awareness">Marca</x-forms.label>
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

                        <div class="md:col-span-2">
                            <x-forms.label icon="badge">Modelo</x-forms.label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" readonly wire:model="selectedModelDisplay"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 text-sm"
                                        placeholder="Ninguno seleccionado">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                </div>
                                <x-ui.button variant="primary" size="sm" icon="travel_explore" wire:click="openModelModal">Buscar</x-ui.button>
                                <x-ui.button variant="secondary" size="sm" icon="close" wire:click="clearModelSelection">Limpiar</x-ui.button>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-ui.textarea icon="edit_note" wire:model="currentDescription" label="Descripción" rows="3" />
                        </div>
                    </div>

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
                                                <td class="px-3 py-2 text-gray-700 text-xs">{{ $pkg->packagingType?->name ?? '—' }}</td>
                                                <td class="px-3 py-2 text-gray-800 text-xs">{{ $pkg->name }}</td>
                                                <td class="px-3 py-2 text-center text-gray-700 text-xs font-mono">{{ rtrim(rtrim(number_format($pkg->quantity_in_base_unit, 4), '0'), '.') }}</td>
                                                <td class="px-3 py-2 text-center">
                                                    @if($pkg->is_default_for_purchase)
                                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs">
                                                            <span class="material-symbols-outlined text-xs">check</span>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <div class="flex items-center justify-center gap-0.5">
                                                        <span wire:click="editPackaging({{ $pkg->id }})" class="p-1 text-blue-600 hover:bg-blue-50 rounded transition cursor-pointer" title="Editar">
                                                            <span class="material-symbols-outlined text-sm">edit</span>
                                                        </span>
                                                        <span wire:click="deletePackaging({{ $pkg->id }})" class="p-1 text-red-500 hover:bg-red-50 rounded transition cursor-pointer" title="Eliminar" onclick="return confirm('¿Eliminar este empaque?')">
                                                            <span class="material-symbols-outlined text-sm">delete</span>
                                                        </span>
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
                                <p class="text-xs font-medium text-blue-700">{{ $editingPackagingId ? 'Editando empaque' : 'Nuevo empaque' }}</p>
                                <div class="flex items-end gap-2 flex-wrap">
                                    <div class="flex-1 min-w-[120px]">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                        <select wire:model.live="newPackagingTypeId" class="w-full px-2 py-1.5 rounded border border-gray-300 bg-white text-xs">
                                            <option value="">Seleccioná...</option>
                                            @foreach($packagingTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Unidades</label>
                                        <input type="number" step="1" wire:model.live.debounce.500ms="newPackagingQuantity" class="w-36 px-2 py-1.5 rounded border border-gray-300 bg-white text-xs" min="1">
                                    </div>
                                    <x-ui.button variant="primary" size="sm" wire:click="savePackaging">{{ $editingPackagingId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
                                    <x-ui.button variant="secondary" size="sm" wire:click="cancelEditPackaging">Cancelar</x-ui.button>
                                </div>
                            </div>
                        @else
                            <span wire:click="editPackaging(0)" class="text-xs text-blue-600 hover:text-blue-800 transition flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">add</span>
                                Agregar empaque
                            </span>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button variant="secondary" icon="arrow_back" href="{{ route('products.index') }}">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" icon="save" type="submit">Actualizar</x-ui.button>
                    </div>
                </form>
            @else
                {{-- Importar desde Google Sheets --}}
                <div class="mb-4">
                    @if(!$showImport)
                        <x-ui.button variant="secondary" icon="cloud_download" wire:click="$set('showImport', true)">
                            Importar desde Google Sheets (CSV)
                        </x-ui.button>
                    @else
                        <div class="rounded-xl border border-gray-200 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50/80 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-500 text-base">cloud_download</span>
                                    Importar desde Google Sheets
                                </p>
                                <button type="button" wire:click="$set('showImport', false)" class="text-gray-400 hover:text-gray-600 transition">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <div class="p-4 space-y-3">
                                <x-ui.alert variant="info" title="¿Cómo obtener la URL?">
                                    <ul>
                                        <li>En Google Sheets: Archivo → Compartir → Publicar en la web.</li>
                                        <li>Elegí el formato "CSV" y copiá la URL.</li>
                                        <li>Pegá la URL acá abajo y tocá "Importar".</li>
                                    </ul>
                                </x-ui.alert>
                                <div class="flex gap-2 items-start">
                                    <div class="flex-1">
                                        <x-ui.input type="text" wire:model="importUrl" icon="link"
                                            placeholder="https://docs.google.com/spreadsheets/d/.../pub?output=csv" />
                                    </div>
                                    <x-ui.button variant="primary" icon="download" wire:click="importFromUrl">Importar</x-ui.button>
                                </div>
                                @if($importError)
                                    <p class="text-sm text-red-600 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">error</span>
                                        {{ $importError }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500 text-lg">add_box</span>
                        <h2 class="font-medium text-gray-700">Agregar nuevo producto</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input icon="edit_note" wire:model="currentName" label="Nombre *" required />
                        <x-ui.select wire:model="currentUnit" label="Unidad de medida" icon="straighten" placeholder="">
                            @foreach($units as $u)
                                <option value="{{ $u->code }}">{{ $u->name }}{{ $u->symbol ? ' ('.$u->symbol.')' : '' }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input type="number" icon="inventory" wire:model="currentStockMin" label="Stock mínimo" />
                        <x-ui.input type="number" icon="exposure_plus_1" wire:model="currentStockMax" label="Stock máximo" />

                        <x-ui.select wire:model="currentPackagingTypeId" label="Tipo de empaque (opcional)" icon="package_2" placeholder="Sin empaque">
                            @foreach($packagingTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input type="number" step="any" icon="123" wire:model="currentPackagingQuantity" label="Unidades por empaque" />

                        <div class="md:col-span-2">
                            <x-forms.label icon="category">Modelo (Marca / Modelo / Categoría)</x-forms.label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" readonly wire:model="selectedModelDisplay"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 text-sm"
                                        placeholder="Ninguno seleccionado">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                </div>
                                <x-ui.button variant="primary" size="sm" icon="travel_explore" wire:click="openModelModal">Buscar</x-ui.button>
                                <x-ui.button variant="secondary" size="sm" icon="close" wire:click="clearModelSelection">Limpiar</x-ui.button>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-ui.textarea icon="edit_note" wire:model="currentDescription" label="Descripción" rows="2" />
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <x-ui.button variant="success" icon="add_circle" wire:click="addToList">Agregar producto</x-ui.button>
                    </div>
                </div>

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
                                            <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span> Nombre</div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5"><span class="material-symbols-outlined text-gray-400 text-base">arrow_downward</span> Stock min</div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5"><span class="material-symbols-outlined text-gray-400 text-base">arrow_upward</span> Stock max</div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-gray-400 text-base">category</span> Modelo</div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-gray-400 text-base">description</span> Descripción</div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5"><span class="material-symbols-outlined text-gray-400 text-base">settings</span> Acciones</div>
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
                                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded-full">{{ $model->brand->name ?? '' }} - {{ $model->name ?? '' }} - {{ $model->category->name ?? '' }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $prod['description'] }}">{{ $prod['description'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <span wire:click="confirmAction('edit', {{ $index }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Editar">
                                                        <span class="material-symbols-outlined text-lg">edit</span>
                                                    </span>
                                                    <span wire:click="confirmAction('delete', {{ $index }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Eliminar">
                                                        <span class="material-symbols-outlined text-lg">delete</span>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-3 pt-4 border-t border-gray-200">
                            <button type="button" wire:click="confirmAction('clear_list')"
                                class="w-full sm:w-auto px-5 py-2.5 border border-red-200 rounded-lg text-sm font-medium text-red-600 bg-white hover:bg-red-50 transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-base">delete_sweep</span> Limpiar
                            </button>
                            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                <x-ui.button variant="secondary" icon="arrow_back" href="{{ route('products.index') }}">Cancelar</x-ui.button>
                                <x-ui.button variant="primary" icon="save" wire:click="confirmSaveAll">Guardar todos ({{ count($productList) }})</x-ui.button>
                            </div>
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
    </x-ui.card>

    {{-- Modal de categorías --}}
    <div x-data="{ show: @entangle('showCategoryModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
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
                    <x-ui.button variant="secondary" wire:click="closeCategoryModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de marcas --}}
    <div x-data="{ show: @entangle('showBrandModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
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
                    <x-ui.button variant="secondary" wire:click="closeBrandModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de búsqueda de modelos --}}
    @if($showModelModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="relative mx-auto p-5 w-full max-w-3xl">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">search</span>
                            Seleccionar Modelo
                        </h3>
                        <span wire:click="$set('showModelModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <span class="material-symbols-outlined">close</span>
                        </span>
                    </div>
                    <div class="p-5">
                        <div class="relative mb-4">
                            <input type="text" wire:model.live="modelSearchTerm"
                                placeholder="Buscar por marca, modelo o categoría..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
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
                                                <span wire:click="selectModel({{ $res->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-sm cursor-pointer">Seleccionar</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <x-ui.button variant="secondary" wire:click="$set('showModelModal', false)">Cerrar</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmación de acción (editar/eliminar) --}}
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative mx-auto p-5 w-full max-w-md"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $modalMessage }}</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                    <x-ui.button variant="primary" icon="check" wire:click="executeAction">Sí, continuar</x-ui.button>
                    <span @click="show = false">
                        <x-ui.button variant="secondary" wire:click="closeModal">Cancelar</x-ui.button>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación de guardado --}}
    <div x-data="{ showSaveModal: @entangle('showSaveConfirmModal') }" x-show="showSaveModal" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative mx-auto p-5 w-full max-w-md"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
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
                            ¿Estás seguro de guardar estos productos? Los movimientos de stock se actualizarán automáticamente.
                        @endif
                    </p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                    @if($editingId)
                        <x-ui.button variant="success" icon="check" wire:click="update">Sí, guardar</x-ui.button>
                    @else
                        <x-ui.button variant="success" icon="check" wire:click="saveAll">Sí, guardar</x-ui.button>
                    @endif
                    <span @click="showSaveModal = false">
                        <x-ui.button variant="secondary">Cancelar</x-ui.button>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de previsualización de importación --}}
    <div x-data="{ show: @entangle('showImportPreview') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-6xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    @php
                        $newCount = collect($importPreview)->where('status', 'new')->count();
                        $existingCount = collect($importPreview)->where('status', 'existing')->count();
                        $totalValue = collect($importPreview)->where('status', 'new')
                            ->sum(fn ($r) => (float) ($r['stock'] ?? 0) * (float) ($r['costo'] ?? 0));
                        $importCountMsg = count($importPreview) . ' producto(s)';
                        if ($existingCount > 0) $importCountMsg .= ', ' . $existingCount . ' existente(s)';
                        if ($importSkipped > 0) $importCountMsg .= ', ' . $importSkipped . ' sin nombre';

                        $filteredRows = collect($importPreview)->filter(function ($row) use ($importSearch, $importStatusFilter) {
                            $nameMatch = ! $importSearch || stripos($row['name'], $importSearch) !== false || stripos((string) ($row['sku'] ?? ''), $importSearch) !== false;
                            $statusMatch = ! $importStatusFilter || (($row['status'] ?? 'new') === $importStatusFilter);
                            return $nameMatch && $statusMatch;
                        });
                    @endphp

                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">preview</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Revisar importación</h3>
                                <p class="text-xs text-gray-500">{{ $importCountMsg }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="refreshImport" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition" title="Recargar">
                                <span class="material-symbols-outlined text-xl">refresh</span>
                            </button>
                            <button type="button" wire:click="cancelImport" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                                <span class="material-symbols-outlined text-xl">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                            <span class="material-symbols-outlined text-sm">add_circle</span> Nuevos: {{ $newCount }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                            <span class="material-symbols-outlined text-sm">inventory_2</span> Existentes: {{ $existingCount }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                            <span class="material-symbols-outlined text-sm">attach_money</span> Valor: ${{ number_format($totalValue, 2) }}
                        </span>
                        <div class="flex-1"></div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                            <input type="text" wire:model.live.debounce.300ms="importSearch" placeholder="Buscar..."
                                class="w-44 pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        <select wire:model.live="importStatusFilter" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="new">Nuevos</option>
                            <option value="existing">Existentes</option>
                        </select>
                    </div>
                </div>

                <div class="p-4 max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Producto</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Unidad</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Empaque</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Unid. x empaque</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Stock</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Costo</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Base</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($filteredRows as $index => $row)
                                @php
                                    $pkgQty = (float) ($row['packaging_quantity'] ?? 0);
                                    $stockQty = (float) ($row['stock'] ?? 0);
                                    $baseQty = $pkgQty > 0 ? $stockQty * $pkgQty : $stockQty;
                                    $isExisting = ($row['status'] ?? 'new') === 'existing';
                                @endphp
                                <tr class="hover:bg-gray-50/50 {{ $isExisting ? 'bg-amber-50/50' : '' }}">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        @if($isExisting)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Existente</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Nuevo</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-800">{{ $row['name'] }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model.live="importPreview.{{ $index }}.unit_of_measure"
                                            class="w-28 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                            @foreach($units as $u)
                                                <option value="{{ $u->code }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model.live="importPreview.{{ $index }}.packaging_type_id"
                                            class="w-32 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                            <option value="">Sin empaque</option>
                                            @foreach($packagingTypes as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="any" min="0" wire:model.live.debounce.500ms="importPreview.{{ $index }}.packaging_quantity"
                                            class="w-20 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="any" min="0" wire:model.live.debounce.500ms="importPreview.{{ $index }}.stock"
                                            class="w-20 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="any" min="0" wire:model.live.debounce.500ms="importPreview.{{ $index }}.costo"
                                            class="w-24 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono whitespace-nowrap">
                                        @if($pkgQty > 0)
                                            <span class="text-gray-400">{{ $stockQty }} × {{ $pkgQty }} =</span>
                                            <span class="font-semibold text-blue-700">{{ rtrim(rtrim(number_format($baseQty, 4), '0'), '.') }}</span>
                                        @else
                                            <span class="font-semibold text-gray-700">{{ rtrim(rtrim(number_format($baseQty, 4), '0'), '.') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-10 text-center text-gray-500 text-sm">No se encontraron productos con ese filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="primary" icon="check_circle" wire:click="confirmImport">Confirmar</x-ui.button>
                    <button @click="show = false" wire:click="cancelImport"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
