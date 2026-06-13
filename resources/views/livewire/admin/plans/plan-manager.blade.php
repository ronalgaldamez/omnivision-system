<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">assignment</span>
                Gestión de Planes y Zonas
            </h1>
            <p class="text-sm text-gray-500 mt-1">Administra zonas geográficas, planes de servicio y precios por zona.</p>
        </div>

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
            </nav>
        </div>

        <div class="p-6">

            {{-- ========== TAB ZONAS (Árbol + Precios) ========== --}}
            @if($activeTab === 'zones')
            <div class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-sm font-semibold text-gray-700">Estructura de Zonas</h2>
                    <button wire:click="openZoneModal"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">add</span>
                        Nueva Zona Raíz
                    </button>
                </div>

                {{-- Árbol por sucursal --}}
                @forelse($branches as $branch)
                    @php $branchRoots = $rootZones->where('branch_id', $branch->id); @endphp
                    @if($branchRoots->count() > 0)
                    <div class="bg-gray-50/50 rounded-xl border border-gray-200 overflow-hidden">
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
                <div class="bg-white rounded-xl border border-blue-200 overflow-hidden shadow-sm">
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
                        <button wire:click="$set('selectedZoneId', null)"
                            class="text-blue-400 hover:text-blue-600">
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
                                        <span class="text-xs px-1.5 py-0.5 rounded-full
                                            {{ $item['plan_service'] === 'internet' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $item['plan_service'] === 'cable' ? 'bg-amber-100 text-amber-700' : '' }}
                                            {{ $item['plan_service'] === 'internet_cable' ? 'bg-green-100 text-green-700' : '' }}">
                                            {{ str_replace('_', ' + ', ucfirst($item['plan_service'])) }}
                                        </span>
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
                                            <button wire:click="editPrice({{ $item['plan_id'] }})"
                                                class="text-xs px-2 py-1 rounded {{ $item['override_price'] !== null ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                                {{ $item['override_price'] !== null ? 'Editar' : 'Ajustar' }}
                                            </button>
                                            @if($item['override_price'] !== null)
                                            <button wire:click="removePriceOverride({{ $item['plan_id'] }})"
                                                class="text-xs px-2 py-1 rounded bg-red-50 text-red-500 hover:bg-red-100"
                                                title="Restablecer herencia">
                                                Quitar
                                            </button>
                                            @endif
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
                <div class="flex items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <input type="text" wire:model.live="planSearch" placeholder="Buscar plan..."
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    </div>
                    <button wire:click="openPlanModal"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">add</span>
                        Nuevo Plan
                    </button>
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
                                <th class="text-right px-4 py-3 font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($plans as $plan)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $plan->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $plan->service_type === 'internet' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $plan->service_type === 'cable' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $plan->service_type === 'internet_cable' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ str_replace('_', ' + ', ucfirst($plan->service_type)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">${{ number_format($plan->base_price, 2) }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $plan->speed ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $plan->channels ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button wire:click="openPlanModal({{ $plan->id }})"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">Editar</button>
                                    <button wire:click="promptDeletePlan({{ $plan->id }})"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium ml-3">Eliminar</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">
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

        </div>
    </div>

    {{-- ========== MODAL ZONA ========== --}}
    @if($showZoneModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-xl">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 text-base">add_location</span>
                        {{ $editingZoneId ? 'Editar Zona' : ($zone_parent_id ? 'Nueva Sub-zona' : 'Nueva Zona Raíz') }}
                    </h3>
                    <button wire:click="$set('showZoneModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Breadcrumb de ubicación (solo sub-zonas o edición) --}}
                    @if($zone_parent_id || $editingZoneId)
                    @php
                        $targetId = $zone_parent_id ?: ($editingZoneId ? \App\Models\Zone::find($editingZoneId)?->parent_id : null);
                    @endphp
                    @if($targetId)
                    @php $ancestry = $this->zoneAncestry($targetId); @endphp
                    @if(count($ancestry) > 0)
                    <div class="bg-blue-50/80 border border-blue-200 rounded-lg px-4 py-3">
                        <label class="text-xs font-medium text-blue-600 mb-1.5 block">Ubicación en la jerarquía</label>
                        <div class="flex items-center flex-wrap gap-1 text-sm">
                            @foreach($ancestry as $i => $item)
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                {{ $item['level'] === 'departamento' ? 'bg-purple-100 text-purple-700' : '' }}
                                {{ $item['level'] === 'municipio' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $item['level'] === 'distrito' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                {{ $item['level'] === 'cantón' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ !in_array($item['level'], ['departamento','municipio','distrito','cantón']) ? 'bg-gray-100 text-gray-600' : '' }}">
                                {{ $item['name'] }}
                            </span>
                            @if($i < count($ancestry) - 1)
                            <span class="text-gray-300 text-xs">›</span>
                            @endif
                            @endforeach
                            @if(!$editingZoneId)
                            <span class="text-gray-300 text-xs mx-1">›</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700 border border-yellow-300 border-dashed">
                                {{ $zone_name ?: 'Nuevo' }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif

                    {{-- 1. SELECCIONAR PADRE (solo crear raíz) --}}
                    @if(!$editingZoneId && !$zone_parent_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Depende de (zona padre)</label>
                        <select wire:model.live="zone_parent_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                            <option value="">Ninguna — es una zona raíz</option>
                            @foreach(\App\Models\Zone::where('branch_id', $zone_branch_id ?: 0)->orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} ({{ ucfirst($p->level) }})
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Si seleccionás un padre, el nivel se calculará automáticamente.</p>
                    </div>
                    @endif

                    {{-- 2. NOMBRE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" wire:model="zone_name" placeholder="ej. Chalatenango Sur"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                        @error('zone_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- 3. SUCURSAL --}}
                    @if(!$zone_parent_id && !$editingZoneId)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal *</label>
                        <select wire:model="zone_branch_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                            <option value="">Seleccione</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('zone_branch_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    @elseif($zone_branch_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
                        <input type="text" readonly value="{{ \App\Models\Branch::find($zone_branch_id)?->name ?? '—' }}"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-gray-50 text-gray-500">
                    </div>
                    @endif

                    {{-- 4. NIVEL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                        @if($zone_parent_id || $editingZoneId)
                        <select wire:model="zone_level" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                            <option value="">Seleccione</option>
                            <option value="departamento">Departamento</option>
                            <option value="municipio">Municipio</option>
                            <option value="distrito">Distrito</option>
                            <option value="cantón">Cantón</option>
                            <option value="caserío">Caserío</option>
                            <option value="colonia">Colonia</option>
                            <option value="barrio">Barrio</option>
                            <option value="localidad">Localidad</option>
                        </select>
                        @if($zone_parent_id && !$editingZoneId)
                        <p class="text-xs text-amber-600 mt-1">Nivel sugerido: <strong>{{ ucfirst($zone_level ?: '...') }}</strong> (según el padre)</p>
                        @endif
                        @else
                        <input type="text" readonly value="Departamento"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm bg-gray-50 text-gray-500">
                        @endif
                    </div>

                    {{-- 5. INTERNET / CABLE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Servicios disponibles en esta zona</label>
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="zone_has_internet" class="rounded text-blue-600">
                                <span class="text-sm text-gray-700">Internet</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="zone_has_cable" class="rounded text-blue-600">
                                <span class="text-sm text-gray-700">Cable</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Esto filtra qué planes estarán disponibles en el panel de precios.</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <button wire:click="$set('showZoneModal', false)"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="saveZone"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        {{ $editingZoneId ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========== MODAL PLAN ========== --}}
    @if($showPlanModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">{{ $editingPlanId ? 'Editar Plan' : 'Nuevo Plan' }}</h3>
                    <button wire:click="$set('showPlanModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" wire:model="plan_name" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                        @error('plan_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea wire:model="plan_description" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de servicio</label>
                        <select wire:model="plan_service_type" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                            <option value="internet">Solo Internet</option>
                            <option value="cable">Solo Cable</option>
                            <option value="internet_cable">Internet + Cable</option>
                        </select>
                        @error('plan_service_type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Precio base ($) *</label>
                            <input type="number" step="0.01" wire:model="plan_base_price" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                            @error('plan_base_price') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Velocidad (Mbps)</label>
                            <input type="text" wire:model="plan_speed" placeholder="ej. 10" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Canales (solo cable)</label>
                        <input type="number" wire:model="plan_channels" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <button wire:click="$set('showPlanModal', false)"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="savePlan"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        {{ $editingPlanId ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========== MODAL PRECIO ========== --}}
    @if($showPriceModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Ajustar Precio</h3>
                    <button wire:click="$set('showPriceModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-sm text-gray-600">
                        Plan: <strong>{{ collect($zonePrices)->firstWhere('plan_id', $editingPriceId)['plan_name'] ?? '' }}</strong>
                    </p>
                    <p class="text-sm text-gray-500">
                        Precio base: <strong>${{ number_format(collect($zonePrices)->firstWhere('plan_id', $editingPriceId)['base_price'] ?? 0, 2) }}</strong>
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio para esta zona</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                            <input type="number" step="0.01" wire:model="price_value" placeholder="Dejar vacío para heredar"
                                class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-300 text-sm">
                        </div>
                        @error('price_value') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-400 mt-1">Dejá vacío para que herede el precio de la zona padre.</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                    <button wire:click="$set('showPriceModal', false)"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="savePrice"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ========== MODAL CONFIRMAR ========== --}}
    @if($confirmingAction)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-sm">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6 text-center">
                <span class="material-symbols-outlined text-4xl text-amber-500 mb-3">warning</span>
                <p class="text-gray-700 mb-6">{{ $confirmMessage }}</p>
                <div class="flex justify-center gap-3">
                    <button wire:click="cancelConfirmation"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button wire:click="executeConfirmedAction"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
