<div class="max-w-7xl mx-auto">
    <x-ui.card icon="inventory_2" title="Catálogo de Productos" subtitle="Administración de modelos, marcas y categorías">
        {{-- Pestañas --}}
        <div class="border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'models')"
                        class="inline-flex items-center gap-1.5 p-3 rounded-t-lg transition
                        {{ $activeTab == 'models' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-600 hover:bg-gray-50' }}">
                        <span class="material-symbols-outlined text-base">category</span>
                        Modelos
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'brands')"
                        class="inline-flex items-center gap-1.5 p-3 rounded-t-lg transition
                        {{ $activeTab == 'brands' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-600 hover:bg-gray-50' }}">
                        <span class="material-symbols-outlined text-base">branding_watermark</span>
                        Marcas
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'categories')"
                        class="inline-flex items-center gap-1.5 p-3 rounded-t-lg transition
                        {{ $activeTab == 'categories' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-600 hover:bg-gray-50' }}">
                        <span class="material-symbols-outlined text-base">sell</span>
                        Categorías
                    </button>
                </li>
                <li>
                    <button wire:click="$set('activeTab', 'units')"
                        class="inline-flex items-center gap-1.5 p-3 rounded-t-lg transition
                        {{ $activeTab == 'units' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-600 hover:bg-gray-50' }}">
                        <span class="material-symbols-outlined text-base">straighten</span>
                        Unidades
                    </button>
                </li>
            </ul>
        </div>

        <div class="p-6 space-y-5">
            {{-- MODELOS --}}
            @if($activeTab == 'models')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-ui.input type="text" wire:model.live="searchModels" placeholder="Buscar modelo, marca o categoría..." icon="search" class="w-full sm:w-80" />
                        <x-ui.button variant="primary" icon="add_circle" wire:click="openModelModal">Nuevo Modelo</x-ui.button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Marca</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Modelo</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Categoría</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($models as $model)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-700">{{ $model->brand->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $model->name }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $model->category->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openModelModal({{ $model->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteModel({{ $model->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center bg-gray-50/50">
                                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                            <p class="text-gray-500">No hay modelos registrados</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div>{{ $models->links() }}</div>
                </div>
            @endif

            {{-- MARCAS --}}
            @if($activeTab == 'brands')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-ui.input type="text" wire:model.live="searchBrands" placeholder="Buscar marca..." icon="search" class="w-full sm:w-80" />
                        <x-ui.button type="primary" icon="add_circle" wire:click="openBrandModal">Nueva Marca</x-ui.button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Nombre</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($brands as $brand)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $brand->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $brand->description ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openBrandModal({{ $brand->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteBrand({{ $brand->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-12 text-center bg-gray-50/50">
                                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                            <p class="text-gray-500">No hay marcas registradas</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div>{{ $brands->links() }}</div>
                </div>
            @endif

            {{-- ========== CATEGORÍAS ========== --}}
            @if($activeTab == 'categories')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-ui.input type="text" wire:model.live="searchCategories" placeholder="Buscar categoría..." icon="search" class="w-full sm:w-80" />
                        <x-ui.button type="primary" icon="add_circle" wire:click="openCategoryModal">Nueva Categoría</x-ui.button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Nombre</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($categories as $cat)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $cat->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $cat->description ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openCategoryModal({{ $cat->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteCategory({{ $cat->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-12 text-center bg-gray-50/50">
                                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                            <p class="text-gray-500">No hay categorías registradas</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div>{{ $categories->links() }}</div>
                </div>
            @endif

            {{-- ========== UNIDADES DE MEDIDA ========== --}}
            @if($activeTab == 'units')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-gray-500">Unidades de medida disponibles para productos.</p>
                        <x-ui.button type="primary" icon="add_circle" wire:click="openUnitModal">Nueva Unidad</x-ui.button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Código</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Nombre</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Símbolo</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Tipo</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($units as $unit)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800 font-mono">{{ $unit->code }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $unit->name }}</td>
                                        <td class="px-4 py-3 text-center text-gray-600">{{ $unit->symbol ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($unit->is_whole)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Entero</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">Decimal</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openUnitModal({{ $unit->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteUnit({{ $unit->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">straighten</span>
                                            <p class="text-gray-500">No hay unidades registradas</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Modal para crear/editar --}}
    @if($showModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        @if($modalType == 'model')
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500">category</span>
                                {{ $editingId ? 'Editar' : 'Nuevo' }} Modelo
                            </h3>
                        @elseif($modalType == 'brand')
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500">branding_watermark</span>
                                {{ $editingId ? 'Editar' : 'Nueva' }} Marca
                            </h3>
                        @elseif($modalType == 'unit')
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500">straighten</span>
                                {{ $editingId ? 'Editar' : 'Nueva' }} Unidad
                            </h3>
                        @else
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500">sell</span>
                                {{ $editingId ? 'Editar' : 'Nueva' }} Categoría
                            </h3>
                        @endif
                        <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($modalType == 'model')
                            <x-forms.group name="modelBrandId" label="Marca">
                                <x-ui.select wire:model="modelBrandId" placeholder="Seleccione">
                                    @foreach(\App\Models\Brand::orderBy('name')->get() as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-forms.group>
                            <x-forms.group name="modelName" label="Nombre del modelo">
                                <x-ui.input type="text" wire:model="modelName" />
                            </x-forms.group>
                            <x-forms.group name="modelCategoryId" label="Categoría">
                                <x-ui.select wire:model="modelCategoryId" placeholder="Seleccione">
                                    @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-forms.group>
                        @elseif($modalType == 'brand')
                            <x-forms.group name="brandName" label="Nombre">
                                <x-ui.input type="text" wire:model="brandName" />
                            </x-forms.group>
                            <x-forms.group name="brandDescription" label="Descripción">
                                <x-ui.textarea wire:model="brandDescription" rows="2" />
                            </x-forms.group>
                        @elseif($modalType == 'unit')
                            <x-forms.group name="unitCode" label="Código">
                                <x-ui.input type="text" wire:model="unitCode" placeholder="ej: metro" />
                            </x-forms.group>
                            <x-forms.group name="unitName" label="Nombre">
                                <x-ui.input type="text" wire:model="unitName" placeholder="ej: Metro" />
                            </x-forms.group>
                            <x-forms.group name="unitSymbol" label="Símbolo (opcional)">
                                <x-ui.input type="text" wire:model="unitSymbol" placeholder="ej: m, L, kg" />
                            </x-forms.group>
                            <div class="space-y-1 px-1">
                                <div class="flex items-center gap-2">
                                    <x-ui.checkbox wire:model="unitIsWhole" id="unitIsWhole" />
                                    <label for="unitIsWhole" class="text-sm text-gray-700 cursor-pointer select-none">Solo acepta números enteros</label>
                                </div>
                                <p class="text-xs text-gray-400 ml-6 leading-relaxed">
                                    Marcá esta opción si la unidad no se divide (ej: routers, piezas). Desmarcá para unidades fraccionables (metros, litros, kilos).
                                </p>
                            </div>
                        @else
                            <x-forms.group name="categoryName" label="Nombre">
                                <x-ui.input type="text" wire:model="categoryName" />
                            </x-forms.group>
                            <x-forms.group name="categoryDescription" label="Descripción">
                                <x-ui.textarea wire:model="categoryDescription" rows="2" />
                            </x-forms.group>
                            <div class="space-y-1 px-1">
                                <div class="flex items-center gap-2">
                                    <x-ui.checkbox wire:model="categoryRequiresDevice" id="catRequiresDevice" />
                                    <label for="catRequiresDevice" class="text-sm text-gray-700 cursor-pointer select-none">Requiere registro de MAC para distribución</label>
                                </div>
                                <p class="text-xs text-gray-400 ml-6 leading-relaxed">
                                    Marcá esta opción si los productos de esta categoría necesitan registrar direcciones MAC (routers, access points, ONT, etc.) antes de poder distribuirse a sucursales. Si no estás seguro, dejalo desmarcado.
                                </p>
                            </div>
                        @endif
                        <div class="flex justify-end gap-3 pt-2">
                            <x-ui.button variant="ghost" wire:click="$set('showModal', false)">Cancelar</x-ui.button>
                            @if($modalType == 'model')
                                <x-ui.button variant="primary" icon="save" wire:click="confirmSaveModel">Guardar</x-ui.button>
                            @elseif($modalType == 'brand')
                                <x-ui.button variant="primary" icon="save" wire:click="confirmSaveBrand">Guardar</x-ui.button>
                            @elseif($modalType == 'unit')
                                <x-ui.button variant="primary" icon="save" wire:click="confirmSaveUnit">Guardar</x-ui.button>
                            @else
                                <x-ui.button variant="primary" icon="save" wire:click="confirmSaveCategory">Guardar</x-ui.button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmación --}}
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-yellow-100 mb-4">
                        <span class="material-symbols-outlined text-yellow-600 text-2xl">warning</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $confirmMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="primary" wire:click="executeConfirmedAction">Sí, continuar</x-ui.button>
                    <x-ui.button variant="ghost" @click="show = false">Cancelar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>