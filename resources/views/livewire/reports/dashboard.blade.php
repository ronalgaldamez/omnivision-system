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

            {{-- Tarjetas financieras (nuevos KPI) --}}
            @if(!is_null($inventoryValue) || !is_null($todayEntries) || !is_null($todayExits) || !is_null($monthlyPurchasesTotal))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if(!is_null($inventoryValue))
                <div class="bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-200/80 shadow-sm p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-indigo-500 text-base">payments</span>
                                Valor del inventario
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($inventoryValue, 2) }}</p>
                        </div>
                        <span class="material-symbols-outlined text-indigo-100 text-3xl">inventory</span>
                    </div>
                    @if(module_active('reports'))
                        <a href="{{ route('reports.stock') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 transition">
                            Ver reporte
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    @endif
                </div>
                @endif

                @if(!is_null($todayEntries))
                <div class="bg-gradient-to-br from-green-50 to-white rounded-xl border border-green-200/80 shadow-sm p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-green-500 text-base">login</span>
                                Entradas hoy
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayEntries }}</p>
                        </div>
                        <span class="material-symbols-outlined text-green-100 text-3xl">arrow_downward</span>
                    </div>
                    <a href="{{ route('movements.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 transition">
                        Ver movimientos
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>
                @endif

                @if(!is_null($todayExits))
                <div class="bg-gradient-to-br from-orange-50 to-white rounded-xl border border-orange-200/80 shadow-sm p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-orange-500 text-base">logout</span>
                                Salidas hoy
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayExits }}</p>
                        </div>
                        <span class="material-symbols-outlined text-orange-100 text-3xl">arrow_upward</span>
                    </div>
                    <a href="{{ route('movements.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-orange-600 hover:text-orange-800 transition">
                        Ver movimientos
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>
                @endif

                @if(!is_null($monthlyPurchasesTotal))
                <div class="bg-gradient-to-br from-cyan-50 to-white rounded-xl border border-cyan-200/80 shadow-sm p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-cyan-500 text-base">shopping_cart</span>
                                Compras del mes
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($monthlyPurchasesTotal, 2) }}</p>
                            @if(!is_null($monthlyPurchasesCount))
                                <p class="text-xs text-gray-400 mt-0.5">{{ $monthlyPurchasesCount }} compra(s)</p>
                            @endif
                        </div>
                        <span class="material-symbols-outlined text-cyan-100 text-3xl">receipt_long</span>
                    </div>
                    <a href="{{ route('purchases.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-cyan-600 hover:text-cyan-800 transition">
                        Ver compras
                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- Dispositivos por estado + Top productos --}}
            @if(!is_null($devicesByStatus) || !is_null($topProducts))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @if(!is_null($devicesByStatus))
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-base">router</span>
                        Dispositivos por estado
                    </h3>
                    <div class="space-y-2">
                        @php
                            $statusColors = [
                                'in_stock' => ['bg-green-100 text-green-800', 'check_circle'],
                                'assigned' => ['bg-blue-100 text-blue-800', 'person_pin'],
                                'installed' => ['bg-purple-100 text-purple-800', 'home_pin'],
                                'damaged' => ['bg-red-100 text-red-800', 'report_problem'],
                            ];
                        @endphp
                        @foreach($devicesByStatus as $status => $count)
                            @php
                                $color = $statusColors[$status] ?? ['bg-gray-100 text-gray-800', 'help'];
                            @endphp
                            <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 transition">
                                <span class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                    <span class="material-symbols-outlined text-base {{ Str::after($color[0], 'text-') }}">{{ $color[1] }}</span>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                                <span class="inline-flex items-center justify-center min-w-[2rem] h-6 px-2 rounded-full text-xs font-medium {{ $color[0] }}">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!is_null($topProducts) && $topProducts->count())
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-gray-500 text-base">trending_up</span>
                        Top 10 productos más movidos
                    </h3>
                    <div class="overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="pb-2 text-left text-gray-500 font-medium">Producto</th>
                                    <th class="pb-2 text-right text-gray-500 font-medium">Movimientos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($topProducts as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="py-1.5 text-gray-800 truncate max-w-[200px]">{{ $item->product?->name ?? 'Sin nombre' }}</td>
                                    <td class="py-1.5 text-right font-mono text-gray-700">{{ number_format($item->total_moved) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Clientes nuevos hoy/este mes --}}
            @if(!is_null($newClientsToday) || !is_null($newClientsThisMonth))
            <div class="flex items-center gap-4 flex-wrap">
                @if(!is_null($newClientsToday))
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-teal-500">person_add</span>
                    <div>
                        <p class="text-xs text-gray-500">Clientes nuevos hoy</p>
                        <p class="text-lg font-bold text-gray-800">{{ $newClientsToday }}</p>
                    </div>
                </div>
                @endif
                @if(!is_null($newClientsThisMonth))
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-teal-500">group_add</span>
                    <div>
                        <p class="text-xs text-gray-500">Clientes nuevos este mes</p>
                        <p class="text-lg font-bold text-gray-800">{{ $newClientsThisMonth }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Órdenes de trabajo: pendientes vs completadas --}}
            @if(!is_null($pendingWorkOrders) || !is_null($completedWorkOrders))
            <div class="flex items-center gap-4 flex-wrap">
                @if(!is_null($pendingWorkOrders))
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-500">pending_actions</span>
                    <div>
                        <p class="text-xs text-gray-500">OTs pendientes</p>
                        <p class="text-lg font-bold text-gray-800">{{ $pendingWorkOrders }}</p>
                    </div>
                </div>
                @endif
                @if(!is_null($completedWorkOrders))
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-500">task_alt</span>
                    <div>
                        <p class="text-xs text-gray-500">OTs completadas</p>
                        <p class="text-lg font-bold text-gray-800">{{ $completedWorkOrders }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Últimas compras --}}
            @if(!is_null($recentPurchases) && $recentPurchases->count())
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">receipt</span>
                    Últimas compras
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Factura</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Proveedor</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Fecha</th>
                                <th class="px-4 py-3 text-right text-gray-600 font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentPurchases as $purchase)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $purchase->invoice_number }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $purchase->supplier?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right font-mono text-gray-800">${{ number_format($purchase->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

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
