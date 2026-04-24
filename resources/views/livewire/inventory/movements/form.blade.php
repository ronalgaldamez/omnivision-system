<div class="max-w-6xl mx-auto">
    <!-- Tarjeta principal con sombra suave y bordes redondeados -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">receipt_long</span>
                Registrar Movimientos (Múltiples)
            </h1>
            <p class="text-sm text-gray-500 mt-1">Agrega uno o varios movimientos y guárdalos juntos</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Formulario para agregar un movimiento -->
            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500 text-lg">add_box</span>
                    <h2 class="font-medium text-gray-700">Agregar movimiento</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Búsqueda de producto -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                Producto *
                            </span>
                        </label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                placeholder="Buscar por nombre o SKU..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        </div>
                        @if(count($searchResults) > 0)
                            <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach($searchResults as $result)
                                    <li wire:click="selectProduct({{ $result->id }})"
                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-gray-800">{{ $result->name }}</span>
                                            <span class="text-gray-500 ml-2">({{ $result->sku }})</span>
                                        </div>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Stock: {{ $result->current_stock }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @error('currentProductId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-400 text-base">swap_vert</span>
                                Tipo *
                            </span>
                        </label>
                        <div class="relative">
                            <select wire:model="currentType"
                                class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="entry">Entrada (+ stock)</option>
                                <option value="exit">Salida (- stock)</option>
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">arrow_selector_tool</span>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    <!-- Cantidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                Cantidad *
                            </span>
                        </label>
                        <div class="relative">
                            <input type="number" step="any" wire:model="currentQuantity"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                        </div>
                        @error('currentQuantity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Costo unitario -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                Costo unitario (opcional)
                            </span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" wire:model="currentUnitCost"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">payments</span>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                Descripción (opcional)
                            </span>
                        </label>
                        <div class="relative">
                            <textarea wire:model="currentDescription" rows="2"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"></textarea>
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="button" wire:click="addToList"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition-all">
                        <span class="material-symbols-outlined text-base">add_circle</span>
                        Agregar movimiento
                    </button>
                </div>
            </div>

            <!-- Tabla de movimientos agregados -->
            @if(count($movementList) > 0)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-500 text-base">list_alt</span>
                            Movimientos pendientes ({{ count($movementList) }})
                        </h3>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto (SKU)</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Stock actual</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Tipo</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Costo unit.</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($movementList as $index => $mov)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $mov['product_name'] }} <span class="text-gray-500 text-xs ml-1">({{ $mov['product_sku'] }})</span></td>
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $mov['current_stock'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($mov['type'] == 'entry')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                                    <span class="material-symbols-outlined text-sm">arrow_upward</span> Entrada
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                                    <span class="material-symbols-outlined text-sm">arrow_downward</span> Salida
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium text-gray-800">{{ $mov['quantity'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700">
                                            {{ $mov['unit_cost'] ? '$' . number_format($mov['unit_cost'], 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $mov['description'] }}">
                                            {{ $mov['description'] ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button" wire:click="confirmEdit({{ $index }})"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </button>
                                                <button type="button" wire:click="confirmDelete({{ $index }})"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
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
                        <a href="{{ route('movements.index') }}"
                            class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                            Cancelar
                        </a>
                        <button type="button" wire:click="confirmSaveAll"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">save</span>
                            Guardar todos ({{ count($movementList) }})
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-12 text-center">
                    <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                    <p class="text-gray-500">No hay movimientos agregados.</p>
                    <p class="text-sm text-gray-400 mt-1">Completa el formulario y haz clic en "Agregar movimiento".</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de confirmación (Alpine.js) - Diseño refinado -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden" @click.outside="show = false">
                <div class="p-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2 text-center">{{ $modalMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="executeModalAction"
                        class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        Sí, continuar
                    </button>
                    <button @click="show = false" wire:click="closeModal"
                        class="w-full sm:w-auto px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast - Diseño más moderno -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-2 opacity-0"
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