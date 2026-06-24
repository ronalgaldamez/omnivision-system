<div class="max-w-5xl mx-auto space-y-6">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">account_tree</span>
                Timeline de OT Pura
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                OT #{{ $workOrder->id }} {{ $workOrder->code ? '- ' . $workOrder->code : '' }} - {{ $workOrder->client?->name ?? 'Sin cliente' }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('work-orders.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Volver</a>
        </div>
    </div>

    {{-- Info de la OT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-xs text-gray-500">Estado</span>
                <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Servicio</span>
                <p class="font-medium">{{ $workOrder->service_type ? ucfirst($workOrder->service_type) : '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Técnico</span>
                <p class="font-medium">{{ $workOrder->technician?->name ?? 'Sin asignar' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Tiempo Total</span>
                <p class="font-mono font-medium text-base">
                    @php
                        $total = $timeline['totalSeconds'];
                        $h = intdiv($total, 3600);
                        $m = intdiv($total % 3600, 60);
                        $s = $total % 60;
                    @endphp
                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($s, 2, '0', STR_PAD_LEFT) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Timeline visual --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-gray-400 text-base">timeline</span>
            Flujo de la Orden de Trabajo
        </h2>

        <div class="relative">
            @foreach($timeline['steps'] as $index => $step)
                <div class="flex items-start gap-4 pb-8 relative {{ $loop->last ? 'pb-0' : '' }}">
                    @if(!$loop->last)
                        <div class="absolute left-[15px] top-8 bottom-0 w-0.5 {{ $step['isCompleted'] ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                    @endif

                    <div class="relative z-10 flex-shrink-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm
                            {{ $step['isCompleted'] ? 'bg-green-500 text-white' : ($step['isActive'] ? 'bg-blue-500 text-white animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                            <span class="material-symbols-outlined text-sm">{{ $step['icon'] }}</span>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-medium {{ $step['isCompleted'] ? 'text-gray-800' : ($step['isActive'] ? 'text-blue-700' : 'text-gray-400') }}">
                                    {{ $step['name'] }}
                                </h3>
                                @if($step['technician'])
                                    <p class="text-xs text-gray-500 mt-0.5">Técnico: {{ $step['technician'] }}</p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if($step['timestamp'])
                                    <p class="text-xs text-gray-500">{{ $step['timestamp']->format('d/m/Y H:i:s') }}</p>
                                @endif
                                @if($step['durationFormatted'])
                                    <p class="text-xs font-mono font-medium {{ $step['isActive'] ? 'text-blue-600' : 'text-gray-600' }}">
                                        ⏱ {{ $step['durationFormatted'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Resumen de tiempos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-gray-400 text-base">summarize</span>
            Resumen de Tiempos
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($timeline['steps'] as $step)
                @if($step['duration'] !== null && $step['key'] !== 'pauses')
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-500">{{ $step['name'] }}</p>
                        <p class="text-sm font-mono font-medium mt-1">{{ $step['durationFormatted'] }}</p>
                    </div>
                @endif
            @endforeach
            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-xs text-blue-600 font-medium">Total</p>
                <p class="text-sm font-mono font-bold text-blue-700 mt-1">
                    @php
                        $total = $timeline['totalSeconds'];
                        $h = intdiv($total, 3600);
                        $m = intdiv($total % 3600, 60);
                    @endphp
                    {{ $h }}h {{ $m }}m
                </p>
            </div>
        </div>
    </div>
</div>
