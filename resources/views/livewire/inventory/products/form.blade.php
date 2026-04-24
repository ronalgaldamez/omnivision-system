<div class="max-w-6xl mx-auto">
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
                        <!-- Unidad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">straighten</span>
                                Unidad *
                            </label>
                            <div class="relative">
                                <select wire:model="currentUnit"
                                    class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                    <option value="unidad">Unidad</option>
                                    <option value="kg">Kilogramo</option>
                                    <option value="g">Gramo</option>
                                    <option value="m">Metro</option>
                                    <option value="cm">Centímetro</option>
                                    <option value="lb">Libra</option>
                                    <option value="l">Litro</option>
                                    <option value="ml">Mililitro</option>
                                    <option value="caja">Caja</option>
                                    <option value="paquete">Paquete</option>
                                </select>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">square_foot</span>
                                <span
                                    class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <!-- Valor medida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">aspect_ratio</span>
                                Valor medida
                            </label>
                            <div class="relative">
                                <input type="number" step="any" wire:model="currentMeasureValue"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">numbers</span>
                            </div>
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
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">exposure_minus_1</span>
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
                                <textarea wire:model="currentDescription" rows="3"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                            </div>
                        </div>
                    </div>
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
                        <!-- Unidad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">straighten</span>
                                Unidad *
                            </label>
                            <div class="relative">
                                <select wire:model="currentUnit"
                                    class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                    <option value="unidad">Unidad</option>
                                    <option value="kg">Kilogramo</option>
                                    <option value="g">Gramo</option>
                                    <option value="m">Metro</option>
                                    <option value="cm">Centímetro</option>
                                    <option value="lb">Libra</option>
                                    <option value="l">Litro</option>
                                    <option value="ml">Mililitro</option>
                                    <option value="caja">Caja</option>
                                    <option value="paquete">Paquete</option>
                                </select>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">square_foot</span>
                                <span
                                    class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        <!-- Valor medida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">aspect_ratio</span>
                                Valor medida
                            </label>
                            <div class="relative">
                                <input type="number" step="any" wire:model="currentMeasureValue"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">numbers</span>
                            </div>
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
                                                    class="material-symbols-outlined text-gray-400 text-base">straighten</span>
                                                Unidad
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">aspect_ratio</span>
                                                Valor
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
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $prod['unit_of_measure'] }}</td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $prod['measure_value'] ?? '—' }}</td>
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