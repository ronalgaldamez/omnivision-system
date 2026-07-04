<div class="max-w-6xl mx-auto py-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">fork_right</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Repartición de inventario</h1>
                    <p class="text-sm text-gray-500">Distribuí el stock entre sucursales</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Buscar producto --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">search</span>
                    Buscar producto
                </label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="productSearch"
                        placeholder="Buscar por nombre o SKU..."
                        class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    @if ($selectedProductId)
                        <button type="button" wire:click="clearProduct"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition"
                            title="Limpiar selección">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    @endif
                </div>
                @if (count($productSearchResults) > 0)
                    <ul class="absolute z-20 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                        @foreach ($productSearchResults as $result)
                            <li wire:click="selectProduct({{ $result->id }})"
                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $result->name }}</span>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $result->sku }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Producto seleccionado --}}
            @if ($selectedProductId)
                {{-- Tarjetas de stock --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl border border-blue-200 p-4">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wider mb-1">Stock Global</p>
                        <p class="text-2xl font-bold text-blue-900 font-mono">{{ rtrim(rtrim(number_format($globalStock, 4), '0'), '.') }}</p>
                        <p class="text-xs text-blue-600 mt-1">Unidades totales</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl border border-amber-200 p-4">
                        <p class="text-xs font-medium text-amber-600 uppercase tracking-wider mb-1">Ya Repartido</p>
                        <p class="text-2xl font-bold text-amber-900 font-mono">{{ rtrim(rtrim(number_format($alreadyAllocated, 4), '0'), '.') }}</p>
                        <p class="text-xs text-amber-600 mt-1">Asignado a sucursales</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl border border-green-200 p-4">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wider mb-1">Disponible</p>
                        <p class="text-2xl font-bold text-green-900 font-mono">{{ rtrim(rtrim(number_format($available, 4), '0'), '.') }}</p>
                        <p class="text-xs text-green-600 mt-1">Sin repartir</p>
                    </div>
                </div>

                {{-- Tabla de repartición --}}
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-500 text-base">store</span>
                        Asignar a sucursales
                    </h3>

                    @if (count($allocations) > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium text-xs uppercase tracking-wider">Sucursal</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium text-xs uppercase tracking-wider w-36">Ya asignado</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium text-xs uppercase tracking-wider w-36">Nueva cantidad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($allocations as $index => $alloc)
                                        <tr class="hover:bg-blue-50/30 transition {{ $activeBranchId && (string) $alloc['branch_id'] === (string) $activeBranchId ? 'bg-blue-50/50 ring-1 ring-blue-200' : '' }}">
                                            <td class="px-4 py-3">
                                                <span class="font-medium text-gray-800">{{ $alloc['branch_name'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-sm font-mono font-medium">
                                                    {{ rtrim(rtrim(number_format($alloc['current_allocated'], 4), '0'), '.') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number"
                                                    wire:model.live="allocations.{{ $index }}.new_quantity"
                                                    step="any" min="0"
                                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-center font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                                    placeholder="0">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gradient-to-r from-blue-50 to-white border-t-2 border-blue-200">
                                        <td class="px-4 py-3 text-right font-semibold text-gray-700 text-xs uppercase">Total a repartir:</td>
                                        <td class="px-4 py-3"></td>
                                        <td class="px-4 py-3 text-center font-bold text-blue-700 text-base font-mono">
                                            {{ rtrim(rtrim(number_format(array_sum(array_map('floatval', array_column($allocations, 'new_quantity'))), 4), '0'), '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="flex justify-end pt-2 border-t border-gray-200">
                            <button type="button" wire:click="save"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                                <span class="material-symbols-outlined text-base">save</span>
                                Guardar repartición
                            </button>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <span class="material-symbols-outlined text-4xl mb-2">store</span>
                            <p>No hay sucursales activas registradas.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border-2 border-dashed border-gray-300 py-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-3xl">inventory_2</span>
                    </div>
                    <p class="text-gray-600 font-medium">Seleccioná un producto para repartir</p>
                    <p class="text-sm text-gray-400 mt-1">Usa el buscador de arriba para encontrar un producto.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Toast --}}
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
</div>
