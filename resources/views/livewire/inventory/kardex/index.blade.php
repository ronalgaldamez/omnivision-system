<div class="max-w-full mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">finance_chip</span>
                        Tarjeta de Control de Inventario
                    </h1>
                    <div class="flex flex-wrap gap-x-6 gap-y-1 mt-1">
                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">calculate</span>
                            Método: Costo Promedio Ponderado
                        </p>
                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">business</span>
                            Empresa: OMNIVISIÓN
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros -->
            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="w-52">
                        <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                            Producto
                        </label>
                        <div class="relative">
                            <select wire:model.live="product_id"
                                class="w-full pl-8 pr-8 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Todos</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            <span
                                class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">unfold_more</span>
                            <span
                                class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-base">expand_more</span>
                        </div>
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-gray-400 text-sm">filter_alt</span>
                            Tipo
                        </label>
                        <div class="relative">
                            <select wire:model.live="type"
                                class="w-full pl-8 pr-8 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Todos</option>
                                <option value="entry">Entrada</option>
                                <option value="exit">Salida</option>
                                <option value="technician_out">Salida a técnico</option>
                                <option value="technician_return">Devolución técnico</option>
                                <option value="damage">Dañado</option>
                                <option value="return_to_supplier">Dev. proveedor</option>
                            </select>
                            <span
                                class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">swap_vert</span>
                            <span
                                class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-base">expand_more</span>
                        </div>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-gray-400 text-sm">calendar_today</span>
                            Desde
                        </label>
                        <div class="relative">
                            <input type="date" wire:model.live="date_from"
                                class="w-full pl-8 pr-2 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">event</span>
                        </div>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-gray-400 text-sm">event_available</span>
                            Hasta
                        </label>
                        <div class="relative">
                            <input type="date" wire:model.live="date_to"
                                class="w-full pl-8 pr-2 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span
                                class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">event</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Kardex estilo tarjeta de control -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th rowspan="2"
                                class="px-3 py-3 text-center text-gray-600 font-medium border-r border-gray-200 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-base">format_list_numbered</span>
                                    No.
                                </div>
                            </th>
                            <th rowspan="2"
                                class="px-3 py-3 text-center text-gray-600 font-medium border-r border-gray-200 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    FECHA
                                </div>
                            </th>
                            <th rowspan="2"
                                class="px-3 py-3 text-center text-gray-600 font-medium border-r border-gray-200 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                                    DESCRIPCIÓN
                                </div>
                            </th>
                            <th colspan="3"
                                class="px-3 py-2 text-center text-gray-600 font-medium border-b border-gray-200 border-r border-gray-200 bg-blue-50/30">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-blue-600 text-base">arrow_upward</span>
                                    ENTRADAS
                                </span>
                            </th>
                            <th colspan="3"
                                class="px-3 py-2 text-center text-gray-600 font-medium border-b border-gray-200 border-r border-gray-200 bg-red-50/30">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-red-600 text-base">arrow_downward</span>
                                    SALIDAS
                                </span>
                            </th>
                            <th colspan="3"
                                class="px-3 py-2 text-center text-gray-600 font-medium border-b border-gray-200 bg-gray-100/50">
                                <span class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-gray-600 text-base">inventory</span>
                                    EXISTENCIAS
                                </span>
                            </th>
                        </tr>
                        <tr class="bg-gray-50">
                            <!-- Subencabezados ENTRADAS -->
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                UNIDADES</th>
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                C.U.</th>
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                C.T.</th>
                            <!-- Subencabezados SALIDAS -->
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                UNIDADES</th>
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                C.U.</th>
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                C.T.</th>
                            <!-- Subencabezados EXISTENCIAS -->
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                UNIDADES</th>
                            <th
                                class="px-2 py-2 text-center text-gray-500 text-xs font-medium border-r border-gray-200">
                                C.U.</th>
                            <th class="px-2 py-2 text-center text-gray-500 text-xs font-medium">C.T.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-3 py-2 border-r border-gray-100 text-center text-gray-700">
                                    {{ $item->line_number }}</td>
                                <td class="px-3 py-2 border-r border-gray-100 text-center text-gray-700 font-mono text-xs">
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-2 border-r border-gray-100">
                                    @if($item->type == 'entry')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">shopping_cart</span> COMPRA
                                        </span>
                                    @elseif($item->type == 'exit')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">logout</span> SALIDA
                                        </span>
                                    @elseif($item->type == 'technician_out')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">engineering</span> INSTALACIÓN
                                        </span>
                                    @elseif($item->type == 'technician_return')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">assignment_return</span> DEVOLUCIÓN
                                        </span>
                                    @elseif($item->type == 'damage')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">broken_image</span> DAÑADO
                                        </span>
                                    @elseif($item->type == 'return_to_supplier')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">local_shipping</span> DEV. PROVEEDOR
                                        </span>
                                    @else
                                        <span class="text-gray-600 text-xs">{{ strtoupper($item->type) }}</span>
                                    @endif
                                </td>
                                <!-- ENTRADAS -->
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700">
                                    {{ $item->entry_qty ?? '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">
                                    {{ $item->entry_cost !== null ? number_format($item->entry_cost, 4) : '' }}
                                </td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">
                                    {{ $item->entry_total !== null ? number_format($item->entry_total, 2) : '' }}
                                </td>
                                <!-- SALIDAS -->
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700">
                                    {{ $item->exit_qty ?? '' }}</td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">
                                    {{ $item->exit_cost !== null ? number_format($item->exit_cost, 4) : '' }}
                                </td>
                                <td class="px-2 py-2 border-r border-gray-100 text-right text-gray-700 font-mono">
                                    {{ $item->exit_total !== null ? number_format($item->exit_total, 2) : '' }}
                                </td>
                                <!-- EXISTENCIAS -->
                                <td class="px-2 py-2 border-r border-gray-100 text-right font-medium text-gray-800">
                                    {{ $item->balance_qty }}</td>
                                <td
                                    class="px-2 py-2 border-r border-gray-100 text-right font-medium text-gray-800 font-mono">
                                    {{ number_format($item->balance_cost, 4) }}
                                </td>
                                <td class="px-2 py-2 text-right font-medium text-gray-800 font-mono">
                                    {{ number_format($item->balance_total, 2) }}
                                </td>
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

            <!-- Opcional: Resumen o totales -->
            @if(count($items) > 0)
                <div class="text-right text-xs text-gray-500 flex items-center justify-end gap-2">
                    <span class="material-symbols-outlined text-gray-400 text-sm">info</span>
                    Mostrando {{ count($items) }} movimientos
                </div>
            @endif
        </div>
    </div>
</div>