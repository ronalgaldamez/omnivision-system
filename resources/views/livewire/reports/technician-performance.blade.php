<div class="max-w-7xl mx-auto">
    <x-ui.card title="Rendimiento de Técnicos" icon="monitoring"
        subtitle="Resumen de actividad y devoluciones por técnico">
        <div class="space-y-6">

            {{-- ========== PANEL LATERAL: Técnico seleccionado ========== --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex flex-col lg:flex-row lg:items-end gap-4 mb-4">
                    <div class="flex-1">
                        @if ($selectedTechnicianId && $selectedTechnician)
                            <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                                <div
                                    class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-green-600 text-xl">engineering</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $selectedTechnician->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Técnico seleccionado</p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button type="button" wire:click="openTechnicianModal"
                                        class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
                                    <button type="button" wire:click="clearTechnician"
                                        class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                        title="Quitar técnico">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="flex gap-2" x-data="{ focused: false }">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="technicianSearch"
                                        @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                        placeholder="Buscar técnico por nombre..."
                                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if (count($technicianResults) > 0)
                                        <ul x-show="focused" x-transition
                                            class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                            @foreach ($technicianResults as $tech)
                                                <li wire:click="selectTechnician({{ $tech->id }})"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                    <div>
                                                        <span
                                                            class="font-medium text-gray-800 group-hover:text-blue-700">{{ $tech->name }}</span>
                                                    </div>
                                                    <span
                                                        class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-base">engineering</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <button type="button" wire:click="openTechnicianModal"
                                    class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                    title="Ver todos los técnicos">
                                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                    <span class="hidden sm:inline">Ver todos</span>
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="w-full lg:w-48">
                        <x-ui.select wire:model.live="selectedMonths" label="Rango de meses" icon="calendar_month">
                            <option value="3">Últimos 3 meses</option>
                            <option value="6">Últimos 6 meses</option>
                            <option value="12">Últimos 12 meses</option>
                        </x-ui.select>
                    </div>
                </div>

                @if ($selectedTechnician && $technicianPerformance)
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-blue-500 text-lg">engineering</span>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Rendimiento de {{ $selectedTechnician->name }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Gráfico Line with Data Labels: OTs asignadas vs completadas --}}
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <x-charts.apex wire:key="chart-assigned-{{ $selectedTechnicianId }}-{{ $selectedMonths }}"
                                type="line" title="OTs asignadas vs completadas" subtitle="Por mes" height="280"
                                :categories="$technicianPerformance['labels']" :series="[
                                    ['name' => 'Asignadas', 'data' => $technicianPerformance['assigned']],
                                    ['name' => 'Completadas', 'data' => $technicianPerformance['completed']],
                                ]" />
                        </div>

                        {{-- Gráfico Line: Requisiciones aprobadas vs devoluciones --}}
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <x-charts.apex wire:key="chart-reqs-{{ $selectedTechnicianId }}-{{ $selectedMonths }}"
                                type="line" title="Requisiciones aprobadas vs devoluciones" subtitle="Por mes"
                                height="280" :categories="$technicianPerformance['labels']" :series="[
                                    [
                                        'name' => 'Requisiciones aprobadas',
                                        'data' => $technicianPerformance['approved_requisitions'],
                                    ],
                                    ['name' => 'Devoluciones', 'data' => $technicianPerformance['returns']],
                                ]" />
                        </div>

                    </div>

                    {{-- Resumen del técnico seleccionado --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-blue-600 font-medium">OTs asignadas</p>
                            <p class="text-xl font-bold text-blue-800">
                                {{ array_sum($technicianPerformance['assigned']) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-green-600 font-medium">OTs completadas</p>
                            <p class="text-xl font-bold text-green-800">
                                {{ array_sum($technicianPerformance['completed']) }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-purple-600 font-medium">Requisiciones aprobadas</p>
                            <p class="text-xl font-bold text-purple-800">
                                {{ array_sum($technicianPerformance['approved_requisitions']) }}</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-orange-600 font-medium">Devoluciones</p>
                            <p class="text-xl font-bold text-orange-800">
                                {{ array_sum($technicianPerformance['returns']) }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ========== TABLA DE TÉCNICOS ========== --}}
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    Técnico
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">list_alt</span>
                                    Solicitudes totales
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-green-600 text-base">check_circle</span>
                                    Aprobadas
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-red-600 text-base">cancel</span>
                                    Rechazadas
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-blue-600 text-base">arrow_upward</span>
                                    Sobrantes
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span
                                        class="material-symbols-outlined text-orange-600 text-base">broken_image</span>
                                    Dañados
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($technicians as $tech)
                            <tr
                                class="hover:bg-gray-50/80 transition {{ $selectedTechnicianId == $tech->id ? 'bg-blue-50/50' : '' }}">
                                <td class="px-4 py-3 text-gray-800 font-medium">{{ $tech->name }}</td>
                                <td class="px-4 py-3 text-center text-gray-700">{{ $tech->total_requests }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                        {{ $tech->approved_requests }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                        {{ $tech->rejected_requests }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                        {{ $tech->surplus_returns ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-orange-50 text-orange-700 rounded-full text-xs font-medium">
                                        {{ $tech->damage_returns ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="openComparisonModal({{ $tech->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-100 transition"
                                            title="Comparar períodos">
                                            <span class="material-symbols-outlined text-sm">compare_arrows</span>
                                            Comparar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span
                                        class="material-symbols-outlined text-gray-300 text-4xl mb-2">monitoring</span>
                                    <p class="text-gray-500">No se encontraron datos de rendimiento</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (session('message'))
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            @endif
            @if (session('error'))
                <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
            @endif

            {{-- ========== COMPARACIÓN GLOBAL ========== --}}
            @if ($completedComparison)
                <div class="mb-4">
                    <x-ui.alert variant="info" icon="trending_up" title="OTs completadas este mes vs mes anterior">
                        <span class="font-bold text-gray-800">{{ $completedComparison['current'] }}</span> completadas
                        este mes
                        ({{ $completedComparison['previous'] }} el mes pasado)
                        @if ($completedComparison['pct'] > 0)
                            · <span class="font-semibold text-green-600">+{{ $completedComparison['pct'] }}%</span>
                        @elseif($completedComparison['pct'] < 0)
                            · <span class="font-semibold text-red-600">{{ $completedComparison['pct'] }}%</span>
                        @else
                            · <span class="font-semibold text-gray-600">sin cambio</span>
                        @endif
                    </x-ui.alert>
                </div>
            @endif

            {{-- ========== MODAL DE COMPARACIÓN ========== --}}
            @if ($showComparisonModal)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                    aria-modal="true">
                    <div
                        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500/75 transition-opacity"
                            wire:click="closeComparisonModal"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                            aria-hidden="true">&#8203;</span>

                        <div
                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-indigo-500">compare_arrows</span>
                                    <h3 class="text-lg font-semibold text-gray-800">Comparar períodos</h3>
                                </div>
                                <button wire:click="closeComparisonModal"
                                    class="text-gray-400 hover:text-gray-600 transition">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="px-6 py-5 space-y-5">
                                {{-- Selector de técnico --}}
                                @if ($compareTechnicianId && $compareTechnician)
                                    <div
                                        class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span
                                                class="material-symbols-outlined text-green-600 text-xl">engineering</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $compareTechnician->name }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Técnico a comparar</p>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button type="button" wire:click="openCompareTechnicianModal"
                                                class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
                                            <button type="button" wire:click="clearCompareTechnician"
                                                class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Quitar técnico">
                                                <span class="material-symbols-outlined text-lg">close</span>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex gap-2" x-data="{ focused: false }">
                                        <div class="relative flex-1">
                                            <input type="text"
                                                wire:model.live.debounce.300ms="compareTechnicianSearch"
                                                @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                                placeholder="Buscar técnico por nombre..."
                                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                            <span
                                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                            @if (count($compareTechnicianResults) > 0)
                                                <ul x-show="focused" x-transition
                                                    class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                                    @foreach ($compareTechnicianResults as $tech)
                                                        <li wire:click="selectCompareTechnician({{ $tech->id }})"
                                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                            <div>
                                                                <span
                                                                    class="font-medium text-gray-800 group-hover:text-blue-700">{{ $tech->name }}</span>
                                                            </div>
                                                            <span
                                                                class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-base">engineering</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="openCompareTechnicianModal"
                                            class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                            title="Ver todos los técnicos">
                                            <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                            <span class="hidden sm:inline">Ver todos</span>
                                        </button>
                                    </div>
                                @endif

                                {{-- Períodos A y B --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-blue-50/50 rounded-xl border border-blue-100 p-4">
                                        <p class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                                            Período A
                                        </p>
                                        <div class="space-y-3">
                                            <x-ui.input type="date" wire:model="compareStartA"
                                                label="Fecha inicio" icon="event" />
                                            <x-ui.input type="date" wire:model="compareEndA" label="Fecha fin"
                                                icon="event" />
                                        </div>
                                    </div>

                                    <div class="bg-green-50/50 rounded-xl border border-green-100 p-4">
                                        <p class="text-xs font-semibold text-green-700 mb-3 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                                            Período B
                                        </p>
                                        <div class="space-y-3">
                                            <x-ui.input type="date" wire:model="compareStartB"
                                                label="Fecha inicio" icon="event" />
                                            <x-ui.input type="date" wire:model="compareEndB" label="Fecha fin"
                                                icon="event" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Botón generar --}}
                                <div class="flex justify-end">
                                    <button wire:click="generateComparison"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                        <span class="material-symbols-outlined text-sm">bar_chart</span>
                                        Generar comparación
                                    </button>
                                </div>

                                {{-- Resultado de la comparación --}}
                                @if ($comparisonResult)
                                    <div class="border-t border-gray-200 pt-5">
                                        <h4 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-indigo-500">insights</span>
                                            Comparación de {{ $compareTechnician?->name ?? 'Técnico' }}
                                        </h4>

                                        {{-- Gráfico comparativo --}}
                                        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
                                            <x-charts.apex
                                                wire:key="chart-compare-{{ $compareTechnicianId }}-{{ md5(json_encode($comparisonResult)) }}"
                                                type="bar" title="Comparación de métricas"
                                                subtitle="Período A vs Período B" height="280" :categories="[
                                                    'OTs asignadas',
                                                    'OTs completadas',
                                                    'Requisiciones aprobadas',
                                                    'Devoluciones',
                                                ]"
                                                :series="[
                                                    [
                                                        'name' => 'Período A',
                                                        'data' => [
                                                            $comparisonResult['periodA']['assigned'],
                                                            $comparisonResult['periodA']['completed'],
                                                            $comparisonResult['periodA']['approved_requisitions'],
                                                            $comparisonResult['periodA']['returns'],
                                                        ],
                                                    ],
                                                    [
                                                        'name' => 'Período B',
                                                        'data' => [
                                                            $comparisonResult['periodB']['assigned'],
                                                            $comparisonResult['periodB']['completed'],
                                                            $comparisonResult['periodB']['approved_requisitions'],
                                                            $comparisonResult['periodB']['returns'],
                                                        ],
                                                    ],
                                                ]" />
                                        </div>

                                        {{-- Tabla comparativa --}}
                                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                                            <table class="min-w-full text-sm">
                                                <thead>
                                                    <tr class="bg-gray-50 border-b border-gray-200">
                                                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                                            Métrica</th>
                                                        <th class="px-4 py-3 text-center text-blue-600 font-medium">
                                                            Período A</th>
                                                        <th class="px-4 py-3 text-center text-green-600 font-medium">
                                                            Período B</th>
                                                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                                            Cambio</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @php
                                                        $metrics = [
                                                            'OTs asignadas' => ['assigned', 'assigned'],
                                                            'OTs completadas' => ['completed', 'completed'],
                                                            'Requisiciones aprobadas' => [
                                                                'approved_requisitions',
                                                                'approved_requisitions',
                                                            ],
                                                            'Devoluciones' => ['returns', 'returns'],
                                                        ];
                                                    @endphp
                                                    @foreach ($metrics as $label => $keys)
                                                        @php
                                                            $valA = $comparisonResult['periodA'][$keys[0]];
                                                            $valB = $comparisonResult['periodB'][$keys[1]];
                                                            $diff = $valB - $valA;
                                                            $pct =
                                                                $valA > 0
                                                                    ? round(($diff / $valA) * 100, 1)
                                                                    : ($valB > 0
                                                                        ? 100
                                                                        : 0);
                                                        @endphp
                                                        <tr class="hover:bg-gray-50/80 transition">
                                                            <td class="px-4 py-3 text-gray-800 font-medium">
                                                                {{ $label }}</td>
                                                            <td class="px-4 py-3 text-center font-mono text-blue-700">
                                                                {{ $valA }}</td>
                                                            <td class="px-4 py-3 text-center font-mono text-green-700">
                                                                {{ $valB }}</td>
                                                            <td class="px-4 py-3 text-center">
                                                                @if ($diff > 0)
                                                                    <span
                                                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                                                        <span
                                                                            class="material-symbols-outlined text-xs">trending_up</span>
                                                                        +{{ $diff }} (+{{ $pct }}%)
                                                                    </span>
                                                                @elseif($diff < 0)
                                                                    <span
                                                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                                                        <span
                                                                            class="material-symbols-outlined text-xs">trending_down</span>
                                                                        {{ $diff }} ({{ $pct }}%)
                                                                    </span>
                                                                @else
                                                                    <span
                                                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-50 text-gray-600 rounded-full text-xs font-medium">
                                                                        <span
                                                                            class="material-symbols-outlined text-xs">remove</span>
                                                                        Sin cambio
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
                        </div>
                    </div>
                </div>
            @endif

            {{-- ========== MODAL "VER TODOS" (panel lateral) ========== --}}
            <div x-data="{ show: @entangle('showTechnicianModal') }" x-show="show" x-cloak x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                style="display: none;">
                <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="relative w-full max-w-lg">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">engineering</span>
                                <h3 class="text-base font-semibold text-gray-900">Seleccionar técnico</h3>
                            </div>
                            <button type="button" wire:click="closeTechnicianModal"
                                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                                <span class="material-symbols-outlined text-xl">close</span>
                            </button>
                        </div>
                        <div class="p-4 border-b border-gray-100">
                            <div class="relative">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                <input type="text" wire:model.live.debounce.300ms="technicianListSearch"
                                    placeholder="Filtrar técnicos..."
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                            </div>
                        </div>
                        <div class="p-2 max-h-96 overflow-y-auto">
                            @forelse($technicianList as $tech)
                                <button type="button" wire:click="selectTechnicianFromList({{ $tech->id }})"
                                    class="w-full text-left px-4 py-2.5 hover:bg-blue-50 rounded-lg transition text-sm flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-lg flex-shrink-0">engineering</span>
                                        <span class="text-gray-800 truncate">{{ $tech->name }}</span>
                                    </div>
                                    @if ($selectedTechnicianId == $tech->id)
                                        <span
                                            class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">check</span> Seleccionado
                                        </span>
                                    @endif
                                </button>
                            @empty
                                <div class="py-8 text-center text-gray-500 text-sm">No se encontraron técnicos</div>
                            @endforelse
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <x-ui.button variant="secondary" wire:click="closeTechnicianModal">Cerrar</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== MODAL "VER TODOS" (modal de comparación) ========== --}}
            <div x-data="{ show: @entangle('showCompareTechnicianModal') }" x-show="show" x-cloak x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4"
                style="display: none;">
                <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="relative w-full max-w-lg">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-600">engineering</span>
                                <h3 class="text-base font-semibold text-gray-900">Seleccionar técnico a comparar</h3>
                            </div>
                            <button type="button" wire:click="closeCompareTechnicianModal"
                                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                                <span class="material-symbols-outlined text-xl">close</span>
                            </button>
                        </div>
                        <div class="p-4 border-b border-gray-100">
                            <div class="relative">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                <input type="text" wire:model.live.debounce.300ms="compareTechnicianListSearch"
                                    placeholder="Filtrar técnicos..."
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                            </div>
                        </div>
                        <div class="p-2 max-h-96 overflow-y-auto">
                            @forelse($compareTechnicianList as $tech)
                                <button type="button"
                                    wire:click="selectCompareTechnicianFromList({{ $tech->id }})"
                                    class="w-full text-left px-4 py-2.5 hover:bg-blue-50 rounded-lg transition text-sm flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-lg flex-shrink-0">engineering</span>
                                        <span class="text-gray-800 truncate">{{ $tech->name }}</span>
                                    </div>
                                    @if ($compareTechnicianId == $tech->id)
                                        <span
                                            class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-xs">check</span> Seleccionado
                                        </span>
                                    @endif
                                </button>
                            @empty
                                <div class="py-8 text-center text-gray-500 text-sm">No se encontraron técnicos</div>
                            @endforelse
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <x-ui.button variant="secondary"
                                wire:click="closeCompareTechnicianModal">Cerrar</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>
</div>

