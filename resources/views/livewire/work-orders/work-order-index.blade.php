<div class="max-w-full mx-auto" wire:poll.15s="$refresh">
    <x-ui.card icon="engineering" title="Órdenes de Trabajo" subtitle="Gestioná y asigná técnicos a las OT del día">
        <x-slot:headerActions>
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button wire:click="setViewMode('cards')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition flex items-center gap-1.5
                        {{ $viewMode === 'cards' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-sm">grid_view</span>
                        <span class="hidden sm:inline">Cards</span>
                    </button>
                    <button wire:click="setViewMode('table')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition flex items-center gap-1.5
                        {{ $viewMode === 'table' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-sm">table_rows</span>
                        <span class="hidden sm:inline">Tabla</span>
                    </button>
                </div>
                <x-ui.button variant="primary" icon="add_circle" href="{{ route('work-orders.create') }}">Nueva
                    Orden</x-ui.button>
            </div>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            {{-- KPIs (iconos Material Symbols del color del sistema) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button wire:click="$set('statusFilter', '')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === '' && $serviceTypeFilter === '' ? 'ring-2 ring-blue-200 border-blue-300' : 'hover:border-blue-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">work</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['total'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Activas</p>
                    </div>
                </button>

                <button wire:click="$set('statusFilter', 'pending')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === 'pending' ? 'ring-2 ring-amber-200 border-amber-300' : 'hover:border-amber-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">schedule</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['pending'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Pendientes</p>
                    </div>
                </button>

                <button wire:click="$set('statusFilter', 'in_progress')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === 'in_progress' ? 'ring-2 ring-sky-200 border-sky-300' : 'hover:border-sky-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">sync</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['in_progress'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">En progreso</p>
                    </div>
                </button>

                <button wire:click="$set('statusFilter', 'unassigned')"
                    class="flex items-center gap-3 w-full rounded-xl border border-gray-200 bg-white p-4 text-left hover:shadow-sm transition-all
                    {{ $statusFilter === 'unassigned' ? 'ring-2 ring-purple-200 border-purple-300' : 'hover:border-purple-200' }}">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-xl">person_off</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-bold text-gray-900 leading-none">{{ $kpis['unassigned'] }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-1">Sin asignar</p>
                    </div>
                </button>
            </div>
            @if($kpis['completed'] > 0)
                <div class="flex justify-end">
                    <button wire:click="$set('statusFilter', 'completed')"
                        class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-green-700 transition {{ $statusFilter === 'completed' ? 'text-green-700 font-semibold' : '' }}">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Ver {{ $kpis['completed'] }} completadas
                    </button>
                </div>
            @endif

            {{-- Tabs por tipo de servicio (patrón de tabs del sistema) --}}
            <div class="border-b border-gray-200">
                <nav class="flex gap-1 px-6 overflow-x-auto">
                    <button wire:click="$set('serviceTypeFilter', '')"
                        class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px whitespace-nowrap
                        {{ $serviceTypeFilter === '' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Todas
                    </button>
                    @foreach($serviceTypes as $st)
                        <button wire:click="$set('serviceTypeFilter', '{{ $st }}')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px whitespace-nowrap
                            {{ $serviceTypeFilter === $st ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            {{ str_replace('_', ' ', ucfirst($st)) }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <x-ui.input type="text" wire:model.live="search" icon="search" label="Buscar" placeholder="OT, cliente o técnico..." class="flex-1 min-w-[200px]" />
                <x-ui.select wire:model.live="dayFilter" label="Día" icon="calendar_month" class="w-40">
                    <option value="">Todos los días</option>
                    <option value="today">Hoy</option>
                    <option value="tomorrow">Mañana</option>
                    <option value="week">Esta semana</option>
                </x-ui.select>
                <x-ui.select wire:model.live="statusFilter" label="Estado" icon="flag" class="w-44">
                    <option value="">Activas</option>
                    <option value="unassigned">Sin asignar</option>
                    <option value="pending">Pendiente</option>
                    <option value="in_progress">En progreso</option>
                    <option value="paused">Pausada</option>
                    <option value="completed">Completadas</option>
                    <option value="cancelled">Canceladas</option>
                </x-ui.select>
                <x-ui.select wire:model.live="technicianFilter" label="Técnico" icon="engineering" class="w-48">
                    <option value="">Todos los técnicos</option>
                    @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="priorityFilter" label="Prioridad" icon="flag" class="w-32">
                    <option value="">Todas</option>
                    <option value="P1">P1 · Crítica</option>
                    <option value="P2">P2 · Alta</option>
                    <option value="P3">P3 · Media</option>
                    <option value="P4">P4 · Baja</option>
                </x-ui.select>
                @if($statusFilter || $search || $serviceTypeFilter || $dayFilter || $technicianFilter || $priorityFilter)
                    <button wire:click="$set('statusFilter', ''); $set('search', ''); $set('serviceTypeFilter', ''); $set('dayFilter', ''); $set('technicianFilter', ''); $set('priorityFilter', '')"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                        Limpiar filtros
                    </button>
                @endif
            </div>

            @if($viewMode === 'table')
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Código OT</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Ticket</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Tipo</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Zona</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Técnico</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Auxiliar</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Vehículo</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Fecha</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/80 transition {{ in_array($order->id, $selectedOrders) ? 'bg-blue-50/60' : '' }}">
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:model.live="selectedOrders" value="{{ $order->id }}"
                                    {{ in_array($order->status, ['completed', 'cancelled']) ? 'disabled' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-blue-700 font-medium">{{ $order->code ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $order->ticket?->ticket_code ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $tVariant = match ($order->service_type) {
                                        'instalacion' => 'info',
                                        'soporte_tecnico' => 'danger',
                                        'traslado' => 'warning',
                                        'reconexion' => 'success',
                                        'verificacion_instalacion', 'verificacion_tecnica' => 'neutral',
                                        default => 'neutral',
                                    };
                                @endphp
                                @if($order->service_type)
                                    <x-ui.badge variant="{{ $tVariant }}">{{ str_replace('_', ' ', ucfirst($order->service_type)) }}</x-ui.badge>
                                @else
                                    <span class="text-xs text-gray-400 italic">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-800">{{ $order->client->name ?? 'No especificado' }}</td>
                            <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $order->zone->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($order->technician)
                                <span class="text-xs font-medium text-gray-700">{{ $order->technician->name }}</span>
                                @else
                                <span class="text-xs text-gray-400 italic">No asignado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($order->auxiliarTechnician)
                                <span class="text-xs text-gray-600">{{ $order->auxiliarTechnician->name }}</span>
                                @else
                                <span class="text-xs text-gray-400 italic">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($order->vehicle)
                                <span class="inline-flex items-center gap-1 text-xs font-mono text-gray-700">
                                    <span class="material-symbols-outlined text-sm text-gray-400">directions_car</span>
                                    {{ $order->vehicle->placa }}
                                </span>
                                @else
                                <span class="text-xs text-gray-400 italic">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php $b = match($order->status) { 'pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'paused' => 'neutral', default => 'danger' }; $sl = ucfirst(str_replace('_', ' ', $order->status)); @endphp
                                <x-ui.badge variant="{{ $b }}">{{ $sl }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-700">{{ $order->scheduled_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($order->status === 'pending' && !$order->accepted_at)
                                        <button wire:click="promptAcceptOrder({{ $order->id }})" class="p-1.5 text-cyan-600 hover:bg-cyan-50 rounded-lg transition" title="Aceptar OT (iniciar asignación)">
                                            <span class="material-symbols-outlined text-lg">play_arrow</span>
                                        </button>
                                    @elseif($order->accepted_at)
                                        <span class="p-1.5 text-cyan-500" title="Aceptada {{ $order->accepted_at->format('H:i') }}">
                                            <span class="material-symbols-outlined text-lg">check_circle</span>
                                        </span>
                                    @endif
                                    @if($order->technician_id)
                                        <button wire:click="promptUnassign({{ $order->id }})" class="p-1.5 text-orange-600 hover:bg-orange-50 rounded-lg transition" title="Desvincular técnico">
                                            <span class="material-symbols-outlined text-lg">person_off</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('work-orders.show', $order->id) }}" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    <a href="{{ route('work-orders.edit', $order->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="promptDelete({{ $order->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                    <a href="{{ $order->ticket_id ? route('sla.ticket-timeline', $order->ticket_id) : route('sla.work-order-timeline', $order->id) }}"
                                        class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Ver Timeline SLA">
                                        <span class="material-symbols-outlined text-lg">account_tree</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">inbox</span>
                                <p class="text-gray-500">No hay órdenes de trabajo registradas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @forelse($orders as $order)
                @php $prio = $order->ticket?->priority; @endphp
                <div class="rounded-xl border overflow-hidden hover:shadow-md transition-all
                    {{ $order->status === 'completed' ? 'bg-gray-50/60 opacity-70 border-gray-200' : ($prio === 'P1' ? 'border-red-400 ring-2 ring-red-100 bg-red-50/30 hover:border-red-500' : ($prio === 'P2' ? 'border-orange-300 bg-orange-50/20 hover:border-orange-400' : 'border-gray-200 bg-white hover:border-blue-300')) }}">
                    <a href="{{ route('work-orders.show', $order->id) }}" class="block p-4 group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="font-mono font-bold text-xs {{ $order->status === 'completed' ? 'text-gray-500' : 'text-blue-700' }}">{{ $order->code ?? '—' }}</span>
                                @php
                                    $prioBadge = match ($prio) {
                                        'P1' => 'bg-red-600 text-white',
                                        'P2' => 'bg-orange-500 text-white',
                                        'P3' => 'bg-blue-100 text-blue-700',
                                        'P4' => 'bg-gray-100 text-gray-600',
                                        default => 'bg-gray-100 text-gray-500',
                                    };
                                @endphp
                                @if($prio)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $prioBadge }}">{{ $prio }}</span>
                                @endif
                            </div>
                            @php $b = match($order->status) { 'pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'paused' => 'neutral', default => 'danger' }; $sl = ucfirst(str_replace('_', ' ', $order->status)); @endphp
                            <x-ui.badge variant="{{ $b }}">{{ $sl }}</x-ui.badge>
                        </div>
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $order->client->name ?? 'No especificado' }}</p>
                        <div class="flex items-center gap-1 text-xs text-gray-500 mt-1.5">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            {{ $order->zone->name ?? '—' }}
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                            <span class="material-symbols-outlined text-sm">engineering</span>
                            @if($order->technician)
                                <span class="truncate">{{ $order->technician->name }}</span>
                                @if($order->vehicle)
                                    <span class="inline-flex items-center gap-0.5 text-gray-400 ml-1">
                                        <span class="material-symbols-outlined text-sm">directions_car</span>
                                        {{ $order->vehicle->placa }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400 italic">Sin asignar</span>
                            @endif
                        </div>
                        @if($order->auxiliarTechnician)
                        <div class="flex items-center gap-1 text-xs text-gray-400 mt-1">
                            <span class="material-symbols-outlined text-sm">handyman</span>
                            <span class="truncate">{{ $order->auxiliarTechnician->name }}</span>
                            <span class="text-[10px]">(auxiliar)</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                            @php
                                $tBadge = match ($order->service_type) {
                                    'instalacion' => 'bg-blue-50 text-blue-700',
                                    'soporte_tecnico' => 'bg-red-50 text-red-700',
                                    'traslado' => 'bg-amber-50 text-amber-700',
                                    'reconexion' => 'bg-green-50 text-green-700',
                                    'verificacion_instalacion', 'verificacion_tecnica' => 'bg-purple-50 text-purple-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            @if($order->service_type)
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $tBadge }}">{{ str_replace('_', ' ', ucfirst($order->service_type)) }}</span>
                            @else
                                <span></span>
                            @endif
                            <span class="inline-flex items-center gap-0.5 text-[10px] text-gray-400 group-hover:text-blue-600 transition">Ver
                                <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </span>
                        </div>
                    </a>
                    @if(!in_array($order->status, ['completed', 'cancelled']))
                    <div class="px-4 py-2.5 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            @if($order->status === 'pending' && !$order->accepted_at)
                                <button wire:click="promptAcceptOrder({{ $order->id }})" class="p-1.5 text-cyan-600 hover:bg-cyan-100 rounded-lg transition" title="Aceptar OT">
                                    <span class="material-symbols-outlined text-sm">play_arrow</span>
                                </button>
                            @elseif($order->accepted_at)
                                <span class="p-1.5 text-cyan-500" title="Aceptada {{ $order->accepted_at->format('H:i') }}">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                </span>
                            @endif
                            @if($order->accepted_at)
                            <button wire:click="assignOrder({{ $order->id }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                                <span class="material-symbols-outlined text-sm">person_add</span>
                                Asignar
                            </button>
                            @endif
                            @if($order->technician_id)
                            <button wire:click="promptUnassign({{ $order->id }})" class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition" title="Desvincular técnico">
                                <span class="material-symbols-outlined text-sm">person_off</span>
                            </button>
                            @endif
                            <a href="{{ route('work-orders.edit', $order->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Editar OT">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </a>
                            <button wire:click="promptDelete({{ $order->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition" title="Eliminar OT">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                            <a href="{{ $order->ticket_id ? route('sla.ticket-timeline', $order->ticket_id) : route('sla.work-order-timeline', $order->id) }}" class="p-1.5 text-purple-600 hover:bg-purple-100 rounded-lg transition" title="Timeline SLA">
                                <span class="material-symbols-outlined text-sm">account_tree</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-200 py-12 text-center bg-gray-50/50">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">inbox</span>
                    <p class="text-gray-500">No hay órdenes de trabajo registradas</p>
                </div>
                @endforelse
            </div>
            @endif

            @if(method_exists($orders, 'hasPages') && $orders->hasPages())
            <div class="mt-5">{{ $orders->links() }}</div>
            @endif

            @if(count($selectedOrders) > 0)
            <div class="fixed bottom-6 right-6 z-50 flex items-center gap-3 animate-slide-up">
                <span class="text-sm text-gray-500 bg-white px-3 py-1.5 rounded-lg shadow-md border border-gray-200">
                    {{ count($selectedOrders) }} seleccionada(s)
                </span>
                <button wire:click="$set('showAssignModal', true)"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-lg hover:bg-blue-700 transition">
                    Asignar
                </button>
                <button wire:click="$set('selectedOrders', [])"
                    class="text-gray-400 hover:text-gray-600 bg-white rounded-full p-1.5 shadow-md border border-gray-200 transition">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
            <style>
                @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
                .animate-slide-up { animation: slideUp 0.2s ease-out; }
            </style>
            @endif
        </div>
    </x-ui.card>

    {{-- Modal de asignación masiva --}}
    @if($showAssignModal)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-2xl">
            <x-ui.card>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Asignación masiva</h3>
                            <p class="text-sm text-gray-500 mt-0.5"><span class="font-semibold text-blue-600">{{ count($selectedOrders) }}</span> OT(s) seleccionadas</p>
                        </div>
                        <button wire:click="closeAssignModal" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 max-h-32 overflow-y-auto">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-2">OTs seleccionadas</p>
                        <div class="flex flex-wrap gap-1.5">
                            @php $selectedCodes = \App\Models\WorkOrder::whereIn('id', $selectedOrders)->pluck('code')->toArray(); @endphp
                            @foreach($selectedCodes as $code)
                                <span class="px-2 py-0.5 rounded bg-blue-50 border border-blue-200 text-blue-700 font-mono text-[10px]">{{ $code }}</span>
                            @endforeach
                        </div>
                    </div>

                    @if($alreadyAssigned > 0)
                    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <span class="material-symbols-outlined text-amber-500 text-base mt-0.5">warning</span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800">{{ $alreadyAssigned }} de {{ count($selectedOrders) }} OT ya tienen técnico</p>
                            <label class="inline-flex items-center gap-2 mt-2 text-xs text-amber-700 cursor-pointer">
                                <input type="checkbox" wire:model="skipAssigned" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                Saltar OT que ya tienen técnico (solo {{ count($selectedOrders) - $alreadyAssigned }} sin técnico)
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Técnico</label>
                            <select wire:model="assignTechnicianId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach($encargados as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Auxiliar</label>
                            <select wire:model="assignAuxiliarId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Vehículo</label>
                            <select wire:model="assignVehicleId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">Sin vehículo</option>
                                @foreach($vehiculos as $v)
                                <option value="{{ $v->id }}">{{ $v->placa }} · {{ $v->marca }} {{ $v->modelo }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">Se sugiere el vehículo del encargado al elegir técnico. Podés cambiarlo.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha programada</label>
                        <input type="date" wire:model="scheduledDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas <span class="text-gray-400">(opcional)</span></label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Notas para todas las OT seleccionadas..."></textarea>
                    </div>
                </div>
                <x-slot:footer>
                    <x-ui.button variant="secondary" wire:click="closeAssignModal">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="assignSelected">Asignar a {{ count($selectedOrders) }} OT</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
    @endif

    @if($confirmingAction)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <x-ui.card>
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full {{ $confirmingAction === 'accept' ? 'bg-cyan-100' : ($confirmingAction === 'unassign' ? 'bg-orange-100' : 'bg-blue-100') }} mb-4">
                        <span class="material-symbols-outlined text-2xl {{ $confirmingAction === 'accept' ? 'text-cyan-600' : ($confirmingAction === 'unassign' ? 'text-orange-600' : 'text-blue-600') }}">{{ $confirmingAction === 'accept' ? 'play_arrow' : ($confirmingAction === 'unassign' ? 'person_off' : 'help') }}</span>
                    </div>
                    @if($confirmingAction === 'accept')
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar aceptación</h3>
                        <p class="text-sm text-gray-600 mt-2">¿Aceptar la OT #{{ $confirmingOrderId }}? Una vez aceptada podrás asignarla a un técnico.</p>
                    @elseif($confirmingAction === 'unassign')
                        <h3 class="text-lg font-semibold text-gray-900">Desvincular técnico</h3>
                        <p class="text-sm text-gray-600 mt-2">¿Desvincular el técnico de la OT #{{ $confirmingOrderId }}? Se quitarán técnico, auxiliar y vehículo. La OT quedará libre para reasignar.</p>
                    @else
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                        <p class="text-sm text-gray-600 mt-2">¿Estás seguro de que deseas eliminar la orden #{{ $confirmingOrderId }}?</p>
                    @endif
                </div>
                <x-slot:footer>
                    @if($confirmingAction === 'accept')
                        <x-ui.button variant="primary" icon="play_arrow" wire:click="executeConfirmedAction">Sí, aceptar</x-ui.button>
                    @elseif($confirmingAction === 'unassign')
                        <x-ui.button variant="warning" icon="person_off" wire:click="executeConfirmedAction">Sí, desvincular</x-ui.button>
                    @else
                        <x-ui.button variant="danger" wire:click="executeConfirmedAction">Sí, eliminar</x-ui.button>
                    @endif
                    <x-ui.button variant="secondary" @click="open = false" wire:click="cancelConfirmation">Cancelar</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
