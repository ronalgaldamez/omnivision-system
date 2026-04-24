<div>
    <div class="max-w-7xl mx-auto bg-white rounded shadow p-4">
        <h1 class="text-xl font-bold mb-4">Catálogo de Productos</h1>

        <!-- Pestañas -->
        <div class="border-b border-gray-200 mb-4">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'models')"
                        class="inline-block p-2 rounded-t-lg {{ $activeTab == 'models' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-600' }}">
                        Modelos
                    </button>
                </li>
                <li class="mr-2">
                    <button wire:click="$set('activeTab', 'brands')"
                        class="inline-block p-2 rounded-t-lg {{ $activeTab == 'brands' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-600' }}">
                        Marcas
                    </button>
                </li>
                <li>
                    <button wire:click="$set('activeTab', 'categories')"
                        class="inline-block p-2 rounded-t-lg {{ $activeTab == 'categories' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-600' }}">
                        Categorías
                    </button>
                </li>
            </ul>
        </div>

        <!-- ========== MODELOS ========== -->
        @if($activeTab == 'models')
            <div>
                <div class="flex justify-between mb-3">
                    <input type="text" wire:model.live="searchModels" placeholder="Buscar modelo, marca o categoría..."
                        class="border rounded px-2 py-1 w-64">
                    <button wire:click="openModelModal" class="bg-blue-600 text-white px-3 py-1 rounded">Nuevo
                        Modelo</button>
                </div>
                <table class="min-w-full border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Marca</th>
                            <th class="px-4 py-2 text-left">Modelo</th>
                            <th class="px-4 py-2 text-left">Categoría</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($models as $model)
                            <tr>
                                <td class="px-4 py-2">{{ $model->brand->name ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $model->name }}</td>
                                <td class="px-4 py-2">{{ $model->category->name ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="openModelModal({{ $model->id }})"
                                        class="text-blue-600 mr-2">Editar</button>
                                    <button wire:click="confirmDeleteModel({{ $model->id }})"
                                        class="text-red-600">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $models->links() }}
            </div>
        @endif

        <!-- ========== MARCAS ========== -->
        @if($activeTab == 'brands')
            <div>
                <div class="flex justify-between mb-3">
                    <input type="text" wire:model.live="searchBrands" placeholder="Buscar marca..."
                        class="border rounded px-2 py-1 w-64">
                    <button wire:click="openBrandModal" class="bg-blue-600 text-white px-3 py-1 rounded">Nueva
                        Marca</button>
                </div>
                <table class="min-w-full border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Descripción</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                            <tr>
                                <td class="px-4 py-2">{{ $brand->name }}</td>
                                <td class="px-4 py-2">{{ $brand->description ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="openBrandModal({{ $brand->id }})"
                                        class="text-blue-600 mr-2">Editar</button>
                                    <button wire:click="confirmDeleteBrand({{ $brand->id }})"
                                        class="text-red-600">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $brands->links() }}
            </div>
        @endif

        <!-- ========== CATEGORÍAS ========== -->
        @if($activeTab == 'categories')
            <div>
                <div class="flex justify-between mb-3">
                    <input type="text" wire:model.live="searchCategories" placeholder="Buscar categoría..."
                        class="border rounded px-2 py-1 w-64">
                    <button wire:click="openCategoryModal" class="bg-blue-600 text-white px-3 py-1 rounded">Nueva
                        Categoría</button>
                </div>
                <table class="min-w-full border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Descripción</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td class="px-4 py-2">{{ $cat->name }}</td>
                                <td class="px-4 py-2">{{ $cat->description ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="openCategoryModal({{ $cat->id }})"
                                        class="text-blue-600 mr-2">Editar</button>
                                    <button wire:click="confirmDeleteCategory({{ $cat->id }})"
                                        class="text-red-600">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <td>
                        {{ $categories->links() }}
            </div>
        @endif

        <!-- Modal para crear/editar (el mismo de antes) -->
        @if($showModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                    @if($modalType == 'model')
                        <h3 class="text-lg font-medium mb-3">{{ $editingId ? 'Editar' : 'Nuevo' }} Modelo</h3>
                        <div class="mb-3">
                            <label>Marca</label>
                            <select wire:model="modelBrandId" class="w-full border rounded px-2 py-1">
                                <option value="">Seleccione</option>
                                @foreach(\App\Models\Brand::orderBy('name')->get() as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('modelBrandId') <span class="text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Nombre del modelo</label>
                            <input type="text" wire:model="modelName" class="w-full border rounded px-2 py-1">
                            @error('modelName') <span class="text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Categoría</label>
                            <select wire:model="modelCategoryId" class="w-full border rounded px-2 py-1">
                                <option value="">Seleccione</option>
                                @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('modelCategoryId') <span class="text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @elseif($modalType == 'brand')
                        <h3 class="text-lg font-medium mb-3">{{ $editingId ? 'Editar' : 'Nueva' }} Marca</h3>
                        <div class="mb-3">
                            <label>Nombre</label>
                            <input type="text" wire:model="brandName" class="w-full border rounded px-2 py-1">
                            @error('brandName') <span class="text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Descripción</label>
                            <textarea wire:model="brandDescription" rows="2" class="w-full border rounded px-2 py-1"></textarea>
                        </div>
                    @else
                        <h3 class="text-lg font-medium mb-3">{{ $editingId ? 'Editar' : 'Nueva' }} Categoría</h3>
                        <div class="mb-3">
                            <label>Nombre</label>
                            <input type="text" wire:model="categoryName" class="w-full border rounded px-2 py-1">
                            @error('categoryName') <span class="text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Descripción</label>
                            <textarea wire:model="categoryDescription" rows="2"
                                class="w-full border rounded px-2 py-1"></textarea>
                        </div>
                    @endif
                    <div class="flex justify-end gap-2 mt-4">
                        <button wire:click="$set('showModal', false)"
                            class="px-3 py-1 bg-gray-300 rounded">Cancelar</button>
                        @if($modalType == 'model')
                            <button wire:click="confirmSaveModel"
                                class="px-3 py-1 bg-blue-600 text-white rounded">Guardar</button>
                        @elseif($modalType == 'brand')
                            <button wire:click="confirmSaveBrand"
                                class="px-3 py-1 bg-blue-600 text-white rounded">Guardar</button>
                        @else
                            <button wire:click="confirmSaveCategory"
                                class="px-3 py-1 bg-blue-600 text-white rounded">Guardar</button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal de confirmación (eliminar/guardar) -->
        <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                        <span class="material-symbols-outlined text-yellow-600">warning</span>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar acción</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">{{ $confirmMessage }}</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="executeConfirmedAction"
                            class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700">Sí,
                            continuar</button>
                        <button @click="show = false"
                            class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full hover:bg-gray-400">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
            x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3000)"
            x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
            style="display: none;">
            <div x-show="toastType === 'success'"
                class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"></span>
            </div>
            <div x-show="toastType === 'error'"
                class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"></span>
            </div>
        </div>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    </div>
</div>