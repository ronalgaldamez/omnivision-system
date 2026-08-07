<div class="max-w-7xl mx-auto space-y-6">
    @php
        $fk = "{$fechaInicio}|{$fechaFin}|{$departamento}|{$branchId}|{$planId}";
    @endphp

    {{-- ========== FILTROS ========== --}}
    <x-ui.card title="Rendimiento Global" icon="leaderboard"
        subtitle="Panel comercial, instalaciones y soporte">
        <div class="space-y-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="space-y-1.5">
                    <x-forms.label>Preset</x-forms.label>
                    <div class="flex gap-1.5 flex-wrap">
                        @foreach ([
                            'month' => 'Mes',
                            '7d' => '7 días',
                            '30d' => '30 días',
                            '90d' => '90 días',
                            '12m' => '12 meses',
                        ] as $val => $label)
                            <x-ui.button size="sm"
                                variant="{{ $preset === $val ? 'primary' : 'secondary' }}"
                                wire:click="applyPreset('{{ $val }}')">{{ $label }}</x-ui.button>
                        @endforeach
                    </div>
                </div>

                <div class="w-40">
                    <x-ui.input type="date" wire:model.live.debounce.500ms="fechaInicio" label="Desde" icon="event" />
                </div>
                <div class="w-40">
                    <x-ui.input type="date" wire:model.live.debounce.500ms="fechaFin" label="Hasta" icon="event" />
                </div>

                <div class="w-56">
                    <x-ui.select wire:model.live="departamento" label="Departamento" icon="map">
                        <option value="">Todos</option>
                        @foreach ($departamentos as $depto)
                            <option value="{{ $depto }}">{{ $depto }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div class="w-52">
                    <x-ui.select wire:model.live="branchId" label="Sucursal" icon="storefront">
                        <option value="">Todas</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div class="w-52">
                    <x-ui.select wire:model.live="planId" label="Plan" icon="signal_wifi_4_bar">
                        <option value="">Todos</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.button variant="ghost" size="sm" icon="filter_alt_off"
                    wire:click="clearFilters">Limpiar</x-ui.button>
            </div>

            <div wire:loading.flex class="items-center gap-2 text-xs text-gray-500" wire:target="applyPreset,fechaInicio,fechaFin,departamento,branchId,planId">
                <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                Calculando métricas…
            </div>
        </div>
    </x-ui.card>

    {{-- ========== HERO KPIs ========== --}}
    @php
        $kpiIcons = [
            'revenue' => 'payments',
            'contracts' => 'description',
            'avgTicket' => 'receipt_long',
            'installations' => 'home_work',
            'successRate' => 'check_circle',
            'resolved' => 'support_agent',
            'avgResolutionHours' => 'schedule',
            'slaCompliance' => 'verified',
        ];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($kpis as $key => $k)
            @php
                $val = $k['value'];
                $fmt = match ($k['suffix']) {
                    '$' => '$' . number_format((float) $val, 2),
                    '%' => number_format((float) $val, 1) . '%',
                    'h' => number_format((float) $val, 1) . ' h',
                    default => number_format((float) $val, 0),
                };
                $prevFmt = match ($k['suffix']) {
                    '$' => '$' . number_format((float) $k['previous'], 2),
                    '%' => number_format((float) $k['previous'], 1) . '%',
                    'h' => number_format((float) $k['previous'], 1) . ' h',
                    default => number_format((float) $k['previous'], 0),
                };
                $up = $k['pct'] > 0;
                $good = $key === 'avgResolutionHours' ? !$up : $up;
            @endphp
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 truncate">{{ $k['label'] }}</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-800 mt-1 truncate">{{ $fmt }}</p>
                    </div>
                    <span class="material-symbols-outlined text-gray-200 text-2xl flex-shrink-0">{{ $kpiIcons[$key] }}</span>
                </div>
                @if ($k['pct'] != 0)
                    <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium {{ $good ? 'text-green-600' : 'text-red-600' }}">
                        <span class="material-symbols-outlined text-xs">{{ $up ? 'trending_up' : 'trending_down' }}</span>
                        {{ $up ? '+' : '' }}{{ $k['pct'] }}%
                        <span class="text-gray-400 font-normal">vs anterior</span>
                    </span>
                @elseif ((float) $k['previous'] > 0)
                    <span class="mt-2 inline-block text-xs text-gray-400">vs anterior: {{ $prevFmt }}</span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ========== VENTAS ========== --}}
    <div class="space-y-6">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-500 text-lg">trending_up</span>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Ventas y contratos</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @if (array_sum($salesMonthly['revenue']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                    <x-charts.apex wire:key="sales-rev-{{ $fk }}" type="area" title="Ingresos por ventas"
                        subtitle="Valor de contratos en el período" height="280"
                        :categories="$salesMonthly['labels']"
                        :series="[['name' => 'Ingresos ($)', 'data' => $salesMonthly['revenue']]]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin datos de ventas en el período</p>
                </div>
            @endif

            @if (array_sum($salesByStatus['series']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="sales-status-{{ $fk }}" type="donut" title="Contratos por estado"
                        height="280" :labels="$salesByStatus['labels']" :series="$salesByStatus['series']" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin contratos en el período</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500 text-base">workspace_premium</span>
                    Top vendedores
                </h3>
                @forelse ($salesByAgent['agents'] as $i => $agent)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm text-gray-800 truncate">{{ $agent['name'] }}</span>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-gray-800">${{ number_format($agent['revenue'], 2) }}</p>
                            <p class="text-xs text-gray-400">{{ $agent['count'] }} contratos</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">Sin ventas en el período</p>
                @endforelse
            </div>

            @if (!empty($salesByPlan['labels']))
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="sales-plan-{{ $fk }}" type="donut" title="Contratos por plan"
                        subtitle="Distribución" height="280" :labels="$salesByPlan['labels']" :series="$salesByPlan['count']" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin planes en el período</p>
                </div>
            @endif

            @if (!empty($salesByZone['labels']))
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="sales-zone-{{ $fk }}" type="bar" title="Ventas por departamento"
                        subtitle="Ingresos ($)" height="280" :categories="$salesByZone['labels']"
                        :series="[['name' => 'Ingresos ($)', 'data' => $salesByZone['revenue']]]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin datos geográficos</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ========== INSTALACIONES ========== --}}
    <div class="space-y-6">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-500 text-lg">home_work</span>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Instalaciones</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @if (array_sum($installationsMonthly['completed']) > 0 || array_sum($installationsMonthly['assigned']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                    <x-charts.apex wire:key="inst-monthly-{{ $fk }}" type="bar" title="Instalaciones completadas vs asignadas"
                        subtitle="Por período" height="280" :categories="$installationsMonthly['labels']"
                        :series="[
                            ['name' => 'Completadas', 'data' => $installationsMonthly['completed']],
                            ['name' => 'Asignadas', 'data' => $installationsMonthly['assigned']],
                        ]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin instalaciones en el período</p>
                </div>
            @endif

            @if (array_sum($installationsByZone['series']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="inst-zone-{{ $fk }}" type="donut" title="Instalaciones por departamento"
                        height="280" :labels="$installationsByZone['labels']" :series="$installationsByZone['series']" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin datos geográficos</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500 text-base">engineering</span>
                    Top técnicos por instalaciones
                </h3>
                @forelse ($installationsByTechnician['technicians'] as $i => $tech)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm text-gray-800 truncate">{{ $tech['name'] }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium flex-shrink-0">
                            {{ $tech['completed'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">Sin instalaciones en el período</p>
                @endforelse
            </div>

            @if (array_sum($averageInstallationTime['hours']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="inst-time-{{ $fk }}" type="area" title="Tiempo promedio de instalación"
                        subtitle="Horas desde asignación hasta completado" height="280"
                        :categories="$averageInstallationTime['labels']"
                        :series="[['name' => 'Horas', 'data' => $averageInstallationTime['hours']]]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin instalaciones completadas</p>
                </div>
            @endif

            <div class="space-y-4">
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200/80 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-blue-500 text-base">fact_check</span>
                                Tasa de éxito
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $installationSuccessRate }}%</p>
                        </div>
                        <span class="material-symbols-outlined text-blue-100 text-3xl">verified</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Completadas vs asignadas en el período</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-white rounded-xl border border-orange-200/80 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-orange-500 text-base">pending_actions</span>
                                Instalaciones pendientes
                            </p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $installationsPending }}</p>
                        </div>
                        <span class="material-symbols-outlined text-orange-100 text-3xl">schedule</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Cola actual (pendiente + en proceso)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== FALLOS / SOPORTE ========== --}}
    <div class="space-y-6">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-500 text-lg">support_agent</span>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Fallos y soporte</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @if (array_sum($failuresMonthly['total']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2">
                    <x-charts.apex wire:key="fail-monthly-{{ $fk }}" type="area" title="Fallos resueltos por período"
                        subtitle="L1 (SAC) vs NOC" height="280" :categories="$failuresMonthly['labels']"
                        :series="[
                            ['name' => 'L1 (SAC)', 'data' => $failuresMonthly['l1']],
                            ['name' => 'NOC', 'data' => $failuresMonthly['noc']],
                        ]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 lg:col-span-2 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin fallos resueltos en el período</p>
                </div>
            @endif

            @if (array_sum($failuresByPriority['series']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="fail-priority-{{ $fk }}" type="donut" title="Resueltos por prioridad"
                        height="280" :labels="$failuresByPriority['labels']" :series="$failuresByPriority['series']" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin fallos en el período</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <x-charts.apex wire:key="sla-overall-{{ $fk }}" type="radial" title="Cumplimiento SLA"
                    subtitle="Período" height="280" :series="[(float) $slaCompliance['overall']]"
                    :labels="['Cumplimiento']" />
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500 text-base">trophy</span>
                    SLA por prioridad
                </h3>
                <div class="space-y-3">
                    @foreach ($slaCompliance['byPriority']['labels'] as $i => $priority)
                        @php $pct = $slaCompliance['byPriority']['percentages'][$i]; @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-600 font-medium">{{ $priority }}</span>
                                <span class="font-semibold {{ $pct >= 80 ? 'text-green-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $pct }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $pct >= 80 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500 text-base">person_search</span>
                    Top resueltores
                </h3>
                @forelse ($failuresByResolver['resolvers'] as $i => $resolver)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-green-50 text-green-600 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm text-gray-800 truncate">{{ $resolver['name'] }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium flex-shrink-0">
                            {{ $resolver['count'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">Sin resoluciones en el período</p>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @if (array_sum($escalations['series']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="escalations-{{ $fk }}" type="bar" title="Escalamientos"
                        subtitle="L1 resueltos vs NOC" height="280" :categories="$escalations['labels']"
                        :series="[['name' => 'Resueltos', 'data' => $escalations['series']]]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin escalamientos</p>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500 text-base">timer</span>
                    Tiempo promedio de resolución (horas)
                </h3>
                <div class="space-y-3">
                    @foreach ($averageResolutionTime['byPriority']['labels'] as $i => $priority)
                        @php $hours = $averageResolutionTime['byPriority']['hours'][$i]; @endphp
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                            <span class="text-sm text-gray-600 font-medium">{{ $priority }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $hours }} h</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if (!empty($failuresByServiceType['labels']))
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="fail-stype-{{ $fk }}" type="bar" title="Fallos por tipo de servicio"
                        subtitle="Resueltos" height="280" :categories="$failuresByServiceType['labels']"
                        :series="[['name' => 'Resueltos', 'data' => $failuresByServiceType['series']]]" />
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-center min-h-[280px]">
                    <p class="text-gray-400 text-sm">Sin fallos por tipo</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ========== FUNNEL COMERCIAL ========== --}}
    @php
        $funnelSteps = [
            ['label' => 'Clientes nuevos', 'value' => $funnel['clients'], 'icon' => 'person_add', 'color' => 'bg-teal-500'],
            ['label' => 'Contratos', 'value' => $funnel['contracts'], 'icon' => 'description', 'color' => 'bg-blue-500'],
            ['label' => 'Instalaciones', 'value' => $funnel['installations'], 'icon' => 'home_work', 'color' => 'bg-indigo-500'],
            ['label' => 'Ingresos', 'value' => '$' . number_format((float) $funnel['revenue'], 2), 'icon' => 'payments', 'color' => 'bg-green-500'],
        ];
        $funnelMax = max(1, $funnel['clients']);
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-gray-500">funnel</span>
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Funnel comercial</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($funnelSteps as $i => $step)
                <div class="relative">
                    <div class="rounded-xl border border-gray-200 p-4 text-center">
                        <span class="material-symbols-outlined text-gray-400">{{ $step['icon'] }}</span>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $step['value'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $step['label'] }}</p>
                    </div>
                    <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full {{ $step['color'] }} rounded-full"
                            style="width: {{ $i === 3 ? 100 : min(100, ($step['value'] / $funnelMax) * 100) }}%"></div>
                    </div>
                    @if (!$loop->last)
                        <span class="absolute -right-3 top-8 -translate-y-1/2 material-symbols-outlined text-gray-300 hidden lg:block">chevron_right</span>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="flex flex-wrap gap-3 mt-5">
            <x-ui.badge variant="info" icon="swap_horiz">Ticket → Contrato: {{ $conversion['ticketsToContracts'] }}%</x-ui.badge>
            <x-ui.badge variant="info" icon="home_work">Contrato → Instalación: {{ $conversion['contractsToInstallations'] }}%</x-ui.badge>
        </div>
    </div>

    {{-- ========== EFICIENCIA TÉCNICA + OPERATIVO ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-gray-500 text-base">build</span>
                Eficiencia técnica por técnico
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-500">
                            <th class="pb-2 text-left font-medium">Técnico</th>
                            <th class="pb-2 text-center font-medium">Requisiciones aprobadas</th>
                            <th class="pb-2 text-center font-medium">Sobrantes</th>
                            <th class="pb-2 text-center font-medium">Dañados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($technicalEfficiency['technicians'] as $row)
                            <tr>
                                <td class="py-2.5 text-gray-800 font-medium">{{ $row['name'] }}</td>
                                <td class="py-2.5 text-center text-gray-700">{{ $row['approved'] }}</td>
                                <td class="py-2.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">{{ $row['surplus'] }}</span>
                                </td>
                                <td class="py-2.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">{{ $row['damage'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400">Sin actividad técnica en el período</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-200/80 p-4">
                    <p class="text-xs text-gray-500">Valor del inventario</p>
                    <p class="text-xl font-bold text-gray-800 mt-0.5">${{ number_format($inventory['value'], 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-white rounded-xl border border-red-200/80 p-4">
                    <p class="text-xs text-gray-500">Stock bajo</p>
                    <p class="text-xl font-bold text-gray-800 mt-0.5">{{ $inventory['lowStock'] }}</p>
                </div>
            </div>

            @if (array_sum($inventory['movements']['series']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="mov-{{ $fk }}" type="area" title="Movimientos de inventario"
                        subtitle="En el período" height="200" :categories="$inventory['movements']['labels']"
                        :series="[['name' => 'Movimientos', 'data' => $inventory['movements']['series']]]" />
                </div>
            @endif

            @if (array_sum($shipments['series']) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <x-charts.apex wire:key="shipments" type="donut" title="Envíos por estado"
                        height="200" :labels="$shipments['labels']" :series="$shipments['series']" />
                </div>
            @endif
        </div>
    </div>
</div>
