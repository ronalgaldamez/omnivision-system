<div class="max-w-5xl mx-auto space-y-6" wire:poll.5s="refreshTimeline">
    @php
        $p = $timeline['parent'];
        $sla = $timeline['sla'];
        $areas = $timeline['areas'];
        $wo = $timeline['workOrder'];
        $ticket = $timeline['ticket'];
    @endphp

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">account_tree</span>
                Timeline del Ticket
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $ticket->ticket_code }} — {{ $ticket->client?->name ?? 'Sin cliente' }}
            </p>
        </div>
        <a href="{{ route('tickets.index') }}"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
            Volver
        </a>
    </div>

    {{--- Info rápida ---}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
        <div>
            <span class="text-xs text-gray-500">Prioridad</span>
            @php $c = ['P1'=>'red','P2'=>'orange','P3'=>'yellow','P4'=>'green']; @endphp
            <p><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c[$ticket->priority] ?? 'gray' }}-100 text-{{ $c[$ticket->priority] ?? 'gray' }}-700">{{ $ticket->priority }}</span></p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Estado</span>
            <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Servicio</span>
            <p class="font-medium">{{ $ticket->service_type ? ucfirst($ticket->service_type) : '—' }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Creado</span>
            <p class="font-mono text-xs">{{ $ticket->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500">Resuelto</span>
            <p class="font-mono text-xs">{{ $ticket->resolved_at?->format('d/m/Y H:i:s') ?? '—' }}</p>
        </div>
    </div>

    {{--- TARJETA PADRE: Tiempo Global L1 con SLA ---}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl">timer</span>
                    <div>
                        <p class="text-sm font-medium text-blue-100">Tiempo Global (L1)</p>
                        <p class="text-2xl font-bold font-mono tracking-wider">
                            @if($p['isCompleted'])
                                {{ $p['durationFormatted'] }}
                            @else
                                <span wire:poll.1s="refreshTimeline">
                                    @php
                                        $e = $ticket->created_at->diffInSeconds(now());
                                        $h = intdiv($e, 3600);
                                        $m = intdiv($e % 3600, 60);
                                        $s = $e % 60;
                                    @endphp
                                    {{ str_pad($h,2,'0',STR_PAD_LEFT) }}:{{ str_pad($m,2,'0',STR_PAD_LEFT) }}:{{ str_pad($s,2,'0',STR_PAD_LEFT) }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
                @if($p['isCompleted'])
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-500/20 text-green-100 rounded-full text-sm font-medium">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Finalizado
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-white/10 text-white rounded-full text-sm font-medium animate-pulse">
                        <span class="material-symbols-outlined text-sm">hourglass_top</span>
                        En curso
                    </span>
                @endif
            </div>
        </div>

        {{--- Barra SLA ---}}
        @if($sla)
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm {{ $sla['met'] === true ? 'text-green-500' : ($sla['met'] === false ? 'text-red-500' : ($sla['isOver'] ? 'text-red-500' : 'text-yellow-500')) }}">
                            {{ $sla['met'] === true ? 'check_circle' : ($sla['met'] === false ? 'cancel' : ($sla['isOver'] ? 'error' : 'hourglass_empty')) }}
                        </span>
                        <span class="text-sm font-medium text-gray-700">SLA {{ $sla['goal']->minutes }} min ({{ $sla['goal']->priority }})</span>
                    </div>
                    <span class="text-xs text-gray-500">Límite: {{ $sla['deadline']->format('d/m/Y H:i:s') }}</span>
                </div>

                @if($sla['isActive'])
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-1000 ease-linear
                            {{ $sla['isOver'] ? 'bg-red-500' : ($sla['progressPercent'] > 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                            style="width: {{ min(100, $sla['progressPercent']) }}%">
                        </div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs {{ $sla['isOver'] ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                            {{ $sla['isOver'] ? 'Vencido por ' . $sla['remainingFormatted'] : $sla['remainingFormatted'] . ' restantes' }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $sla['progressPercent'] }}%</span>
                    </div>
                @elseif($sla['met'] === true)
                    <div class="flex items-center gap-2 text-green-700 bg-green-50 px-3 py-2 rounded-lg text-sm">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        SLA cumplido
                    </div>
                @elseif($sla['met'] === false)
                    <div class="flex items-center gap-2 text-red-700 bg-red-50 px-3 py-2 rounded-lg text-sm">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        SLA incumplido
                    </div>
                @endif
            </div>
        @endif

        {{--- Línea de hitos del padre ---}}
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-center">
                    <div class="w-5 h-5 rounded-full bg-blue-500 mx-auto mb-1 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-xs">flag</span>
                    </div>
                    <p class="text-[10px] text-gray-500">Inicio</p>
                    <p class="text-[10px] font-mono text-gray-600">{{ $ticket->created_at->format('H:i:s') }}</p>
                </div>
                <div class="flex-1 mx-2">
                    <div class="h-0.5 {{ $p['isCompleted'] ? 'bg-blue-400' : 'bg-blue-200' }}"></div>
                </div>
                <div class="text-center">
                    <div class="w-5 h-5 rounded-full {{ $p['isCompleted'] ? 'bg-green-500' : 'bg-gray-300' }} mx-auto mb-1 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-xs">check_small</span>
                    </div>
                    <p class="text-[10px] text-gray-500">Fin</p>
                    <p class="text-[10px] font-mono text-gray-600">{{ $p['end']?->format('H:i:s') ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{--- ÁREAS (SAC, NOC, Supervisor, Técnico) ---}}
    <div class="space-y-4">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2 px-1">
            <span class="material-symbols-outlined text-gray-400 text-base">account_tree</span>
            Desglose por Áreas
        </h2>

        @forelse($areas as $area)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden transition hover:shadow-md">
                {{-- Cabecera del área --}}
                <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100
                    {{ $area['isActive'] ? 'bg-gradient-to-r from-blue-50 to-white' : ($area['isCompleted'] ? 'bg-gradient-to-r from-green-50 to-white' : 'bg-gray-50') }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-sm
                            {{ $area['isCompleted'] ? 'bg-green-500 text-white' : ($area['isActive'] ? 'bg-blue-500 text-white animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                            <span class="material-symbols-outlined">{{ $area['icon'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold {{ $area['isActive'] ? 'text-blue-800' : 'text-gray-800' }}">
                                {{ $area['label'] }}
                            </h3>
                            @if($area['createdByName'] ?? false)
                                <p class="text-xs text-gray-400">Creado por: {{ $area['createdByName'] }}</p>
                            @endif
                            @if($area['responsible'])
                                <p class="text-xs text-gray-500">{{ $area['responsibleLabel'] ?? 'Responsable:' }} {{ $area['responsible'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-mono font-bold {{ $area['isActive'] ? 'text-blue-600' : 'text-gray-700' }}">
                            {{ $area['totalFormatted'] }}
                        </p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $area['isCompleted'] ? 'bg-green-100 text-green-700' : ($area['isActive'] ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                            <span class="material-symbols-outlined text-xs">{{ $area['isCompleted'] ? 'check_circle' : ($area['isActive'] ? 'schedule' : 'pending') }}</span>
                            {{ $area['isCompleted'] ? 'Completado' : ($area['isActive'] ? 'En curso' : 'Pendiente') }}
                        </span>
                    </div>
                </div>

                {{-- Sub-segmentos del área --}}
                <div class="px-5 py-3 space-y-2">
                    @foreach($area['subSegments'] as $sub)
                        <div class="flex items-center justify-between py-1.5 px-3 rounded-lg
                            {{ $sub['isActive'] ? 'bg-blue-50/50' : ($sub['isCompleted'] ? 'bg-gray-50' : 'bg-gray-50/50') }}">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full
                                    {{ $sub['isCompleted'] ? 'bg-green-400' : ($sub['isActive'] ? 'bg-blue-400 animate-pulse' : 'bg-gray-300') }}">
                                </span>
                                <span class="text-sm {{ $sub['isActive'] ? 'text-blue-700 font-medium' : 'text-gray-600' }}">
                                    {{ $sub['label'] }}
                                </span>
                                @if($sub['start'] && $sub['end'])
                                    <span class="text-[10px] text-gray-400 font-mono">
                                        {{ $sub['start']->format('H:i:s') }} → {{ $sub['end']->format('H:i:s') }}
                                    </span>
                                @elseif($sub['start'])
                                    <span class="text-[10px] text-gray-400 font-mono">
                                        desde {{ $sub['start']->format('H:i:s') }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-sm font-mono font-medium {{ $sub['isActive'] ? 'text-blue-600' : 'text-gray-500' }}">
                                @if($sub['durationSeconds'] > 0)
                                    {{ $sub['durationFormatted'] }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-8 text-center text-gray-400">
                <span class="material-symbols-outlined text-4xl mb-2">account_tree</span>
                <p class="text-sm">No hay áreas involucradas en este ticket</p>
            </div>
        @endforelse
    </div>

    {{--- Pausas ---}}
    @if($timeline['pausesSeconds'] > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-600">pause_circle</span>
                <span class="text-sm text-yellow-700 font-medium">Tiempo en pausas</span>
            </div>
            <span class="text-sm font-mono font-bold text-yellow-700">{{ $timeline['pausesFormatted'] }}</span>
        </div>
    @endif

    {{--- Resumen de tiempos ---}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-gray-400 text-base">summarize</span>
            Resumen de Tiempos
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-xs text-blue-600 font-medium">Tiempo Global (L1)</p>
                <p class="text-lg font-mono font-bold text-blue-700 mt-1">{{ $p['durationFormatted'] }}</p>
            </div>
            @foreach($areas as $area)
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-500">{{ $area['label'] }}</p>
                    @if($area['createdByName'] ?? false)
                        <p class="text-xs text-gray-400 mt-0.5">Creado por: {{ $area['createdByName'] }}</p>
                    @endif
                    @if($area['responsible'])
                        <p class="text-xs text-gray-400 mt-0.5">{{ $area['responsibleLabel'] ?? 'Responsable:' }} {{ $area['responsible'] }}</p>
                    @endif
                    <p class="text-lg font-mono font-bold text-gray-700 mt-1">{{ $area['totalFormatted'] }}</p>
                </div>
            @endforeach
            @if($timeline['pausesSeconds'] > 0)
                <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-xs text-yellow-600 font-medium">Pausas</p>
                    <p class="text-lg font-mono font-bold text-yellow-700 mt-1">{{ $timeline['pausesFormatted'] }}</p>
                </div>
            @endif
        </div>
    </div>

    {{--- Nota informativa ---}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-700 flex items-start gap-2">
        <span class="material-symbols-outlined text-sm mt-0.5">info</span>
        <div>
            <strong>Tiempo Global (L1)</strong> es el indicador maestro del SLA — mide desde que se crea el ticket hasta que se resuelve.
            Cada área tiene sus propios sub-segmentos de <strong>espera</strong> y <strong>atención</strong> para medir desempeño individual.
            La suma de áreas no necesariamente equivale al tiempo global por posibles overlaps o tiempos muertos.
        </div>
    </div>
</div>
