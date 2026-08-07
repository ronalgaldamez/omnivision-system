<div class="max-w-7xl mx-auto">
    <x-ui.card title="Dashboard" icon="dashboard" subtitle="Resumen general de la actividad del sistema">
        <div class="space-y-6">
            {{-- ========== 1. KPI COMPACTOS (una fila) ========== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if (!is_null($lowStockCount))
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-red-500 text-sm">inventory</span>
                                    Stock bajo
                                </p>
                                <p class="text-xl font-bold text-gray-800 mt-0.5">{{ $lowStockCount }}</p>
                            </div>
                            <span class="material-symbols-outlined text-red-100 text-2xl">warning</span>
                        </div>
                    </div>
                @endif

                @if (!is_null($pendingRequisitionsCount))
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-yellow-500 text-sm">inventory_2</span>
                                    Requisiciones
                                </p>
                                <p class="text-xl font-bold text-gray-800 mt-0.5">{{ $pendingRequisitionsCount }}</p>
                            </div>
                            <span class="material-symbols-outlined text-yellow-100 text-2xl">hourglass_top</span>
                        </div>
                    </div>
                @endif

                @if (!is_null($activeWorkOrdersCount))
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-blue-500 text-sm">engineering</span>
                                    OTs activas
                                </p>
                                <p class="text-xl font-bold text-gray-800 mt-0.5">{{ $activeWorkOrdersCount }}</p>
                            </div>
                            <span class="material-symbols-outlined text-blue-100 text-2xl">work</span>
                        </div>
                    </div>
                @endif

                @if (!is_null($todayMovementsCount))
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-green-500 text-sm">swap_vert</span>
                                    Movimientos hoy
                                </p>
                                <p class="text-xl font-bold text-gray-800 mt-0.5">{{ $todayMovementsCount }}</p>
                            </div>
                            <span class="material-symbols-outlined text-green-100 text-2xl">today</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ========== 2. GRÁFICOS (PROTAGONISTA) ========== --}}
            @if (
                $monthlyMovements ||
                    $monthlyWorkOrders ||
                    $ticketsByStatus ||
                    $ticketsByPriority ||
                    $devicesChart ||
                    $workOrdersChart ||
                    $newClientsChart ||
                    $purchasesChart)

                <div class="pt-2">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500 text-lg">monitoring</span>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Gráficos y tendencias
                        </h3>
                    </div>

                    @if ($instalacionesComparison)
                        <div class="mb-4">
                            <x-ui.alert variant="info" icon="trending_up"
                                title="Instalaciones completadas este mes vs mes anterior">
                                <span class="font-bold text-gray-800">{{ $instalacionesComparison['current'] }}</span>
                                instalaciones este mes
                                ({{ $instalacionesComparison['previous'] }} el mes pasado)
                                @if ($instalacionesComparison['pct'] > 0)
                                    · <span
                                        class="font-semibold text-green-600">+{{ $instalacionesComparison['pct'] }}%</span>
                                @elseif($instalacionesComparison['pct'] < 0)
                                    · <span
                                        class="font-semibold text-red-600">{{ $instalacionesComparison['pct'] }}%</span>
                                @else
                                    · <span class="font-semibold text-gray-600">sin cambio</span>
                                @endif
                            </x-ui.alert>
                        </div>
                    @endif

                    {{-- Fila 1: Movimientos (2/3) + Tickets por estado (1/3) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        @if ($monthlyMovements)
                            <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                                <x-charts.apex type="area" title="Movimientos últimos 12 meses"
                                    subtitle="Entradas vs Salidas" height="280" :categories="$monthlyMovements['labels']" :series="[
                                        ['name' => 'Entradas', 'data' => $monthlyMovements['entries']],
                                        ['name' => 'Salidas', 'data' => $monthlyMovements['exits']],
                                    ]" />
                            </div>
                        @endif

                        @if ($ticketsByStatus && array_sum($ticketsByStatus['series']) > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                <x-charts.apex type="donut" title="Tickets por estado" height="280"
                                    :labels="$ticketsByStatus['labels']" :series="$ticketsByStatus['series']" />
                            </div>
                        @endif
                    </div>

                    {{-- Fila 2: OTs por mes (2/3) + Tickets por prioridad (1/3) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        @if ($monthlyWorkOrders)
                            <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                                <x-charts.apex type="bar" title="Órdenes de trabajo por mes"
                                    subtitle="Creadas vs Completadas" height="280" :categories="$monthlyWorkOrders['labels']"
                                    :series="[
                                        ['name' => 'Creadas', 'data' => $monthlyWorkOrders['created']],
                                        ['name' => 'Completadas', 'data' => $monthlyWorkOrders['completed']],
                                    ]" />
                            </div>
                        @endif

                        @if ($ticketsByPriority && array_sum($ticketsByPriority['series']) > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                <x-charts.apex type="donut" title="Tickets por prioridad" height="280"
                                    :labels="$ticketsByPriority['labels']" :series="$ticketsByPriority['series']" />
                            </div>
                        @endif
                    </div>

                    {{-- Fila 3: Dispositivos por estado (2/3) + OTs por estado (1/3) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        @if ($devicesChart && array_sum($devicesChart['series']) > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                                <x-charts.apex type="donut" title="Dispositivos por estado" height="280"
                                    :labels="$devicesChart['labels']" :series="$devicesChart['series']" />
                            </div>
                        @endif

                        @if ($workOrdersChart && array_sum($workOrdersChart['series']) > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                <x-charts.apex type="donut" title="OTs por estado" height="280" :labels="$workOrdersChart['labels']"
                                    :series="$workOrdersChart['series']" />
                            </div>
                        @endif
                    </div>

                    {{-- Fila 4: Clientes nuevos (2/3) + Compras por mes (1/3) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        @if ($newClientsChart && array_sum($newClientsChart['series']) > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                                <x-charts.apex type="area" title="Clientes nuevos por mes" subtitle="Últimos 6 meses"
                                    height="280" :categories="$newClientsChart['labels']" :series="[['name' => 'Clientes nuevos', 'data' => $newClientsChart['series']]]" />
                            </div>
                        @endif

                        @if ($purchasesChart && array_sum($purchasesChart['series']) > 0)
                            <div class="bg-white rounded-xl border border-gray-200 p-4">
                                <x-charts.apex type="bar" title="Compras por mes" subtitle="Total en dólares"
                                    height="280" :categories="$purchasesChart['labels']" :series="[['name' => 'Compras ($)', 'data' => $purchasesChart['series']]]" />
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== 3. TARJETAS FINANCIERAS ========== --}}
            @if (!is_null($inventoryValue) || !is_null($todayEntries) || !is_null($todayExits) || !is_null($monthlyPurchasesTotal))
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @if (!is_null($inventoryValue))
                            <div
                                class="bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-200/80 shadow-sm p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-indigo-500 text-base">payments</span>
                                            Valor del inventario
                                        </p>
                                        <p class="text-2xl font-bold text-gray-800 mt-1">
                                            ${{ number_format($inventoryValue, 2) }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-indigo-100 text-3xl">inventory</span>
                                </div>
                                @if (module_active('reports'))
                                    <a href="{{ route('reports.stock') }}"
                                        class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 transition">
                                        Ver reporte
                                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if (!is_null($todayEntries))
                            <div
                                class="bg-gradient-to-br from-green-50 to-white rounded-xl border border-green-200/80 shadow-sm p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-green-500 text-base">login</span>
                                            Entradas hoy
                                        </p>
                                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayEntries }}</p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-green-100 text-3xl">arrow_downward</span>
                                </div>
                                <a href="{{ route('movements.index') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 transition">
                                    Ver movimientos
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </div>
                        @endif

                        @if (!is_null($todayExits))
                            <div
                                class="bg-gradient-to-br from-orange-50 to-white rounded-xl border border-orange-200/80 shadow-sm p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-orange-500 text-base">logout</span>
                                            Salidas hoy
                                        </p>
                                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayExits }}</p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-orange-100 text-3xl">arrow_upward</span>
                                </div>
                                <a href="{{ route('movements.index') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-xs text-orange-600 hover:text-orange-800 transition">
                                    Ver movimientos
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </div>
                        @endif

                        @if (!is_null($monthlyPurchasesTotal))
                            <div
                                class="bg-gradient-to-br from-cyan-50 to-white rounded-xl border border-cyan-200/80 shadow-sm p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-cyan-500 text-base">shopping_cart</span>
                                            Compras del mes
                                        </p>
                                        <p class="text-2xl font-bold text-gray-800 mt-1">
                                            ${{ number_format($monthlyPurchasesTotal, 2) }}</p>
                                        @if (!is_null($monthlyPurchasesCount))
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $monthlyPurchasesCount }}
                                                compra(s)</p>
                                        @endif
                                    </div>
                                    <span class="material-symbols-outlined text-cyan-100 text-3xl">receipt_long</span>
                                </div>
                                <a href="{{ route('purchases.index') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-xs text-cyan-600 hover:text-cyan-800 transition">
                                    Ver compras
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== 4. DISPOSITIVOS + TOP PRODUCTOS ========== --}}
            @if (!is_null($devicesByStatus) || !is_null($topProducts))
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @if (!is_null($devicesByStatus))
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
                                    @foreach ($devicesByStatus as $status => $count)
                                        @php
                                            $color = $statusColors[$status] ?? ['bg-gray-100 text-gray-800', 'help'];
                                        @endphp
                                        <div
                                            class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 transition">
                                            <span class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                                <span
                                                    class="material-symbols-outlined text-base {{ Str::after($color[0], 'text-') }}">{{ $color[1] }}</span>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                            <span
                                                class="inline-flex items-center justify-center min-w-[2rem] h-6 px-2 rounded-full text-xs font-medium {{ $color[0] }}">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (!is_null($topProducts) && $topProducts->count())
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
                                            @foreach ($topProducts as $item)
                                                <tr class="hover:bg-gray-50/80 transition">
                                                    <td class="py-1.5 text-gray-800 truncate max-w-[200px]">
                                                        {{ $item->product?->name ?? 'Sin nombre' }}</td>
                                                    <td class="py-1.5 text-right font-mono text-gray-700">
                                                        {{ number_format($item->total_moved) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== 5. CLIENTES NUEVOS + OTs ========== --}}
            @if (
                !is_null($newClientsToday) ||
                    !is_null($newClientsThisMonth) ||
                    !is_null($pendingWorkOrders) ||
                    !is_null($completedWorkOrders))
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center gap-4 flex-wrap">
                        @if (!is_null($newClientsToday))
                            <div
                                class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-teal-500">person_add</span>
                                <div>
                                    <p class="text-xs text-gray-500">Clientes nuevos hoy</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $newClientsToday }}</p>
                                </div>
                            </div>
                        @endif
                        @if (!is_null($newClientsThisMonth))
                            <div
                                class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-teal-500">group_add</span>
                                <div>
                                    <p class="text-xs text-gray-500">Clientes nuevos este mes</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $newClientsThisMonth }}</p>
                                </div>
                            </div>
                        @endif
                        @if (!is_null($pendingWorkOrders))
                            <div
                                class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-amber-500">pending_actions</span>
                                <div>
                                    <p class="text-xs text-gray-500">OTs pendientes</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $pendingWorkOrders }}</p>
                                </div>
                            </div>
                        @endif
                        @if (!is_null($completedWorkOrders))
                            <div
                                class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3 flex items-center gap-3">
                                <span class="material-symbols-outlined text-green-500">task_alt</span>
                                <div>
                                    <p class="text-xs text-gray-500">OTs completadas</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $completedWorkOrders }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== 6. TABLAS DETALLADAS ========== --}}

            {{-- Últimas compras --}}
            @if (!is_null($recentPurchases) && $recentPurchases->count())
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
                                @foreach ($recentPurchases as $purchase)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                            {{ $purchase->invoice_number }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $purchase->supplier?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700">
                                            {{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-800">
                                            ${{ number_format($purchase->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Panel del Técnico --}}
            @if (!is_null($techPendingRequisitionsCount) || !is_null($techActiveWorkOrdersCount))
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-gray-500">home_repair_service</span>
                        Mi Panel de Control
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if (!is_null($techPendingRequisitionsCount))
                            <div
                                class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-yellow-500 text-base">inventory_2</span>
                                            Mis requisiciones pendientes
                                        </p>
                                        <p class="text-2xl font-bold text-gray-800 mt-1">
                                            {{ $techPendingRequisitionsCount }}</p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-yellow-100 text-3xl">hourglass_top</span>
                                </div>
                                <a href="{{ route('technician.requisitions.index') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                                    Ver
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </div>
                        @endif

                        @if (!is_null($techActiveWorkOrdersCount))
                            <div
                                class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                            <span
                                                class="material-symbols-outlined text-blue-500 text-base">engineering</span>
                                            Mis órdenes activas
                                        </p>
                                        <p class="text-2xl font-bold text-gray-800 mt-1">
                                            {{ $techActiveWorkOrdersCount }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-blue-100 text-3xl">work</span>
                                </div>
                                <a href="{{ route('mobile.work-orders.list') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                                    Ver
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if (!is_null($techRecentRequisitions) && $techRecentRequisitions->count())
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
                                        @foreach ($techRecentRequisitions as $req)
                                            <tr class="hover:bg-gray-50/80 transition">
                                                <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                                    {{ $req->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="px-4 py-3 text-gray-800">
                                                    @foreach ($req->items as $item)
                                                        <div class="text-sm">{{ $item->product->name }}
                                                            ({{ $item->quantity_requested }})
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($req->status == 'open')
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                                            <span
                                                                class="material-symbols-outlined text-sm">schedule</span>
                                                            Abierta
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
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

            {{-- Últimos movimientos --}}
            @if (!is_null($recentMovements))
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
                            @if (isset($recentMovements) && $recentMovements->count())
                                @foreach ($recentMovements as $mov)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                            {{ $mov->created_at->format('d/m/Y H:i') }}</td>
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
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-{{ $label[1] }}-50 text-{{ $label[1] }}-700 rounded-full text-xs font-medium">
                                                {{ $label[0] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-800">{{ $mov->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center bg-gray-50/50">
                                        <span
                                            class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                        <p class="text-gray-500">No hay movimientos recientes</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </x-ui.card>
</div>

