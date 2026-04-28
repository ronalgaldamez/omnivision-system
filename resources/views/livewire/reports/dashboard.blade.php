<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">dashboard</span>
                Dashboard
            </h1>
            <p class="text-sm text-gray-500 mt-1">Resumen general de la actividad del sistema</p>
        </div>

    <!-- Últimos movimientos -->
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-medium mb-2">Últimos movimientos</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Fecha</th>
                        <th class="px-3 py-2 text-left">Producto</th>
                        <th class="px-3 py-2 text-center">Tipo</th>
                        <th class="px-3 py-2 text-right">Cantidad</th>
                        <th class="px-3 py-2 text-left">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentMovements as $mov)
                        <tr class="border-b">
                            <td class="px-3 py-1">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-1">{{ $mov->product->name }}</td>
                            <td class="px-3 py-1 text-center">{{ $mov->type }}</td>
                            <td class="px-3 py-1 text-right">{{ $mov->quantity }}</td>
                            <td class="px-3 py-1">{{ $mov->user->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Tarjetas resumen -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stock bajo -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-red-500 text-base">inventory</span>
                                Stock bajo
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $lowStockCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-red-100 text-3xl">warning</span>
                    </div>
                    @if(module_active('reports'))
                        <a href="{{ route('reports.stock') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Ver reporte
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    @endif
                </div>

                <!-- Solicitudes pendientes -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-yellow-500 text-base">inbox</span>
                                Solicitudes pendientes
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingRequestsCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-yellow-100 text-3xl">hourglass_top</span>
                    </div>
                    <a href="{{ route('technician-requests.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                        Gestionar
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>

                <!-- Órdenes activas -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-blue-500 text-base">engineering</span>
                                Órdenes activas
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $activeWorkOrdersCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-blue-100 text-3xl">work</span>
                    </div>
                    <a href="{{ route('work-orders.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                        Ver
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>

                <!-- Movimientos hoy -->
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-green-500 text-base">swap_vert</span>
                                Movimientos hoy
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayMovementsCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-green-100 text-3xl">today</span>
                    </div>
                    <a href="{{ route('movements.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                        Ver
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Últimos movimientos -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">history</span>
                    Últimos movimientos
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                        Fecha
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                        Producto
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                    <div class="flex items-center justify-center gap-1.5">
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
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                        Usuario
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentMovements as $mov)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $mov->product->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($mov->type == 'entry')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">arrow_upward</span> Entrada
                                            </span>
                                        @elseif($mov->type == 'exit')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">arrow_downward</span> Salida
                                            </span>
                                        @elseif($mov->type == 'technician_out')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">engineering</span> Salida a técnico
                                            </span>
                                        @elseif($mov->type == 'technician_return')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">assignment_return</span> Devolución técnico
                                            </span>
                                        @elseif($mov->type == 'damage')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                                <span class="material-symbols-outlined text-sm">broken_image</span> Dañado
                                            </span>
                                        @else
                                            <span class="text-xs">{{ $mov->type }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-800">{{ $mov->quantity }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                        <p class="text-gray-500">No hay movimientos recientes</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>