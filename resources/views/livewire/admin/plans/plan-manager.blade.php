<div class="max-w-7xl mx-auto">
    <x-ui.card icon="assignment" title="Gestión de Planes y Zonas" subtitle="Administrá zonas geográficas, planes de servicio y precios por zona." class="overflow-visible">
        {{-- Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex gap-1 px-6">
                <button wire:click="setTab('zones')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'zones' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">map</span>
                    Zonas y Precios
                </button>
                <button wire:click="setTab('plans')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'plans' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">subscriptions</span>
                    Planes
                </button>
                <button wire:click="setTab('groups')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'groups' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">folder</span>
                    Grupos
                </button>
                <button wire:click="setTab('history')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'history' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">history</span>
                    Historial
                </button>
            </nav>
        </div>

        <div class="p-6">

            {{-- ========== TAB ZONAS (Árbol + Precios) ========== --}}
            @if($activeTab === 'zones')
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

                {{-- ========== PANEL DE PRECIOS ========== --}}
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
            @endif

            {{-- ========== TAB PLANES ========== --}}
            @if($activeTab === 'plans')
            <div class="space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center gap-1">
                        <button wire:click="$set('planFilterType', '')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $planFilterType === '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Todos</button>
                        <button wire:click="$set('planFilterType', 'internet')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $planFilterType === 'internet' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Internet</button>
                        <button wire:click="$set('planFilterType', 'cable')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $planFilterType === 'cable' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Cable</button>
                        <button wire:click="$set('planFilterType', 'internet_cable')" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $planFilterType === 'internet_cable' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Internet + Cable</button>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 max-w-sm">
                            <input type="text" wire:model.live="planSearch" placeholder="Buscar plan..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-400">$</span>
                            <input type="number" step="0.01" wire:model.live="planPriceMin" placeholder="Min" class="w-20 px-2 py-2 rounded-lg border border-gray-300 text-sm text-center">
                            <span class="text-xs text-gray-400">—</span>
                            <input type="number" step="0.01" wire:model.live="planPriceMax" placeholder="Max" class="w-20 px-2 py-2 rounded-lg border border-gray-300 text-sm text-center">
                        </div>
                        <x-ui.button variant="primary" icon="add" wire:click="openPlanModal">Nuevo Plan</x-ui.button>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium">Nombre</th>
                                <th class="text-left px-4 py-3 font-medium">Tipo</th>
                                <th class="text-right px-4 py-3 font-medium">Precio Base</th>
                                <th class="text-center px-4 py-3 font-medium">Velocidad</th>
                                <th class="text-center px-4 py-3 font-medium">Canales</th>
                                <th class="text-center px-4 py-3 font-medium">Estado</th>
                                <th class="text-right px-4 py-3 font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($plans as $plan)
                            <tr class="hover:bg-gray-50/50 {{ !$plan->is_active ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $plan->name }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badgeVariant = match($plan->service_type) {
                                            'internet' => 'info',
                                            'cable' => 'warning',
                                            'internet_cable' => 'success',
                                            default => 'neutral'
                                        };
                                    @endphp
                                    <x-ui.badge variant="{{ $badgeVariant }}">{{ str_replace('_', ' + ', ucfirst($plan->service_type)) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">${{ number_format($plan->base_price, 2) }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $plan->speed ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $plan->channels ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <x-ui.badge variant="{{ $plan->is_active ? 'success' : 'danger' }}">{{ $plan->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button wire:click="viewPlan({{ $plan->id }})" class="text-gray-500 hover:text-gray-700 text-sm font-medium" title="Ver detalles">
                                        <span class="material-symbols-outlined text-sm align-text-bottom">visibility</span>
                                    </button>
                                    <x-ui.button variant="ghost" wire:click="openPlanModal({{ $plan->id }})" class="text-sm ml-2">Editar</x-ui.button>
                                    <x-ui.button variant="ghost" wire:click="togglePlanActive({{ $plan->id }})" class="text-sm ml-2 {{ $plan->is_active ? 'text-amber-600' : 'text-green-600' }}">{{ $plan->is_active ? 'Desactivar' : 'Activar' }}</x-ui.button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">subscriptions</span>
                                    <p>No hay planes registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($plans->hasPages())
                <div class="mt-4">{{ $plans->links() }}</div>
                @endif
            </div>
            @endif

            {{-- ========== TAB GRUPOS ========== --}}
            @if($activeTab === 'groups')
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-sm font-semibold text-gray-700">Grupos de Planes</h2>
                    <x-ui.button variant="primary" icon="add" wire:click="openGroupModal">Nuevo Grupo</x-ui.button>
                </div>

                @if(count($planGroups) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($planGroups as $group)
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-sm transition">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">{{ $group->name }}</h3>
                                @if($group->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $group->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="ghost" wire:click="openGroupModal({{ $group->id }})" class="text-sm">Editar</x-ui.button>
                                <x-ui.button variant="ghost" wire:click="confirmDeleteGroup({{ $group->id }})" class="text-sm text-red-500">Eliminar</x-ui.button>
                            </div>
                        </div>
                        <div class="px-4 py-2.5 bg-gray-50/50">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span class="material-symbols-outlined text-sm">view_list</span>
                                {{ $group->plans_count }} planes
                                <span class="text-gray-300 mx-1">|</span>
                                <span class="material-symbols-outlined text-sm">calendar_today</span>
                                {{ $group->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        @php $groupPlans = $group->plans()->take(5)->get(); @endphp
                        @if($groupPlans->count() > 0)
                        <div class="px-4 py-2 space-y-1">
                            @foreach($groupPlans as $plan)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ $plan->service_type === 'internet' ? 'bg-blue-400' : '' }}
                                    {{ $plan->service_type === 'cable' ? 'bg-amber-400' : '' }}
                                    {{ $plan->service_type === 'internet_cable' ? 'bg-green-400' : '' }}"></span>
                                {{ $plan->name }}
                                <span class="text-gray-400">${{ number_format($plan->base_price, 2) }}</span>
                            </div>
                            @endforeach
                            @if($group->plans_count > 5)
                            <p class="text-xs text-gray-400 pt-1">+{{ $group->plans_count - 5 }} más</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 text-gray-500 bg-gray-50/50 rounded-xl border border-gray-200">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">folder</span>
                    <p class="font-medium">No hay grupos de planes</p>
                    <p class="text-xs mt-1">Creá grupos para asignar varios planes a una zona de una sola vez.</p>
                </div>
                @endif
            </div>
            @endif

            {{-- ========== TAB HISTORIAL ========== --}}
            @if($activeTab === 'history')
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <input type="text" wire:model.live="historySearch" placeholder="Buscar por plan o zona..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    </div>
                    <input type="date" wire:model.live="historyDateFrom" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">
                    <span class="text-xs text-gray-400">—</span>
                    <input type="date" wire:model.live="historyDateTo" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium">Fecha</th>
                                <th class="text-left px-4 py-3 font-medium">Zona</th>
                                <th class="text-left px-4 py-3 font-medium">Plan</th>
                                <th class="text-right px-4 py-3 font-medium">Anterior</th>
                                <th class="text-right px-4 py-3 font-medium">Nuevo</th>
                                <th class="text-center px-4 py-3 font-medium">Cambio</th>
                                <th class="text-left px-4 py-3 font-medium">Usuario</th>
                                <th class="text-center px-4 py-3 font-medium">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($priceHistories as $h)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $h->zone?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $h->plan?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-gray-500">
                                    @if($h->old_price !== null) ${{ number_format($h->old_price, 2) }} @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium">
                                    @if($h->new_price !== null) ${{ number_format($h->new_price, 2) }} @else <span class="text-gray-300">—</span> @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($h->old_price === null && $h->new_price !== null)
                                        <x-ui.badge variant="success">Asignado</x-ui.badge>
                                    @elseif($h->new_price === null)
                                        <x-ui.badge variant="neutral">Restablecido</x-ui.badge>
                                    @elseif($h->new_price > $h->old_price)
                                        <x-ui.badge variant="danger">Subió</x-ui.badge>
                                    @elseif($h->new_price < $h->old_price)
                                        <x-ui.badge variant="info">Bajó</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $h->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="loadPriceHistory({{ $h->plan_id }}, {{ $h->zone_id ?? 'null' }})" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-500 hover:bg-gray-200" title="Ver detalle">
                                        <span class="material-symbols-outlined text-xs align-text-bottom">visibility</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">history</span>
                                    <p>No hay cambios registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($priceHistories->hasPages())
                <div class="mt-4">{{ $priceHistories->links() }}</div>
                @endif
            </div>
            @endif

        </div>
    </x-ui.card>

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
                            Agregar sub-zona
                        @else
                            Nueva Zona Raíz
                        @endif
                    </h3>
                    <button wire:click="$set('showZoneModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-5 space-y-5">
                    {{-- NUEVA ZONA RAÍZ --}}
                    @if(!$editingZoneId && !$zone_parent_id)
                    <div class="bg-amber-50/60 border border-amber-200 rounded-lg px-4 py-3">
                        <p class="text-xs text-amber-700 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">info</span>
                            Vas a crear un Departamento. Opcionalmente podés crear también su primer Municipio ahora mismo.
                        </p>
                    </div>
                    <x-forms.group name="zone_name" label="Departamento *">
                        <x-ui.input type="text" wire:model="zone_name" placeholder="ej. Chalatenango" />
                    </x-forms.group>
                    <x-forms.group name="zone_branch_id" label="Sucursal *">
                        <x-ui.select wire:model="zone_branch_id" placeholder="Seleccione la sucursal">
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-forms.group>
                    <x-forms.group name="zone_municipio_name" label="Municipio (opcional)">
                        <x-ui.input type="text" wire:model="zone_municipio_name" placeholder="ej. Chalatenango Sur" />
                    </x-forms.group>
                    @if($zone_name)
                    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-3">
                        <p class="text-xs text-gray-500 mb-1">Se va a crear:</p>
                        <div class="flex items-center gap-1.5 text-sm">
                            <x-ui.badge variant="info">{{ $zone_name ?: '—' }}</x-ui.badge>
                            @if($zone_municipio_name)
                            <span class="text-gray-300 text-xs">›</span>
                            <x-ui.badge variant="info">{{ $zone_municipio_name }}</x-ui.badge>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- SUB-ZONA --}}
                    @elseif($zone_parent_id && !$editingZoneId)
                    @php $ancestry = $this->zoneAncestry($zone_parent_id); @endphp
                    @if(count($ancestry) > 0)
                    <div class="bg-blue-50/80 border border-blue-200 rounded-lg px-4 py-3">
                        <label class="text-xs font-medium text-blue-600 mb-1.5 block">📍 Dónde se ubica</label>
                        <div class="flex items-center flex-wrap gap-1 text-sm">
                            @foreach($ancestry as $i => $item)
                            <x-ui.badge variant="{{ $item['level'] === 'departamento' ? 'info' : ($item['level'] === 'municipio' ? 'info' : 'neutral') }}">{{ $item['name'] }}</x-ui.badge>
                            @if($i < count($ancestry) - 1) <span class="text-gray-300 text-xs">›</span> @endif
                            @endforeach
                            <span class="text-gray-300 text-xs mx-1">›</span>
                            <x-ui.badge variant="warning">{{ $zone_name ?: 'Nuevo' }}</x-ui.badge>
                            <span class="text-xs text-gray-400 ml-1">({{ ucfirst($zone_level) }})</span>
                        </div>
                    </div>
                    @endif
                    <x-forms.group name="zone_branch_id" label="Sucursal">
                        <x-ui.input type="text" readonly value="{{ \App\Models\Branch::find($zone_branch_id)?->name ?? '—' }}" />
                    </x-forms.group>
                    <x-forms.group name="zone_level" label="Nivel">
                        <x-ui.select wire:model.live="zone_level">
                            <option value="distrito">Distrito</option>
                            <option value="cantón">Cantón</option>
                            <option value="caserío">Caserío</option>
                            <option value="colonia">Colonia</option>
                            <option value="barrio">Barrio</option>
                            <option value="localidad">Localidad</option>
                        </x-ui.select>
                    </x-forms.group>
                    <x-forms.group name="zone_name" label="Nombre *">
                        <x-ui.input type="text" wire:model="zone_name" placeholder="ej. El Paraíso" />
                    </x-forms.group>
                    @if(!in_array($zone_level, ['departamento','municipio']))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Servicios disponibles</label>
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-ui.checkbox wire:model="zone_has_internet" /> <span class="text-sm text-gray-700">Internet</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-ui.checkbox wire:model="zone_has_cable" /> <span class="text-sm text-gray-700">Cable</span>
                            </label>
                        </div>
                    </div>
                    @endif

                    {{-- EDITAR ZONA --}}
                    @elseif($editingZoneId)
                    <x-forms.group name="zone_name" label="Nombre">
                        <x-ui.input type="text" wire:model="zone_name" />
                    </x-forms.group>
                    <x-forms.group name="zone_level" label="Nivel">
                        <x-ui.select wire:model.live="zone_level">
                            <option value="departamento">Departamento</option>
                            <option value="municipio">Municipio</option>
                            <option value="distrito">Distrito</option>
                            <option value="cantón">Cantón</option>
                            <option value="caserío">Caserío</option>
                            <option value="colonia">Colonia</option>
                            <option value="barrio">Barrio</option>
                            <option value="localidad">Localidad</option>
                        </x-ui.select>
                    </x-forms.group>
                    @if(!in_array($zone_level, ['departamento','municipio']))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Servicios disponibles</label>
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-ui.checkbox wire:model="zone_has_internet" /> <span class="text-sm text-gray-700">Internet</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-ui.checkbox wire:model="zone_has_cable" /> <span class="text-sm text-gray-700">Cable</span>
                            </label>
                        </div>
                    </div>
                    @endif
                    @endif

                    {{-- Planes asignados --}}
                    @if(!in_array($zone_level, ['departamento','municipio']))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="material-symbols-outlined text-base align-text-bottom text-amber-600">attach_money</span>
                            Planes asignados a esta zona
                        </label>

                        @if(count($planGroups) > 0)
                        <div class="relative mb-2">
                            <x-ui.input type="text" wire:model.live="group_search" placeholder="Buscar grupo para agregar todos sus planes..." icon="folder" class="border-amber-200 bg-amber-50/30" />
                            @if(strlen($group_search) >= 1)
                            <button wire:click="$set('group_search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                            @endif
                            @php $groupResults = $this->searchedGroups; @endphp
                            @if(strlen($group_search) >= 1 && $groupResults->count() > 0)
                            <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-48 overflow-y-auto">
                                @foreach($groupResults as $group)
                                <button wire:click="addGroupToZone({{ $group->id }})" class="w-full text-left px-4 py-2.5 hover:bg-amber-50 text-sm flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-amber-500">folder</span>
                                    <span class="font-medium">{{ $group->name }}</span>
                                    <span class="text-xs text-gray-400">({{ $group->plans->count() }} planes)</span>
                                    <span class="text-xs text-gray-400 ml-auto">{{ $group->created_at->format('d/m/Y') }}</span>
                                </button>
                                @endforeach
                            </div>
                            @elseif(strlen($group_search) >= 1 && $groupResults->count() === 0)
                            <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 p-4 text-center text-sm text-gray-400">
                                No se encontraron grupos
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="relative mb-3">
                            <x-ui.input type="text" wire:model.live="plan_search" placeholder="Buscar plan para agregar..." icon="search" />
                            @if(strlen($plan_search) >= 1)
                            <button wire:click="$set('plan_search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                            @endif
                            @php $results = $this->searchedPlans; @endphp
                            @if(strlen($plan_search) >= 1 && $results->count() > 0)
                            <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-48 overflow-y-auto">
                                @foreach($results as $plan)
                                <button wire:click="addPlanToZone({{ $plan->id }})" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm flex items-center gap-2 {{ isset($zone_plan_prices[$plan->id]) ? 'opacity-40' : '' }}" {{ isset($zone_plan_prices[$plan->id]) ? 'disabled' : '' }}>
                                    <span class="material-symbols-outlined text-sm text-green-500">add</span>
                                    {{ $plan->name }}
                                    @if($plan->speed) <span class="text-xs text-gray-400">({{ $plan->speed }})</span> @endif
                                    @php
                                        $badgeV = match($plan->service_type) { 'internet' => 'info', 'cable' => 'warning', default => 'success' };
                                    @endphp
                                    <x-ui.badge variant="{{ $badgeV }}">{{ str_replace('_', ' + ', ucfirst($plan->service_type)) }}</x-ui.badge>
                                    <span class="text-xs text-gray-400 ml-auto">${{ number_format($plan->base_price, 2) }}</span>
                                </button>
                                @endforeach
                            </div>
                            @elseif(strlen($plan_search) >= 1 && $results->count() === 0)
                            <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-200 p-4 text-center text-sm text-gray-400">
                                No se encontraron planes
                            </div>
                            @endif
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
                                                        <span class="text-gray-500">📈</span>
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
                            <x-ui.badge variant="{{ $viewingZone->has_internet ? 'info' : 'neutral' }}">🌐 Internet {{ $viewingZone->has_internet ? '✔' : '✖' }}</x-ui.badge>
                            <x-ui.badge variant="{{ $viewingZone->has_cable ? 'warning' : 'neutral' }}">📺 Cable {{ $viewingZone->has_cable ? '✔' : '✖' }}</x-ui.badge>
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
                                    <span class="text-gray-500">📈 Historial:</span>
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

    {{-- MODAL PLAN --}}
    @if($showPlanModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">{{ $editingPlanId ? 'Editar Plan' : 'Nuevo Plan' }}</h3>
                    <button wire:click="$set('showPlanModal', false)" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-5 space-y-4">
                    <x-forms.group name="plan_service_type" label="Tipo de servicio">
                        <x-ui.select wire:model.live="plan_service_type">
                            <option value="internet">Solo Internet</option>
                            <option value="cable">Solo Cable</option>
                            <option value="internet_cable">Internet + Cable</option>
                        </x-ui.select>
                    </x-forms.group>
                    <x-forms.group name="plan_name" label="Nombre del plan *">
                        <x-ui.input type="text" wire:model="plan_name" />
                    </x-forms.group>
                    <x-forms.group name="plan_description" label="Descripción">
                        <x-ui.textarea wire:model="plan_description" rows="2" />
                    </x-forms.group>
                    @if($plan_service_type === 'cable')
                        <x-forms.group name="plan_base_price" label="Precio base ($) *">
                            <x-ui.input type="number" step="0.01" wire:model="plan_base_price" />
                        </x-forms.group>
                    @else
                        <div class="grid grid-cols-2 gap-4">
                            <x-forms.group name="plan_base_price" label="Precio base ($) *">
                                <x-ui.input type="number" step="0.01" wire:model="plan_base_price" />
                            </x-forms.group>
                            <x-forms.group name="plan_speed" label="Velocidad">
                                <x-ui.input type="text" wire:model="plan_speed" placeholder="ej. 300" />
                                <p class="text-xs text-gray-400 mt-1">Se agrega "Mbps" automáticamente.</p>
                            </x-forms.group>
                        </div>
                    @endif
                    @if(in_array($plan_service_type, ['cable', 'internet_cable']))
                    <x-forms.group name="plan_channels" label="Canales">
                        <x-ui.input type="number" wire:model="plan_channels" />
                    </x-forms.group>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <x-ui.button variant="ghost" wire:click="$set('showPlanModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="savePlan">{{ $editingPlanId ? 'Actualizar' : 'Crear' }}</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL GRUPO --}}
    @if($showGroupModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-2xl">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">{{ $editingGroupId ? 'Editar Grupo' : 'Nuevo Grupo' }}</h3>
                    <button wire:click="$set('showGroupModal', false)" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-5 space-y-4">
                    <x-forms.group name="group_name" label="Nombre del grupo *">
                        <x-ui.input type="text" wire:model="group_name" placeholder="ej. La Palma" />
                    </x-forms.group>
                    <x-forms.group name="group_description" label="Descripción">
                        <x-ui.textarea wire:model="group_description" rows="2" placeholder="Opcional" />
                    </x-forms.group>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Planes del grupo *</label>
                        @error('group_plan_ids') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        <div class="flex items-center gap-1 mb-3">
                            <button wire:click="$set('groupPlanFilterType', '')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $groupPlanFilterType === '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Todos</button>
                            <button wire:click="$set('groupPlanFilterType', 'internet')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $groupPlanFilterType === 'internet' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Internet</button>
                            <button wire:click="$set('groupPlanFilterType', 'cable')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $groupPlanFilterType === 'cable' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Cable</button>
                            <button wire:click="$set('groupPlanFilterType', 'internet_cable')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $groupPlanFilterType === 'internet_cable' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Internet + Cable</button>
                        </div>
                        @php
                            $filteredPlans = $groupPlanFilterType ? $allPlans->where('service_type', $groupPlanFilterType) : $allPlans;
                            $filteredIds = $filteredPlans->pluck('id')->toArray();
                            $allFilteredSelected = count($filteredIds) > 0 && !array_diff($filteredIds, $this->group_plan_ids ?? []);
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-2 bg-gray-50 rounded-t-lg border border-gray-200 border-b-0 text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <x-ui.checkbox wire:click="toggleAllFilteredPlans({{ $allFilteredSelected ? 'false' : 'true' }})" {{ $allFilteredSelected ? 'checked' : '' }} />
                                <span class="text-xs font-medium text-gray-600">{{ $allFilteredSelected ? 'Deseleccionar todos' : 'Seleccionar todos' }} ({{ count($filteredIds) }} planes)</span>
                            </label>
                        </div>
                        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100 {{ count($filteredIds) > 0 ? 'rounded-t-none border-t-0' : '' }}">
                            @forelse($filteredPlans as $plan)
                            <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
                                <x-ui.checkbox wire:model="group_plan_ids" value="{{ $plan->id }}" />
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-800">{{ $plan->name }}</span>
                                    @if($plan->speed) <span class="text-xs text-gray-400">({{ $plan->speed }})</span> @endif
                                </div>
                                @php $badgeV = match($plan->service_type) { 'internet' => 'info', 'cable' => 'warning', default => 'success' }; @endphp
                                <x-ui.badge variant="{{ $badgeV }}">{{ str_replace('_', ' + ', ucfirst($plan->service_type)) }}</x-ui.badge>
                                <span class="text-xs text-gray-500">${{ number_format($plan->base_price, 2) }}</span>
                            </label>
                            @empty
                            <div class="px-4 py-8 text-center text-sm text-gray-400">No hay planes de este tipo</div>
                            @endforelse
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Seleccioná los planes que pertenecen a este grupo.</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <x-ui.button variant="ghost" wire:click="$set('showGroupModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="saveGroup">{{ $editingGroupId ? 'Actualizar' : 'Crear' }}</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL PRECIO --}}
    @if($showPriceModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Ajustar Precio</h3>
                    <button wire:click="$set('showPriceModal', false)" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-sm text-gray-600">Plan: <strong>{{ collect($zonePrices)->firstWhere('plan_id', $editingPriceId)['plan_name'] ?? '' }}</strong></p>
                    <p class="text-sm text-gray-500">Precio base: <strong>${{ number_format(collect($zonePrices)->firstWhere('plan_id', $editingPriceId)['base_price'] ?? 0, 2) }}</strong></p>
                    <x-forms.group name="price_value" label="Precio para esta zona">
                        <x-ui.input type="number" step="0.01" wire:model="price_value" placeholder="Dejar vacío para heredar" />
                        <p class="text-xs text-gray-400 mt-1">Dejá vacío para que herede el precio de la zona padre.</p>
                    </x-forms.group>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <x-ui.button variant="ghost" wire:click="$set('showPriceModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="savePrice">Guardar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL VER PLAN --}}
    @if($viewingPlan)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-base">visibility</span>
                        {{ $viewingPlan->name }}
                    </h3>
                    <button wire:click="closeViewPlan" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500">Tipo de servicio</label>
                            <p class="text-sm font-medium text-gray-800 mt-1">
                                @php $badgeV = match($viewingPlan->service_type) { 'internet' => 'info', 'cable' => 'warning', default => 'success' }; @endphp
                                <x-ui.badge variant="{{ $badgeV }}">{{ str_replace('_', ' + ', ucfirst($viewingPlan->service_type)) }}</x-ui.badge>
                            </p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Estado</label>
                            <p class="text-sm font-medium text-gray-800 mt-1">
                                <x-ui.badge variant="{{ $viewingPlan->is_active ? 'success' : 'danger' }}">{{ $viewingPlan->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                            </p>
                        </div>
                    </div>
                    @if($viewingPlan->description)
                    <div><label class="text-xs text-gray-500">Descripción</label><p class="text-sm text-gray-800 mt-1">{{ $viewingPlan->description }}</p></div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs text-gray-500">Precio base</label><p class="text-lg font-bold text-amber-600">${{ number_format($viewingPlan->base_price, 2) }}</p></div>
                        @if($viewingPlan->speed) <div><label class="text-xs text-gray-500">Velocidad</label><p class="text-sm font-medium text-gray-800">{{ $viewingPlan->speed }} Mbps</p></div> @endif
                    </div>
                    @if($viewingPlan->channels) <div><label class="text-xs text-gray-500">Canales</label><p class="text-sm font-medium text-gray-800">{{ $viewingPlan->channels }}</p></div> @endif
                    @if($viewingPlanHistories->count() > 0)
                    <div class="pt-2 border-t border-gray-100">
                        <label class="text-xs text-gray-500 mb-2 block">📈 Historial de cambios de precio base</label>
                        <div class="space-y-1.5">
                            @foreach($viewingPlanHistories as $vh)
                            <div class="flex items-center justify-between px-3 py-1.5 bg-gray-50 rounded-lg text-xs">
                                <div>
                                    @if($vh->old_price !== null) <span class="text-gray-500">${{ number_format($vh->old_price, 2) }}</span> <span class="text-gray-300 mx-1">→</span> @endif
                                    <span class="font-semibold {{ $vh->new_price > $vh->old_price ? 'text-red-600' : ($vh->new_price < $vh->old_price ? 'text-blue-600' : 'text-gray-700') }}">${{ number_format($vh->new_price, 2) }}</span>
                                    @if($vh->old_price !== null && $vh->new_price !== null)
                                        @if($vh->new_price > $vh->old_price) <span class="text-red-500 ml-1">▲</span> @elseif($vh->new_price < $vh->old_price) <span class="text-blue-500 ml-1">▼</span> @endif
                                    @endif
                                </div>
                                <span class="text-gray-400">{{ $vh->created_at?->format('d/m/Y H:i') }} @if($vh->user) · {{ $vh->user->name }} @endif</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="text-xs text-gray-400 pt-2 border-t border-gray-100">
                        <p>Creado: {{ $viewingPlan->created_at?->format('d/m/Y H:i') }}</p>
                        <p>Actualizado: {{ $viewingPlan->updated_at?->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                    <x-ui.button variant="ghost" wire:click="closeViewPlan">Cerrar</x-ui.button>
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