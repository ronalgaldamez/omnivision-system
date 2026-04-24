<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">inventory</span>
                    Movimientos de Inventario
                </h1>
                <p class="text-sm text-gray-500 mt-1">Historial de entradas y salidas de productos</p>
            </div>
            <a href="{{ route('movements.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nuevo movimiento
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por producto o SKU..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="typeFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Todos los tipos</option>
                        <option value="entry">Entrada</option>
                        <option value="exit">Salida</option>
                        <option value="technician_out">Salida a técnico</option>
                        <option value="technician_return">Devolución de técnico</option>
                        <option value="damage">Dañado</option>
                        <option value="return_to_supplier">Devolución a proveedor</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">calendar_today</span>
                    <input type="date" wire:model.live="dateFrom"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                    <input type="date" wire:model.live="dateTo"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
            </div>

            <!-- Tabla de movimientos -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    Fecha
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                    Producto
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">swap_vert</span>
                                    Tipo
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                    Cantidad
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">attach_money</span>
                                    Costo unitario
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                    Usuario
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">link</span>
                                    Referencia
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $mov)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $mov->product->name }}</td>
                                <td class="px-4 py-3">
                                    @if($mov->type == 'entry')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">arrow_upward</span> Entrada
                                        </span>
                                    @elseif($mov->type == 'exit')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">arrow_downward</span> Salida
                                        </span>
                                    @elseif($mov->type == 'technician_out')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">engineering</span> Salida a técnico
                                        </span>
                                    @elseif($mov->type == 'technician_return')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">assignment_return</span> Devolución
                                            técnico
                                        </span>
                                    @elseif($mov->type == 'damage')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">broken_image</span> Dañado
                                        </span>
                                    @elseif($mov->type == 'return_to_supplier')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">local_shipping</span> Dev. proveedor
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-gray-800">{{ $mov->quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    {{ $mov->unit_cost ? '$' . number_format($mov->unit_cost, 2) : '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if($mov->reference_type && $mov->reference_id)
                                        <span class="bg-gray-100 px-2 py-0.5 rounded-full">{{ $mov->reference_type }}:
                                            #{{ $mov->reference_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay movimientos registrados</p>
                                    <p class="text-sm text-gray-400 mt-1">Los movimientos que agregues aparecerán aquí</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($movements->hasPages())
                <div class="mt-5">
                    {{ $movements->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión (Toast-like inline) -->
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div
                    class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>