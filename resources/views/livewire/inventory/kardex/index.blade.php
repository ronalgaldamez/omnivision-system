<div class="max-w-full mx-auto">
    <x-ui.card title="Tarjeta de Control de Inventario" icon="finance_chip">
        <x-slot:headerActions>
            <span class="flex items-center gap-1.5 text-sm text-gray-500">
                <span class="material-symbols-outlined text-gray-400 text-base">calculate</span>
                Método: Costo Promedio Ponderado
            </span>
            <span class="flex items-center gap-1.5 text-sm text-gray-500">
                <span class="material-symbols-outlined text-gray-400 text-base">business</span>
                Empresa: OMNIVISIÓN
            </span>
        </x-slot:headerActions>

        <div class="space-y-5">
            @if($activeBranch)
                <x-ui.alert variant="info">
                    Filtrando por sucursal: <strong>{{ $activeBranch->name }}</strong>
                </x-ui.alert>
            @endif

            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                            Producto
                        </label>
                        <div class="flex gap-2 items-stretch">
                            <div class="relative flex-1">
                                <input type="text" wire:model.live.debounce.300ms="productSearch"
                                    placeholder="Buscar por nombre o SKU..."
                                    class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                            </div>
                            @if($product_id)
                                <span wire:click="clearProduct" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                                    <span class="material-symbols-outlined text-base">close</span>
                                </span>
                            @endif
                            <button type="button" wire:click="openProductModal"
                                class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                title="Ver todos los productos">
                                <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                <span class="hidden sm:inline">Ver todos</span>
                            </button>
                        </div>
                        @if(count($productResults) > 0 && !$product_id)
                            <div class="relative">
                                <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                    @foreach($productResults as $product)
                                        <li wire:click="selectProduct('{{ $product->id }}', '{{ $product->name }} ({{ $product->sku }})')"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                            <span class="font-medium text-gray-800">{{ $product->name }}</span>
                                            <span class="text-xs text-gray-500">({{ $product->sku }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <x-ui.select icon="swap_vert" wire:model.live="type" placeholder="Todos" label="Tipo">
                        <option value="entry">Entrada</option>
                        <option value="exit">Salida</option>
                        <option value="technician_out">Salida a técnico</option>
                        <option value="technician_return">Devolución técnico</option>
                        <option value="damage">Dañado</option>
                        <option value="return_to_supplier">Dev. proveedor</option>
                        <option value="requisition_out">Requisición</option>
                    </x-ui.select>

                    <x-ui.input type="date" icon="event" wire:model.live="date_from" label="Desde" />
                    <x-ui.input type="date" icon="event" wire:model.live="date_to" label="Hasta" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th rowspan="2" class="px-3 py-3 text-center text-gray-600 font-medium border-r border-gray-200 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-base">format_list_numbered</span>
                                    No.
                                </div>
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center text-gray-600 font-medium border-r border-gray-200 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    FECHA
                                </div>
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center text-gray-600 font-medium border-r border-gray-200 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                    DESCRIPCIÓN
                                </div>
                            </th>
                            <th colspan="3" class="px-3 py-2 text-center text-gray-600 font-medium border-b border-gray-200 border-r border-gray-200 bg-blue-50/30">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-blue-600 text-base">arrow_upward</span>
                                    ENTRADAS
                                </span>
                            </th>
                            <th colspan="3" class="px-3 py-2 text-center text-gray-600 font-medium border-b border-gray-200 border-r border-gray-200 bg-red-50/30">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-red-600 text-base">arrow_downward</span>
                                    SALIDAS
                                </span>
                            </th>
                            <th colspan="3" class="px-3 py-2 text-center text-gray-600 font-medium border-b border-gray-200 bg-gray-100/50">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-gray-600 text-base">inventory</span>
                                    EXISTENCIAS
                                </span>
                            </th>
                        </tr>
                        <tr class="bg-gray-50">
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">UNIDADES</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">C.U.</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">C.T.</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">UNIDADES</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">C.U.</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">C.T.</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">UNIDADES</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">C.U.</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium">C.T.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-3 py-2 border-r border-gray-100 text-center text-gray-700">{{ $item->line_number }}</td>
                                <td class="px-3 py-2 border-r border-gray-100 text-center text-gray-700 font-mono text-xs">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 border-r border-gray-100">
                                    @if($item->type == 'entry')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">shopping_cart</span> COMPRA
                                        </span>
                                    @elseif($item->type == 'exit')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">logout</span> SALIDA
                                        </span>
                                    @elseif($item->type == 'technician_out')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">engineering</span> INSTALACIÓN
                                        </span>
                                    @elseif($item->type == 'technician_return')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">assignment_return</span> DEVOLUCIÓN
                                        </span>
                                    @elseif($item->type == 'damage')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">broken_image</span> DAÑADO
                                        </span>
                                    @elseif($item->type == 'return_to_supplier')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">local_shipping</span> DEV. PROVEEDOR
                                        </span>
                                    @elseif($item->type == 'requisition_out')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">handyman</span> REQUISICIÓN
                                        </span>
                                    @elseif($item->type == 'branch_allocation')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-teal-50 text-teal-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">store</span> REPARTICIÓN
                                        </span>
                                    @else
                                        <span class="text-gray-600 text-xs">{{ strtoupper($item->type) }}</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700">{{ $item->entry_qty ?? '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">{{ $item->entry_cost !== null ? number_format($item->entry_cost, 4) : '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">{{ $item->entry_total !== null ? number_format($item->entry_total, 2) : '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700">{{ $item->exit_qty ?? '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">{{ $item->exit_cost !== null ? number_format($item->exit_cost, 4) : '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">{{ $item->exit_total !== null ? number_format($item->exit_total, 2) : '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right font-medium text-gray-800">{{ $item->balance_qty }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right font-medium text-gray-800 font-mono">{{ number_format($item->balance_cost, 4) }}</td>
                                <td class="px-2 py-2 text-right font-medium text-gray-800 font-mono">{{ number_format($item->balance_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">finance</span>
                                    <p class="text-gray-500">No hay movimientos en el rango seleccionado</p>
                                    <p class="text-sm text-gray-400 mt-1">Ajusta los filtros para ver el kardex</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(count($items) > 0)
                <div class="text-right text-xs text-gray-500 flex items-center justify-end gap-2">
                    <span class="material-symbols-outlined text-gray-400 text-sm">info</span>
                    Mostrando {{ count($items) }} movimientos
                </div>
            @endif
        </div>
    </x-ui.card>

    <div x-data="{ show: @entangle('showProductModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar producto</h3>
                            <p class="text-xs text-gray-500">Elegí un producto para ver su kardex</p>
                        </div>
                    </div>
                    <span wire:click="closeProductModal" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition cursor-pointer">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </span>
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
                    <x-ui.button variant="secondary" wire:click="closeProductModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
