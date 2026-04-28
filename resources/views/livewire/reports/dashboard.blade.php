<div>
    <h1 class="text-lg font-semibold mb-4">Dashboard</h1>

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="text-sm text-gray-500">Stock bajo</div>
            <div class="text-2xl font-bold">{{ $lowStockCount }}</div>
            @if(module_active('reports'))
                <a href="{{ route('reports.stock') }}" class="text-xs text-blue-600">Ver reporte →</a>
            @endif
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="text-sm text-gray-500">Solicitudes pendientes</div>
            <div class="text-2xl font-bold">{{ $pendingRequestsCount }}</div>
            <a href="{{ route('technician-requests.index') }}" class="text-xs text-blue-600">Gestionar →</a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="text-sm text-gray-500">Órdenes activas</div>
            <div class="text-2xl font-bold">{{ $activeWorkOrdersCount }}</div>
            <a href="{{ route('work-orders.index') }}" class="text-xs text-blue-600">Ver →</a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="text-sm text-gray-500">Movimientos hoy</div>
            <div class="text-2xl font-bold">{{ $todayMovementsCount }}</div>
            <a href="{{ route('movements.index') }}" class="text-xs text-blue-600">Ver →</a>
        </div>
    </div>

<<<<<<< Updated upstream
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
=======
        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <!-- Tarjetas resumen (solo visibles según permisos) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @can('view low stock')
                    <!-- Stock bajo -->
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-red-500 text-base">inventory</span>
                                    Stock bajo
                                </p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $lowStockCount ?? 0 }}</p>
                            </div>
                            <span class="material-symbols-outlined text-red-100 text-3xl">warning</span>
                        </div>
                        @if(module_active('reports'))
                            <a href="{{ route('reports.stock') }}"
                                class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                                Ver reporte
                                <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        @endif
                    </div>
                @endcan

                @can('view technician_requests')
                    <!-- Solicitudes pendientes -->
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-yellow-500 text-base">inbox</span>
                                    Solicitudes pendientes
                                </p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingRequestsCount ?? 0 }}</p>
                            </div>
                            <span class="material-symbols-outlined text-yellow-100 text-3xl">hourglass_top</span>
                        </div>
                        <a href="{{ route('technician-requests.index') }}"
                            class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Gestionar
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                @endcan

                @can('view work_orders')
                    <!-- Órdenes activas -->
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-blue-500 text-base">engineering</span>
                                    Órdenes activas
                                </p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $activeWorkOrdersCount ?? 0 }}</p>
                            </div>
                            <span class="material-symbols-outlined text-blue-100 text-3xl">work</span>
                        </div>
                        <a href="{{ route('work-orders.index') }}"
                            class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Ver
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                @endcan

                @can('view movements')
                    <!-- Movimientos hoy -->
                    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-green-500 text-base">swap_vert</span>
                                    Movimientos hoy
                                </p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayMovementsCount ?? 0 }}</p>
                            </div>
                            <span class="material-symbols-outlined text-green-100 text-3xl">today</span>
                        </div>
                        <a href="{{ route('movements.index') }}"
                            class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition">
                            Ver
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                @endcan
            </div>

            <!-- Últimos movimientos (solo permisos) -->
            @can('view movements')
                @if(isset($recentMovements) && $recentMovements && $recentMovements->count())
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
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                                Fecha
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <span
                                                    class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
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
                                    @foreach($recentMovements as $mov)
                                        <tr class="hover:bg-gray-50/80 transition">
                                            <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                                {{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-3 text-gray-800">{{ $mov->product->name }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($mov->type == 'entry')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                                        <span class="material-symbols-outlined text-sm">arrow_upward</span> Entrada
                                                    </span>
                                                @elseif($mov->type == 'exit')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                                        <span class="material-symbols-outlined text-sm">arrow_downward</span> Salida
                                                    </span>
                                                @elseif($mov->type == 'technician_out')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                                        <span class="material-symbols-outlined text-sm">engineering</span> Salida a
                                                        técnico
                                                    </span>
                                                @elseif($mov->type == 'technician_return')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                                        <span class="material-symbols-outlined text-sm">assignment_return</span>
                                                        Devolución técnico
                                                    </span>
                                                @elseif($mov->type == 'damage')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                                        <span class="material-symbols-outlined text-sm">broken_image</span> Dañado
                                                    </span>
                                                @else
                                                    <span class="text-xs">{{ $mov->type }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right font-mono text-gray-800">{{ $mov->quantity }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $mov->user->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endcan

            <!-- SECCIÓN: Mis tickets (secretaria / NOC) -->
            @can('view own tickets')
                @if(isset($myTickets) && $myTickets && $myTickets->count())
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500">chat</span>
                            Mis tickets recientes
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-500">Total</p>
                                <p class="text-xl font-bold">{{ $totalMyTickets ?? 0 }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-500">Pendientes</p>
                                <p class="text-xl font-bold text-yellow-600">{{ $pendingMyTickets ?? 0 }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-500">Resueltos</p>
                                <p class="text-xl font-bold text-green-600">{{ $resolvedMyTickets ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Cliente</th>
                                        <th class="px-4 py-3 text-left">Descripción</th>
                                        <th class="px-4 py-3 text-left">Estado</th>
                                        <th class="px-4 py-3 text-left">Requiere NOC</th>
                                        <th class="px-4 py-3 text-left">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myTickets as $ticket)
                                        <tr class="hover:bg-gray-50/80">
                                            <td class="px-4 py-3 font-mono">#{{ $ticket->id }}</td>
                                            <td class="px-4 py-3">{{ $ticket->client->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 truncate max-w-xs">{{ $ticket->description }}</td>
                                            <td class="px-4 py-3">
                                                @if($ticket->status == 'pending')
                                                    <span
                                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>
                                                @elseif($ticket->status == 'in_progress')
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">En
                                                        proceso</span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Resuelto</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($ticket->requires_noc)
                                                    <span
                                                        class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs">Sí</span>
                                                @else
                                                    <span class="text-gray-400">No</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endcan

            <!-- SECCIÓN: Tickets pendientes NOC -->
            @can('view pending noc tickets')
                @if(isset($pendingNocTickets) && $pendingNocTickets && $pendingNocTickets->count())
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500">settings_overscan</span>
                            Tickets pendientes de resolución remota
                        </h2>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Cliente</th>
                                        <th class="px-4 py-3 text-left">Descripción</th>
                                        <th class="px-4 py-3 text-left">Servicio</th>
                                        <th class="px-4 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingNocTickets as $ticket)
                                        <tr class="hover:bg-gray-50/80">
                                            <td class="px-4 py-3 font-mono">#{{ $ticket->id }}</td>
                                            <td class="px-4 py-3">{{ $ticket->client->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 truncate max-w-md">{{ $ticket->description }}</td>
                                            <td class="px-4 py-3">{{ $ticket->service_type ?? 'Genérico' }}</td>
                                            <td class="px-4 py-3 text-center space-x-2">
                                                <button wire:click="resolveRemote({{ $ticket->id }})"
                                                    class="px-3 py-1 bg-green-100 text-green-700 rounded-md text-xs hover:bg-green-200">
                                                    Resolver remotamente
                                                </button>
                                                <button wire:click="createWorkOrder({{ $ticket->id }})"
                                                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md text-xs hover:bg-blue-200">
                                                    Crear OT
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endcan

            <!-- SECCIÓN: Clientes recientes (solo secretaria/admin) -->
            @can('view clients')
                @if(isset($recentClients) && $recentClients && $recentClients->count())
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500">group</span>
                            Clientes recientes
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($recentClients as $client)
                                <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                                    <div>
                                        <p class="font-medium">{{ $client->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</p>
                                    </div>
                                    <a href="{{ route('tickets.create') }}?client_id={{ $client->id }}"
                                        class="text-blue-600 text-xs">Crear ticket</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endcan

            <!-- SECCIÓN: Resueltos por NOC hoy (solo NOC) -->
            @can('view resolutions')
                @if(isset($resolvedToday))
                    <div class="border-t border-gray-200 pt-6 flex justify-end">
                        <div class="bg-blue-50 rounded-lg p-3 inline-block">
                            <p class="text-xs text-gray-500">Tickets resueltos hoy por NOC</p>
                            <p class="text-2xl font-bold text-blue-700">{{ $resolvedToday }}</p>
                        </div>
                    </div>
                @endif
            @endcan

            <!-- SECCIÓN: Órdenes relacionadas (secretaria/NOC) -->
            @can('view own work_orders')
                @if(isset($relatedWorkOrders) && $relatedWorkOrders && $relatedWorkOrders->count())
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500">work</span>
                            Órdenes de trabajo relacionadas
                        </h2>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">OT #</th>
                                        <th class="px-4 py-3 text-left">Ticket</th>
                                        <th class="px-4 py-3 text-left">Estado</th>
                                        <th class="px-4 py-3 text-left">Técnico</th>
                                        <th class="px-4 py-3 text-left">Fecha creación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($relatedWorkOrders as $wo)
                                        <tr>
                                            <td class="px-4 py-3 font-mono"><a href="{{ route('work-orders.show', $wo->id) }}"
                                                    class="text-blue-600 hover:underline">#{{ $wo->id }}</a></td>
                                            <td class="px-4 py-3">#{{ $wo->ticket_id }}</td>
                                            <td class="px-4 py-3">{{ ucfirst($wo->status) }}</td>
                                            <td class="px-4 py-3">{{ $wo->technician->name ?? 'Sin asignar' }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $wo->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endcan
>>>>>>> Stashed changes
        </div>
    </div>
</div>