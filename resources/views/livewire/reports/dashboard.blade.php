<div class="max-w-7xl mx-auto">
    <x-ui.card title="Dashboard" icon="dashboard" subtitle="Resumen general de la actividad del sistema">
        <div class="space-y-6">
            {{-- Tarjetas superiores (se muestran según permisos) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Stock bajo --}}
                @if(!is_null($lowStockCount))
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
                @endif

                {{-- Requisiciones pendientes (generales) --}}
                @if(!is_null($pendingRequisitionsCount))
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-yellow-500 text-base">inventory_2</span>
                                Requisiciones pendientes
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingRequisitionsCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-yellow-100 text-3xl">hourglass_top</span>
                    </div>
                    @can('view requisitions')
                        <a href="{{ route('technician.requisitions.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Gestionar
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    @endcan
                </div>
                @endif

                {{-- Órdenes activas (generales) --}}
                @if(!is_null($activeWorkOrdersCount))
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
                @endif

                {{-- Movimientos hoy --}}
                @if(!is_null($todayMovementsCount))
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
                @endif
            </div>

            {{-- SECCIÓN: Panel del Técnico (solo si tiene permiso) --}}
            @if(!is_null($techPendingRequisitionsCount) || !is_null($techActiveWorkOrdersCount))
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">home_repair_service</span>
                    Mi Panel de Control
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Mis Requisiciones pendientes --}}
                    @if(!is_null($techPendingRequisitionsCount))
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-yellow-500 text-base">inventory_2</span>
                                    Mis requisiciones pendientes
                                </p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $techPendingRequisitionsCount }}</p>
                            </div>
                            <span class="material-symbols-outlined text-yellow-100 text-3xl">hourglass_top</span>
                        </div>
                        <a href="{{ route('technician.requisitions.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Ver
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                    @endif

                    {{-- Mis Órdenes activas --}}
                    @if(!is_null($techActiveWorkOrdersCount))
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-blue-500 text-base">engineering</span>
                                    Mis órdenes activas
                                </p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $techActiveWorkOrdersCount }}</p>
                            </div>
                            <span class="material-symbols-outlined text-blue-100 text-3xl">work</span>
                        </div>
                        <a href="{{ route('mobile.work-orders.list') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Ver
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Mis últimas requisiciones --}}
                @if(!is_null($techRecentRequisitions) && $techRecentRequisitions->count())
                <div class="mt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-gray-500">history</span>
                        Mis últimas requisiciones
                    </h2>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Fecha</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Productos</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($techRecentRequisitions as $req)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-gray-800">
                                            @foreach($req->items as $item)
                                                <div class="text-sm">{{ $item->product->name }} ({{ $item->quantity_requested }})</div>
                                            @endforeach
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($req->status == 'open')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                                    Abierta
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                                    <span class="material-symbols-outlined text-sm">lock</span>
                                                    Cerrada
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Últimos movimientos (sección común) --}}
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">history</span>
                    Últimos movimientos
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Fecha</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Tipo</th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">Cantidad</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if(isset($recentMovements) && $recentMovements->count())
                                @foreach($recentMovements as $mov)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $mov->product->name }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @php
                                                $typeLabels = [
                                                    'entry' => ['Entrada', 'green'],
                                                    'exit' => ['Salida', 'red'],
                                                    'technician_out' => ['Salida a técnico', 'orange'],
                                                    'technician_return' => ['Devolución técnico', 'blue'],
                                                    'requisition_out' => ['Salida requisición', 'purple'],
                                                    'damage' => ['Dañado', 'red'],
                                                ];
                                                $label = $typeLabels[$mov->type] ?? [$mov->type, 'gray'];
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-{{ $label[1] }}-50 text-{{ $label[1] }}-700 rounded-full text-xs font-medium">
                                                {{ $label[0] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-800">{{ $mov->quantity }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                        <p class="text-gray-500">No hay movimientos recientes</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-ui.card>
</div>
