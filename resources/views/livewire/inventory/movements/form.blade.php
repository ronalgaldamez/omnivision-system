<div class="max-w-6xl mx-auto">
    <x-ui.card title="Registrar Movimientos (Múltiples)" subtitle="Agrega uno o varios movimientos y guárdalos juntos" icon="receipt_long">
        <div class="space-y-6">
            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500 text-lg">add_box</span>
                    <h2 class="font-medium text-gray-700">Agregar movimiento</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <x-ui.select icon="swap_vert" wire:model="currentType" label="Tipo *" required>
                        <option value="entry">Entrada (+ stock)</option>
                        <option value="exit">Salida (- stock)</option>
                    </x-ui.select>

                    <x-ui.input type="number" step="any" icon="tag" wire:model="currentQuantity" label="Cantidad *" required />

                    <x-ui.input type="number" icon="payments" wire:model="currentUnitCost" label="Costo unitario (opcional)" />

                    <div class="md:col-span-2">
                        <x-ui.textarea icon="edit_note" wire:model="currentDescription" label="Descripción (opcional)" rows="2" />
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <x-ui.button variant="success" icon="add_circle" wire:click="addToList">Agregar movimiento</x-ui.button>
                </div>
            </div>

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
                                        <td class="px-4 py-3 text-center text-gray-700">{{ $mov['unit_cost'] ? '$' . number_format($mov['unit_cost'], 2) : '—' }}</td>
                                        <td class="px-4 py-3 text-gray-600 max-w-[200px] truncate" title="{{ $mov['description'] }}">{{ $mov['description'] ?: '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <span wire:click="confirmEdit({{ $index }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Editar">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </span>
                                                <span wire:click="confirmDelete({{ $index }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Eliminar">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-ui.button variant="secondary" icon="arrow_back" href="{{ route('movements.index') }}">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" icon="save" wire:click="confirmSaveAll">Guardar todos ({{ count($movementList) }})</x-ui.button>
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
    </x-ui.card>

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
                    <x-ui.button variant="primary" icon="check" wire:click="executeModalAction">Sí, continuar</x-ui.button>
                    <span @click="show = false">
                        <x-ui.button variant="secondary" wire:click="closeModal">Cancelar</x-ui.button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
