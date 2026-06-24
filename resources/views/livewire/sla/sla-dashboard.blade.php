<div class="max-w-7xl mx-auto space-y-6">
    {{-- Título --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">monitoring</span>
                Dashboard SLA
            </h1>
            <p class="text-sm text-gray-500 mt-1">Indicadores de cumplimiento de tiempos de respuesta</p>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Cumplimiento General</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['overallPercentage'] }}%</p>
                </div>
                <span class="material-symbols-outlined text-3xl {{ $stats['overallPercentage'] >= 80 ? 'text-green-500' : ($stats['overallPercentage'] >= 50 ? 'text-yellow-500' : 'text-red-500') }}">check_circle</span>
            </div>
            <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                <span>{{ $stats['met'] }} cumplidos</span>
                <span class="text-gray-300">|</span>
                <span>{{ $stats['notMet'] }} incumplidos</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pendientes</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-blue-500">hourglass_empty</span>
            </div>
            <p class="mt-3 text-xs text-gray-500">Tickets sin evaluar</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">En Riesgo</p>
                    <p class="text-3xl font-bold text-orange-500 mt-1">{{ count($atRiskTickets) }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-orange-500">warning</span>
            </div>
            <p class="mt-3 text-xs text-gray-500">Próximos a exceder SLA (30 min)</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Vencidos</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ count($overdueTickets) }}</p>
                </div>
                <span class="material-symbols-outlined text-3xl text-red-500">error</span>
            </div>
            <p class="mt-3 text-xs text-gray-500">SLA ya excedido</p>
        </div>
    </div>

    {{-- Cumplimiento por prioridad --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-400 text-base">priority</span>
                Cumplimiento por Prioridad
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($stats['byPriority'] as $priority => $data)
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-sm">{{ $priority }}</span>
                            <span class="text-sm font-medium {{ $data['percentage'] >= 80 ? 'text-green-600' : ($data['percentage'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $data['percentage'] }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                {{ $data['percentage'] >= 80 ? 'bg-green-500' : ($data['percentage'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                style="width: {{ $data['percentage'] }}%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ $data['met'] }}/{{ $data['total'] }} cumplidos</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tickets en riesgo y vencidos --}}
    @if(count($atRiskTickets) > 0 || count($overdueTickets) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400 text-base">notifications_active</span>
                    Alertas SLA
                </h2>
            </div>
            <div class="p-6">
                @if(count($overdueTickets) > 0)
                    <div class="mb-4">
                        <h3 class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-2">Vencidos ({{ count($overdueTickets) }})</h3>
                        <div class="space-y-2">
                            @foreach($overdueTickets as $ticket)
                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                    <div>
                                        <span class="font-mono text-xs text-gray-500">{{ $ticket->ticket_code }}</span>
                                        <span class="text-sm text-gray-700 ml-2">{{ $ticket->client?->name ?? 'Sin cliente' }}</span>
                                    </div>
                                    <span class="text-xs text-red-600 font-medium">
                                        Vence: {{ $ticket->sla_deadline_at?->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($atRiskTickets) > 0)
                    <div>
                        <h3 class="text-xs font-semibold text-orange-600 uppercase tracking-wider mb-2">En Riesgo ({{ count($atRiskTickets) }})</h3>
                        <div class="space-y-2">
                            @foreach($atRiskTickets as $ticket)
                                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">
                                    <div>
                                        <span class="font-mono text-xs text-gray-500">{{ $ticket->ticket_code }}</span>
                                        <span class="text-sm text-gray-700 ml-2">{{ $ticket->client?->name ?? 'Sin cliente' }}</span>
                                    </div>
                                    <span class="text-xs text-orange-600 font-medium">
                                        Vence: {{ $ticket->sla_deadline_at?->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Listado de tickets con SLA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-400 text-base">list_alt</span>
                Tickets con SLA
            </h2>
            <div class="flex gap-3">
                <select wire:model.live="filterPriority"
                    class="py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    <option value="">Todas las prioridades</option>
                    <option value="P1">P1</option>
                    <option value="P2">P2</option>
                    <option value="P3">P3</option>
                    <option value="P4">P4</option>
                </select>
                <select wire:model.live="filterStatus"
                    class="py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    <option value="">Todos los estados</option>
                    <option value="met">Cumplió SLA</option>
                    <option value="not_met">No cumplió SLA</option>
                    <option value="pending">Pendiente</option>
                </select>
            </div>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Código</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Prioridad</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Límite SLA</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Estado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">SLA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $ticket->ticket_code }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $ticket->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $colors = ['P1' => 'red', 'P2' => 'orange', 'P3' => 'yellow', 'P4' => 'green'];
                                        $bg = $colors[$ticket->priority] ?? 'gray';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $bg }}-100 text-{{ $bg }}-700">
                                        {{ $ticket->priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $ticket->sla_deadline_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusLabels = ['pending' => 'Pendiente', 'open' => 'Abierto', 'in_progress' => 'En Progreso', 'resolved' => 'Resuelto', 'closed' => 'Cerrado', 'cancelled' => 'Cancelado'];
                                    @endphp
                                    <span class="text-xs text-gray-600">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(is_null($ticket->sla_met))
                                        <span class="text-xs text-gray-400">—</span>
                                    @elseif($ticket->sla_met)
                                        <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-100 px-2 py-0.5 rounded-full">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            Cumplió
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-100 px-2 py-0.5 rounded-full">
                                            <span class="material-symbols-outlined text-sm">cancel</span>
                                            Incumplió
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay tickets con SLA</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="mt-5">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
</div>
