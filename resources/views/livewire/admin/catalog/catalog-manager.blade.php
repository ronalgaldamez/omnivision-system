<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                Catálogo de Productos
            </h1>
            <p class="text-sm text-gray-500 mt-1">Administración de modelos, marcas y categorías</p>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Pestañas -->
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
                    <li>
                        <button wire:click="$set('activeTab', 'categories')"
                            class="inline-flex items-center gap-1.5 p-3 rounded-t-lg transition
                            {{ $activeTab == 'categories' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-600 hover:bg-gray-50' }}">
                            <span class="material-symbols-outlined text-base">sell</span>
                            Categorías
                        </button>
                    </li>
                </ul>
            </div>

            <!-- ========== MODELOS ========== -->
            @if($activeTab == 'models')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="relative w-full sm:w-80">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            <input type="text" wire:model.live="searchModels" placeholder="Buscar modelo, marca o categoría..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        </div>
                        <button wire:click="openModelModal"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            Nuevo Modelo
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">branding_watermark</span>
                                            Marca
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
                                            <span class="material-symbols-outlined text-gray-400 text-base">sell</span>
                                            Categoría
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
                                @forelse($models as $model)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-700">{{ $model->brand->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $model->name }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $model->category->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openModelModal({{ $model->id }})"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteModel({{ $model->id }})"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
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

            <!-- ========== MARCAS ========== -->
            @if($activeTab == 'brands')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="relative w-full sm:w-80">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            <input type="text" wire:model.live="searchBrands" placeholder="Buscar marca..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        </div>
                        <button wire:click="openBrandModal"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            Nueva Marca
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                            Nombre
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">description</span>
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
                                @forelse($brands as $brand)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $brand->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $brand->description ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openBrandModal({{ $brand->id }})"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteBrand({{ $brand->id }})"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
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

            <!-- ========== CATEGORÍAS ========== -->
            @if($activeTab == 'categories')
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="relative w-full sm:w-80">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            <input type="text" wire:model.live="searchCategories" placeholder="Buscar categoría..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        </div>
                        <button wire:click="openCategoryModal"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            Nueva Categoría
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                            Nombre
                                        </div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-gray-400 text-base">description</span>
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
                                @forelse($categories as $cat)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $cat->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $cat->description ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="openCategoryModal({{ $cat->id }})"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button wire:click="confirmDeleteCategory({{ $cat->id }})"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
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
        </div>
    </div>

    <!-- Modal para crear/editar -->
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">branding_watermark</span>
                                    Marca
                                </label>
                                <div class="relative">
                                    <select wire:model="modelBrandId"
                                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                        <option value="">Seleccione</option>
                                        @foreach(\App\Models\Brand::orderBy('name')->get() as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">expand_more</span>
                                </div>
                                @error('modelBrandId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">edit_note</span>
                                    Nombre del modelo
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model="modelName"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">category</span>
                                </div>
                                @error('modelName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">sell</span>
                                    Categoría
                                </label>
                                <div class="relative">
                                    <select wire:model="modelCategoryId"
                                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                        <option value="">Seleccione</option>
                                        @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">expand_more</span>
                                </div>
                                @error('modelCategoryId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @elseif($modalType == 'brand')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                    Nombre
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model="brandName"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                                </div>
                                @error('brandName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                    Descripción
                                </label>
                                <div class="relative">
                                    <textarea wire:model="brandDescription" rows="2"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                    Nombre
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model="categoryName"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                                </div>
                                @error('categoryName') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                    Descripción
                                </label>
                                <div class="relative">
                                    <textarea wire:model="categoryDescription" rows="2"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                                </div>
                            </div>
                        @endif
                        <div class="flex justify-end gap-3 pt-2">
                            <button wire:click="$set('showModal', false)"
                                class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                Cancelar
                            </button>
                            @if($modalType == 'model')
                                <button wire:click="confirmSaveModel"
                                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">save</span> Guardar
                                </button>
                            @elseif($modalType == 'brand')
                                <button wire:click="confirmSaveBrand"
                                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">save</span> Guardar
                                </button>
                            @else
                                <button wire:click="confirmSaveCategory"
                                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">save</span> Guardar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de confirmación -->
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
                    <button wire:click="executeConfirmedAction"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                        Sí, continuar
                    </button>
                    <button @click="show = false"
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
        [x-cloak] { display: none !important; }
    </style>
</div>