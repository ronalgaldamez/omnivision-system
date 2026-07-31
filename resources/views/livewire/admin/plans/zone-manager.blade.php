<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-sm font-semibold text-gray-700">Estructura de Zonas</h2>
            <x-ui.button variant="primary" icon="add" wire:click="openZoneModal">Nueva Zona Raíz</x-ui.button>
        </div>

        @forelse($branches as $branch)
            @php $branchRoots = $rootZones->where('branch_id', $branch->id); @endphp
            @if($branchRoots->count() > 0)
            <div class="bg-gray-50/50 rounded-xl border border-gray-200 overflow-visible">
                <div class="px-4 py-2.5 bg-gray-100/80 border-b border-gray-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm text-gray-500">business</span>
                    <span class="text-sm font-semibold text-gray-700">{{ $branch->name }}</span>
                    <span class="text-xs text-gray-400">({{ $branchRoots->sum(fn($z) => 1 + $z->children->count()) }} zonas)</span>
                </div>
                <div class="p-2">
                    @foreach($branchRoots as $rootZone)
                        @include('livewire.admin.plans._zone-tree', [
                            'zone' => $rootZone,
                            'depth' => 0,
                            'expandedZones' => $expandedZones,
                            'selectedZoneId' => $selectedZoneId,
                        ])
                    @endforeach
                </div>
            </div>
            @endif
        @empty
            <div class="text-center py-12 text-gray-500">
                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">map</span>
                <p>No hay sucursales activas</p>
            </div>
        @endforelse

        @if($rootZones->count() === 0)
            <div class="text-center py-12 text-gray-500 bg-gray-50/50 rounded-xl border border-gray-200">
                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">layers</span>
                <p class="font-medium">No hay zonas registradas</p>
                <p class="text-xs mt-1">Creá un departamento como zona raíz, luego agregá municipios, distritos, etc.</p>
            </div>
        @endif

        {{-- PANEL DE PRECIOS --}}
        @if($selectedZone)
        <div id="price-panel" class="bg-white rounded-xl border border-blue-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-blue-50 border-b border-blue-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-blue-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">attach_money</span>
                        Precios para: {{ $selectedZone->name }}
                    </h3>
                    <p class="text-xs text-blue-600 mt-0.5">
                        {{ $selectedZone->branch->name }}
                        — Nivel: {{ ucfirst($selectedZone->level) }}
                        @if($selectedZone->parent)
                            — Padre: {{ $selectedZone->parent->name }}
                        @endif
                    </p>
                </div>
                <button wire:click="$set('selectedZoneId', null)" class="text-blue-400 hover:text-blue-600">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>

            @if(count($zonePrices) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-medium">Plan</th>
                            <th class="text-center px-4 py-2.5 font-medium">Tipo</th>
                            <th class="text-right px-4 py-2.5 font-medium">Precio Base</th>
                            <th class="text-left px-4 py-2.5 font-medium">Hereda de</th>
                            <th class="text-right px-4 py-2.5 font-medium">Precio Final</th>
                            <th class="text-center px-4 py-2.5 font-medium">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($zonePrices as $item)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5 font-medium text-gray-800">
                                {{ $item['plan_name'] }}
                                @if($item['plan_speed'])
                                    <span class="text-xs text-gray-400">({{ $item['plan_speed'] }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $svc = $item['plan_service'] ?? '';
                                    $badgeClass = match($svc) {
                                        'internet' => 'info',
                                        'cable' => 'warning',
                                        'internet_cable' => 'success',
                                        default => 'neutral'
                                    };
                                @endphp
                                <x-ui.badge variant="{{ $badgeClass }}">{{ str_replace('_', ' + ', ucfirst($svc)) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-500">${{ number_format($item['base_price'], 2) }}</td>
                            <td class="px-4 py-2.5 text-left text-gray-600">
                                @if($item['inherited_from'])
                                    <span class="text-amber-600 text-xs">{{ $item['inherited_from'] }}</span>
                                @elseif($item['override_price'] !== null)
                                    <span class="text-green-600 text-xs">Precio propio</span>
                                @else
                                    <span class="text-gray-400 text-xs">— (precio base)</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold {{ $item['override_price'] !== null ? 'text-amber-600' : ($item['inherited_from'] ? 'text-blue-600' : 'text-gray-700') }}">
                                ${{ number_format($item['effective_price'], 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.button variant="ghost" wire:click="editPrice({{ $item['plan_id'] }})" class="text-xs px-2 py-1">{{ $item['override_price'] !== null ? 'Editar' : 'Ajustar' }}</x-ui.button>
                                    @if($item['override_price'] !== null)
                                    <x-ui.button variant="ghost" wire:click="removePriceOverride({{ $item['plan_id'] }})" class="text-xs px-2 py-1 text-red-500">Quitar</x-ui.button>
                                    @endif
                                    <button wire:click="loadPriceHistory({{ $item['plan_id'] }})" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-500 hover:bg-gray-200" title="Ver historial de precios">
                                        <span class="material-symbols-outlined text-xs align-text-bottom">history</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <p>No hay planes activos. Creá planes primero.</p>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- MODAL ZONA --}}
    @if($showZoneModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-xl">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 text-base">add_location</span>
                        @if($editingZoneId)
                            Editar: {{ \App\Models\Zone::find($editingZoneId)?->name }}
                        @elseif($zone_parent_id)
                            Nueva sub-zona ({{ $zone_level }})
                        @else
                            Nueva Zona Raíz (Departamento)
                        @endif
                    </h3>
                    <button wire:click="$set('showZoneModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    @if(!$editingZoneId && !$zone_parent_id)
                        <x-ui.select wire:model="zone_branch_id" label="Sucursal" icon="business" placeholder="Seleccionar sucursal..." required>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif

                    <x-ui.input type="text" wire:model="zone_name" label="Nombre" icon="badge" placeholder="Nombre de la zona" required />

                    @if(!$editingZoneId && !$zone_parent_id)
                        <x-ui.input type="text" wire:model="zone_municipio_name" label="Municipio (opcional)" icon="arrow_downward" placeholder="También podés crear el municipio ahora" />
                    @endif

                    @if($editingZoneId)
                    <x-ui.select wire:model="zone_level" label="Nivel" icon="layers">
                        <option value="departamento">Departamento</option>
                        <option value="municipio">Municipio</option>
                        <option value="distrito">Distrito</option>
                        <option value="cantón">Cantón</option>
                        <option value="caserío">Caserío</option>
                    </x-ui.select>

                    @if(!in_array($zone_level, ['departamento', 'municipio']))
                    <div class="flex items-center gap-4 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="zone_has_internet" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700"><span class="material-symbols-outlined text-base align-text-bottom">language</span> Internet</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="zone_has_cable" class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                            <span class="text-sm text-gray-700"><span class="material-symbols-outlined text-base align-text-bottom">tv</span> Cable</span>
                        </label>
                    </div>
                    @endif
                    @endif

                    @if($editingZoneId || $zone_parent_id)
                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Planes y Precios</label>
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="text" wire:model.live="group_search" placeholder="Buscar grupo..."
                                        class="w-40 pl-8 pr-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">search</span>
                                    @if(strlen($group_search) >= 1)
                                    <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-32 overflow-y-auto">
                                        @php $groups = $this->searchedGroups; @endphp
                                        @forelse($groups as $g)
                                        <button wire:click="addGroupToZone({{ $g->id }})" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-xs flex items-center gap-2">
                                            <span class="material-symbols-outlined text-xs text-green-500">add</span>
                                            <span class="font-medium text-gray-700">{{ $g->name }}</span>
                                            <span class="text-gray-400">({{ $g->plans_count }} planes)</span>
                                        </button>
                                        @empty
                                        <p class="px-3 py-2 text-xs text-gray-400">Sin resultados</p>
                                        @endforelse
                                    </div>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input type="text" wire:model.live="plan_search" placeholder="Buscar plan..."
                                        class="w-40 pl-8 pr-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">search</span>
                                    @if(strlen($plan_search) >= 1)
                                    <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-48 overflow-y-auto">
                                        @php $results = $this->searchedPlans; @endphp
                                        @forelse($results as $plan)
                                        <button wire:click="addPlanToZone({{ $plan->id }})" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-xs flex items-center gap-2 {{ isset($zone_plan_prices[$plan->id]) ? 'opacity-40' : '' }}" {{ isset($zone_plan_prices[$plan->id]) ? 'disabled' : '' }}>
                                            <span class="material-symbols-outlined text-xs text-green-500">add</span>
                                            {{ $plan->name }}
                                            @if($plan->speed) <span class="text-xs text-gray-400">({{ $plan->speed }})</span> @endif
                                        </button>
                                        @empty
                                        <p class="px-3 py-2 text-xs text-gray-400">Sin resultados</p>
                                        @endforelse
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @php
                            $validPrices = collect($zone_plan_prices)->filter(fn($item) => is_array($item) && isset($item['plan_service']));
                            $typeOrder = ['internet_cable', 'internet', 'cable'];
                            $groupLabels = ['internet' => 'Internet', 'cable' => 'Cable', 'internet_cable' => 'Internet + Cable'];
                            $groupColors = ['internet' => ['badge' => 'bg-blue-100 text-blue-700', 'header' => 'border-blue-200 bg-blue-50', 'dot' => 'bg-blue-400'],
                                            'cable' => ['badge' => 'bg-amber-100 text-amber-700', 'header' => 'border-amber-200 bg-amber-50', 'dot' => 'bg-amber-400'],
                                            'internet_cable' => ['badge' => 'bg-green-100 text-green-700', 'header' => 'border-green-200 bg-green-50', 'dot' => 'bg-green-400']];
                            $typeCounts = $validPrices->groupBy(fn($i) => $i['plan_service'])->map->count();
                        @endphp

                        <div class="text-xs text-gray-500 mb-2">
                            <span class="font-semibold text-gray-700">{{ $validPrices->count() }}</span> planes asignados
                        </div>

                        @if($validPrices->count() > 0)
                        <div class="space-y-2">
                            @foreach($typeOrder as $type)
                                @php $items = $validPrices->where('plan_service', $type); $isCollapsed = in_array($type, $collapsedTypes); @endphp
                                @if($items->count() > 0)
                                <div wire:key="g_{{ $type }}" class="border border-gray-200 rounded-xl overflow-hidden">
                                    <button wire:click="toggleCollapseType('{{ $type }}')" class="w-full flex items-center justify-between gap-3 px-4 py-2.5 text-sm font-medium {{ $groupColors[$type]['header'] }} hover:opacity-80 transition">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm {{ $isCollapsed ? 'text-gray-400' : 'text-gray-600' }}">{{ $isCollapsed ? 'expand_more' : 'expand_less' }}</span>
                                            <span class="w-2 h-2 rounded-full {{ $groupColors[$type]['dot'] }}"></span>
                                            {{ $groupLabels[$type] }}
                                            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $groupColors[$type]['badge'] }}">{{ $items->count() }} {{ $items->count() === 1 ? 'plan' : 'planes' }}</span>
                                        </div>
                                    </button>
                                    @if(!$isCollapsed)
                                    <div class="divide-y divide-gray-100">
                                        @foreach($items as $planId => $item)
                                        @php
                                            $hists = $item['history'] ?? [];
                                            $prevAdj = count($hists) >= 1 ? $hists[0] : null;
                                            $prevAdj2 = count($hists) >= 2 ? $hists[1] : null;
                                        @endphp
                                        <div wire:key="r_{{ $planId }}" class="px-4 py-2.5 hover:bg-gray-50 transition">
                                            <div class="flex items-center gap-3">
                                                <button wire:click="removePlanFromZone({{ $planId }})" class="flex-shrink-0 text-red-300 hover:text-red-500 transition" title="Quitar plan">
                                                    <span class="material-symbols-outlined text-sm">remove_circle</span>
                                                </button>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-sm font-medium text-gray-800">{{ $item['plan_name'] }}</span>
                                                        @if($item['plan_speed']) <span class="text-xs text-gray-400">({{ $item['plan_speed'] }})</span> @endif
                                                    </div>
                                                    <div class="text-xs text-gray-400">Base: <strong>${{ number_format($item['base_price'], 2) }}</strong></div>
                                                    @if($prevAdj || $prevAdj2)
                                                    <div class="text-xs text-gray-400 mt-0.5">
                                                        <span class="material-symbols-outlined text-xs align-text-bottom text-gray-500">trending_up</span>
                                                        @if($prevAdj2) Anterior: <strong class="text-gray-600">${{ number_format($prevAdj2['new_price'], 2) }}</strong> <span class="text-gray-300">→</span> @endif
                                                        @if($prevAdj) @if($prevAdj['new_price'] !== null) Actual: <strong class="text-gray-600">${{ number_format($prevAdj['new_price'], 2) }}</strong> @else Restablecido a base @endif @endif
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="relative flex-shrink-0">
                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                                                    <input type="number" step="0.01" wire:key="p_{{ $planId }}" wire:model="zone_plan_prices.{{ $planId }}.value" placeholder="Usar base" class="w-28 pl-7 pr-3 py-2 rounded-lg border border-gray-300 text-sm text-right">
                                                </div>
                                                @if($item['value'] !== null && $item['value'] !== '')
                                                <button wire:click="$set('zone_plan_prices.{{ $planId }}.value', '')" class="flex-shrink-0 text-red-400 hover:text-red-600" title="Usar precio base">
                                                    <span class="material-symbols-outlined text-sm">undo</span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-400 py-3 text-center border border-dashed border-gray-200 rounded-lg">No hay planes asignados. Buscá y agregá planes arriba.</p>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <x-ui.button variant="ghost" wire:click="$set('showZoneModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="saveZone">
                        @if($editingZoneId) Guardar cambios @elseif($zone_parent_id) Crear sub-zona @else Crear @endif
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL VER ZONA --}}
    @if($viewingZone)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-base">visibility</span>
                        {{ $viewingZone->name }}
                    </h3>
                    <button wire:click="closeViewZone" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    @php $ancestry = $this->zoneAncestry($viewingZone->id); @endphp
                    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-3">
                        <label class="text-xs font-medium text-gray-500 mb-2 block">📍 Ruta completa</label>
                        <div class="flex items-center flex-wrap gap-1">
                            @foreach($ancestry as $i => $item)
                            <x-ui.badge variant="{{ in_array($item['level'], ['departamento','municipio']) ? 'info' : 'neutral' }}">{{ $item['name'] }}</x-ui.badge>
                            @if($i < count($ancestry) - 1) <span class="text-gray-300 text-xs">›</span> @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs text-gray-500">Nivel</label><p class="text-sm font-medium text-gray-800">{{ ucfirst($viewingZone->level) }}</p></div>
                        <div><label class="text-xs text-gray-500">Sucursal</label><p class="text-sm font-medium text-gray-800">{{ $viewingZone->branch->name ?? '—' }}</p></div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 mb-1.5 block">Servicios disponibles</label>
                        <div class="flex gap-3">
                            <x-ui.badge variant="{{ $viewingZone->has_internet ? 'info' : 'neutral' }}"><span class="material-symbols-outlined text-xs align-text-bottom">language</span> Internet {{ $viewingZone->has_internet ? '✓' : '✗' }}</x-ui.badge>
                            <x-ui.badge variant="{{ $viewingZone->has_cable ? 'warning' : 'neutral' }}"><span class="material-symbols-outlined text-xs align-text-bottom">tv</span> Cable {{ $viewingZone->has_cable ? '✓' : '✗' }}</x-ui.badge>
                        </div>
                    </div>
                    @php $viewPlans = $viewingZone->prices()->with('plan')->get(); @endphp
                    @if($viewPlans->count() > 0)
                    <div>
                        <label class="text-xs text-gray-500 mb-1.5 block">Planes asignados ({{ $viewPlans->count() }})</label>
                        <div class="space-y-2">
                            @foreach($viewPlans as $vp)
                            @php
                                $hists = $viewingZonePriceHistories[$vp->plan_id] ?? collect();
                                $current = $vp->price ?? $vp->plan->base_price;
                                $latest = $hists->first();
                                $second = $hists->count() >= 2 ? $hists->get(1) : null;
                            @endphp
                            <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                        {{ $vp->plan->service_type === 'internet' ? 'bg-blue-400' : '' }}
                                        {{ $vp->plan->service_type === 'cable' ? 'bg-amber-400' : '' }}
                                        {{ $vp->plan->service_type === 'internet_cable' ? 'bg-green-400' : '' }}"></span>
                                    <span class="text-gray-800 font-medium">{{ $vp->plan->name }}</span>
                                    @if($vp->plan->speed) <span class="text-xs text-gray-400">({{ $vp->plan->speed }})</span> @endif
                                    <span class="ml-auto text-xs font-semibold {{ $vp->price !== null ? 'text-amber-600' : 'text-gray-500' }}">
                                        ${{ number_format($current, 2) }}
                                        @if($vp->price !== null) <span class="text-gray-400 font-normal">(ajustado)</span> @else <span class="text-gray-400 font-normal">base</span> @endif
                                    </span>
                                </div>
                                @if($latest)
                                @php $anterior = $latest->old_price ?? ($second?->new_price); @endphp
                                <div class="flex items-center gap-2 mt-1.5 text-xs text-gray-400">
                                    <span class="material-symbols-outlined text-xs align-text-bottom text-gray-500">trending_up</span> Historial:
                                    <span class="text-gray-600">Base: <strong>${{ number_format($vp->plan->base_price, 2) }}</strong></span>
                                    @if($anterior !== null && $anterior != $current && $anterior != $vp->plan->base_price)
                                    <span class="text-gray-300">→</span> <span>Anterior: <strong class="text-gray-600">${{ number_format($anterior, 2) }}</strong></span>
                                    @endif
                                    @if($latest->new_price !== null && $latest->new_price != $vp->plan->base_price)
                                    <span class="text-gray-300">→</span> <span>Actual: <strong class="text-gray-600">${{ number_format($latest->new_price, 2) }}</strong></span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="text-xs text-gray-500 mb-1.5 block">Sub-zonas ({{ $viewingZone->children->count() }})</label>
                        @if($viewingZone->children->count() > 0)
                        <ul class="text-sm text-gray-700 space-y-0.5">
                            @foreach($viewingZone->children as $child)
                            <li class="flex items-center gap-2"><span class="material-symbols-outlined text-xs text-gray-300">subdirectory_arrow_right</span>{{ $child->name }} <span class="text-xs text-gray-400">({{ ucfirst($child->level) }})</span></li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-sm text-gray-400">No tiene sub-zonas</p>
                        @endif
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                    <x-ui.button variant="ghost" wire:click="closeViewZone">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL PRECIO --}}
    @if($showPriceModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-sm">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-base">attach_money</span>
                        Ajustar precio
                    </h3>
                    <button wire:click="$set('showPriceModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1.5 block">Nuevo precio <span class="text-xs text-gray-400">(dejá vacío para usar precio base)</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                            <input type="number" step="0.01" wire:model="price_value" placeholder="Dejar vacío = precio base" class="w-full pl-8 pr-3 py-2.5 rounded-lg border border-gray-300 text-sm">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <x-ui.button variant="ghost" wire:click="$set('showPriceModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="savePrice">Guardar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL HISTORIAL DE PRECIOS --}}
    @if($showHistoryModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-base">history</span>
                        Historial de precios
                    </h3>
                    <button wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-5">
                    @if(count($historyRecords) > 0)
                    <div class="space-y-3">
                        @foreach($historyRecords as $record)
                        <div class="flex items-start gap-3 px-3 py-3 rounded-lg border border-gray-100 bg-gray-50/50">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-medium">${{ number_format($record->new_price ?? 0, 2) }}</span>
                                    @if($record->old_price !== null) <span class="text-xs text-gray-400">(antes ${{ number_format($record->old_price, 2) }})</span> @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $record->created_at?->format('d/m/Y H:i') }} @if($record->user) · {{ $record->user->name }} @endif</div>
                            </div>
                            @if($record->old_price === null) <x-ui.badge variant="success">Asignado</x-ui.badge>
                            @elseif($record->new_price === null) <x-ui.badge variant="neutral">Restablecido</x-ui.badge>
                            @elseif($record->new_price > $record->old_price) <x-ui.badge variant="danger">Subió</x-ui.badge>
                            @elseif($record->new_price < $record->old_price) <x-ui.badge variant="info">Bajó</x-ui.badge>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">history</span>
                        <p>No hay cambios registrados para este plan.</p>
                    </div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                    <x-ui.button variant="ghost" wire:click="closeHistoryModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL CONFIRMAR --}}
    @if($confirmingAction)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-sm">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6 text-center">
                <span class="material-symbols-outlined text-4xl text-amber-500 mb-3">warning</span>
                <p class="text-gray-700 mb-6">{{ $confirmMessage }}</p>
                <div class="flex justify-center gap-3">
                    <x-ui.button variant="ghost" wire:click="cancelConfirmation">Cancelar</x-ui.button>
                    <x-ui.button variant="danger" wire:click="executeConfirmedAction">Eliminar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
