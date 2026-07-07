<div class="max-w-6xl mx-auto py-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-visible">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">fork_right</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Repartición de dispositivos</h1>
                    <p class="text-sm text-gray-500">Asigná dispositivos específicos a sucursales</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">search</span>
                    Buscar producto
                </label>
                @if($selectedProductId && $selectedProduct)
                <div class="flex items-start gap-3 p-3.5 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-blue-600 text-xl">check_circle</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $selectedProduct->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $selectedProduct->sku }}</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" wire:click="openProductModal"
                            class="px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition">Cambiar</button>
                        <button type="button" wire:click="clearProduct"
                            class="p-1.5 text-blue-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar producto">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                </div>
                @else
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.300ms="productSearch"
                            placeholder="Buscar por nombre o SKU..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        @if (count($productSearchResults) > 0)
                            <ul class="absolute z-20 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100">
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
                    <button type="button" wire:click="openProductModal"
                        class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                        title="Ver todos los productos">
                        <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                        <span class="hidden sm:inline">Ver todos</span>
                    </button>
                </div>
                @endif
            </div>

            @if ($selectedProductId)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl border border-blue-200 p-4">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wider mb-1">Total dispositivos</p>
                        <p class="text-2xl font-bold text-blue-900 font-mono">{{ $globalStock }}</p>
                        <p class="text-xs text-blue-600 mt-1">Registrados en el sistema</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl border border-amber-200 p-4">
                        <p class="text-xs font-medium text-amber-600 uppercase tracking-wider mb-1">Ya Repartido</p>
                        <p class="text-2xl font-bold text-amber-900 font-mono">{{ $alreadyAllocated }}</p>
                        <p class="text-xs text-amber-600 mt-1">Asignados a sucursales</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl border border-green-200 p-4">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wider mb-1">Disponibles</p>
                        <p class="text-2xl font-bold text-green-900 font-mono">{{ $available }}</p>
                        <p class="text-xs text-green-600 mt-1">Sin sucursal asignada</p>
                    </div>
                </div>

                @if($requiresDevice)
                    @if($globalStock == 0)
                    <div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                        <span class="material-symbols-outlined text-yellow-600 text-base flex-shrink-0 mt-0.5">warning</span>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Este producto requiere registro de MAC</p>
                            <p class="text-xs text-yellow-700 mt-1">No se encontraron dispositivos registrados para este producto. Andá a <a href="{{ route('devices.register') }}" class="underline font-medium">/devices/register</a> para cargar las direcciones MAC antes de distribuir.</p>
                        </div>
                    </div>
                    @endif
                    {{-- Distribución por dispositivos (routers con MAC) --}}
                    @if($available > 0)
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-500 text-base">assignment</span>
                            Asignar dispositivos disponibles
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Sucursal destino</label>
                                <select wire:model="targetBranchId" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                                    <option value="">Seleccioná una sucursal...</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end justify-end gap-2">
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" wire:click="toggleSelectAll" {{ $selectAll ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                    Seleccionar todos disponibles
                                </label>
                            </div>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white max-h-72 overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 sticky top-0">
                                        <th class="px-4 py-2.5 text-center w-12"></th>
                                        <th class="px-4 py-2.5 text-left text-gray-600 font-medium">MAC Address</th>
                                        <th class="px-4 py-2.5 text-left text-gray-600 font-medium">Sucursal actual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($devices as $d)
                                    <tr class="hover:bg-gray-50/50 transition {{ $d['branch_id'] ? 'bg-gray-50 opacity-60' : (in_array($d['id'], $selectedDevices) ? 'bg-blue-50/50' : '') }}">
                                        <td class="px-4 py-2.5 text-center">
                                            @if(!$d['branch_id'])
                                            <input type="checkbox" wire:click="toggleDevice({{ $d['id'] }})" {{ in_array($d['id'], $selectedDevices) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 font-mono text-xs text-gray-800">{{ $d['mac_address'] }}</td>
                                        <td class="px-4 py-2.5">
                                            @if($d['branch_name'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">{{ $d['branch_name'] }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">Sin asignar</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end pt-2 border-t border-gray-200">
                            <button type="button" wire:click="assign" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-base">fork_right</span>
                                Asignar {{ count($selectedDevices) }} dispositivo(s)
                            </button>
                        </div>
                    </div>
                    @endif
                @else
                    {{-- Distribución por cantidad (material sin MAC) --}}
                    @if($available > 0)
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-500 text-base">assignment</span>
                            Asignar por cantidad
                        </h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium text-xs uppercase">Sucursal</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium text-xs uppercase w-36">Ya asignado</th>
                                        <th class="px-4 py-3 text-center text-gray-600 font-medium text-xs uppercase w-36">Nueva cantidad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($quantityAllocations as $index => $alloc)
                                    <tr class="hover:bg-blue-50/30 transition">
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $alloc['branch_name'] }}</td>
                                        <td class="px-4 py-3 text-center font-mono text-gray-700">{{ (int) $alloc['current_allocated'] }}</td>
                                        <td class="px-4 py-3">
                                            <input type="number" wire:model.live="quantityAllocations.{{ $index }}.new_quantity" step="any" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-center font-mono" placeholder="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end pt-2 border-t border-gray-200">
                            <button type="button" wire:click="assign" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-base">save</span>
                                Guardar repartición
                            </button>
                        </div>
                    </div>
                    @endif
                @endif

                @if(count($branchAllocations) > 0 && $alreadyAllocated > 0)
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-base">store</span>
                        {{ $requiresDevice ? 'Dispositivos ya asignados por sucursal' : 'Stock ya repartido por sucursal' }}
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($branchAllocations as $ba)
                            @if($ba['count'] > 0)
                            <div class="bg-white rounded-lg border border-gray-200 p-3 flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ $ba['branch_name'] }}</span>
                                <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 rounded-md text-sm font-mono font-medium">{{ (int) $ba['count'] }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                @if($available == 0 && $alreadyAllocated > 0)
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border-2 border-dashed border-gray-300 py-8 text-center">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">check_circle</span>
                    <p class="text-gray-600 font-medium">Todo el stock ya está asignado a sucursales</p>
                </div>
                @endif
            @else
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border-2 border-dashed border-gray-300 py-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-3xl">inventory_2</span>
                    </div>
                    <p class="text-gray-600 font-medium">Seleccioná un producto para repartir</p>
                    <p class="text-sm text-gray-400 mt-1">Usa el buscador de arriba para encontrar un producto con dispositivos registrados.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de productos --}}
    <div x-data="{ show: @entangle('showProductModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar producto</h3>
                            <p class="text-xs text-gray-500">Elegí un producto de la lista</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeProductModal"
                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="productListSearch"
                            placeholder="Filtrar por nombre o SKU..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($productList as $p)
                        <button type="button" wire:click="selectProductFromList({{ $p->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-blue-600">inventory_2</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ $p->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $p->sku }}</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-lg flex-shrink-0">chevron_right</span>
                        </button>
                    @empty
                        <div class="py-12 text-center">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">search_off</span>
                            <p class="text-gray-500 text-sm">No se encontraron productos</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeProductModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cerrar</button>
                </div>
            </div>
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
