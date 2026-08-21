<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            @if($category === 'contract_rule')
                <h3 class="text-sm font-semibold text-gray-700">Reglas de contrato</h3>
                <p class="text-xs text-gray-500">Condiciones permanentes de la contratación (meses gratis, doble velocidad todo el contrato). Sin fechas.</p>
            @else
                <h3 class="text-sm font-semibold text-gray-700">Promociones</h3>
                <p class="text-xs text-gray-500">Promociones temporales (mes festivo / premio): beneficio por un mes, con vigencia por fechas.</p>
            @endif
        </div>
        <x-ui.button variant="primary" icon="add_circle" wire:click="openModal">{{ $category === 'contract_rule' ? 'Agregar regla' : 'Agregar promoción' }}</x-ui.button>
    </div>

    @if($category === 'promotion')
        {{-- PROMOCIONES: agrupadas por nombre (campaña/evento) --}}
        @php $groups = $campaigns->groupBy('name'); @endphp
        @forelse($groups as $name => $groupItems)
            @php $first = $groupItems->first(); $activeCount = $groupItems->where('is_active', true)->count(); @endphp
            <div class="rounded-xl border border-gray-200 overflow-hidden mb-4" x-data="{ open: true }">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">campaign</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $groupItems->count() }} promo/s
                                @if($first->starts_at) · {{ $first->starts_at->format('d/m/Y') }} → {{ $first->ends_at?->format('d/m/Y') }} @endif
                                <span class="{{ $activeCount ? 'text-green-600' : 'text-gray-400' }}"> · {{ $activeCount ? '● Vigente' : '○ No vigente' }}</span>
                            </p>
                        </div>
                    </div>
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                </div>
                <div x-show="open" x-collapse>
                    <table class="w-full text-sm">
                        <thead class="bg-white text-gray-500">
                            <tr class="border-b border-gray-100">
                                <th class="text-left px-4 py-2 font-medium text-xs">Tipo</th>
                                <th class="text-left px-4 py-2 font-medium text-xs">Servicio</th>
                                <th class="text-left px-4 py-2 font-medium text-xs">Zona</th>
                                <th class="text-center px-4 py-2 font-medium text-xs">Activo</th>
                                <th class="text-right px-4 py-2 font-medium text-xs">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($groupItems as $campaign)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-4 py-2.5">
                                        <x-ui.badge variant="neutral">{{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</x-ui.badge>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if($campaign->service === 'all')
                                            <span class="text-gray-500">Todos</span>
                                        @else
                                            <x-ui.badge variant="info">{{ ucfirst(str_replace('_', ' ', $campaign->service)) }}</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700">{{ $campaign->zone?->name ?? 'Global' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <button wire:click="toggleActive({{ $campaign->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition
                                            {{ $campaign->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                            <span class="material-symbols-outlined text-sm">{{ $campaign->is_active ? 'check_circle' : 'cancel' }}</span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="openModal({{ $campaign->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                <span class="material-symbols-outlined text-sm">edit</span>
                                            </button>
                                            <button wire:click="promptDelete({{ $campaign->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-200 py-12 text-center text-gray-500">
                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">campaign</span>
                No hay promociones configuradas.
            </div>
        @endforelse
    @else
        {{-- REGLAS DE CONTRATO: tabla simple --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium">Nombre</th>
                        <th class="text-left px-4 py-3 font-medium">Tipo</th>
                        <th class="text-left px-4 py-3 font-medium">Servicio</th>
                        <th class="text-left px-4 py-3 font-medium">Zona</th>
                        <th class="text-center px-4 py-3 font-medium">Activo</th>
                        <th class="text-right px-4 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-4 py-3 text-gray-800 font-medium">{{ $campaign->name }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge variant="neutral">{{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                @if($campaign->service === 'all')
                                    <span class="text-gray-500">Todos</span>
                                @else
                                    <x-ui.badge variant="info">{{ ucfirst(str_replace('_', ' ', $campaign->service)) }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $campaign->zone?->name ?? 'Global' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $campaign->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition
                                    {{ $campaign->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    <span class="material-symbols-outlined text-sm">{{ $campaign->is_active ? 'check_circle' : 'cancel' }}</span>
                                    {{ $campaign->is_active ? 'Activa' : 'Inactiva' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openModal({{ $campaign->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <button wire:click="promptDelete({{ $campaign->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">campaign</span>
                                No hay reglas de contrato configuradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal agregar/editar campaña --}}
    @if($showModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600">campaign</span>
                        {{ $editingId ? 'Editar' : 'Nueva' }} {{ $category === 'contract_rule' ? 'regla de contrato' : 'promoción' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <x-ui.input type="text" wire:model="name" label="Nombre" icon="badge" placeholder="Mes del Padre" required />
                    <x-ui.select wire:model.live="type" label="Tipo" icon="tune">
                        @if($category === 'contract_rule')
                            <option value="discount_months">Meses gratis</option>
                            <option value="double_speed">Doble velocidad</option>
                        @else
                            <option value="free_installation">Instalación gratis</option>
                            <option value="free_tv_month">Mes de TV gratis</option>
                            <option value="free_internet_month">Mes de Internet gratis</option>
                            <option value="double_speed">Doble velocidad</option>
                        @endif
                    </x-ui.select>

                    {{-- Servicios a los que aplica la promo/regla --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 text-sm">tv</span>
                            Servicios
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @foreach([
                                'internet' => 'Internet',
                                'cable' => 'Cable',
                                'internet_cable' => 'Cable + Internet',
                            ] as $val => $label)
                                @if(in_array($val, $this->allowedServices($type)))
                                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition
                                        {{ in_array($val, $services) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                        <input type="checkbox" wire:model="services" value="{{ $val }}" class="accent-blue-600">
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Marcá uno o varios. Se creará una promo/regla por cada servicio marcado.</p>
                    </div>

                    @if($type === 'discount_months')
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.input type="number" wire:model="cfg_min_pay" label="Mínimo de meses a pagar" icon="calendar_month" min="1" placeholder="12" />
                            <x-ui.input type="number" wire:model="cfg_free" label="Meses regalados" icon="card_giftcard" min="0" placeholder="2" />
                        </div>
                        <p class="text-xs text-gray-400">Si el cliente paga el mínimo, se le regalan los meses indicados.</p>
                    @elseif($type === 'double_speed')
                        <div class="flex items-center gap-3 py-1">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="cfg_enabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-sm text-gray-600">Aplicar doble velocidad</span>
                        </div>
                    @endif
                    {{-- Zona (árbol jerárquico) --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Zona (vacío = global)</label>

                        @php $selectedZoneObj = $zone_id ? $allZones->firstWhere('id', $zone_id) : null; @endphp
                        @if($selectedZoneObj)
                            <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-3 py-2.5 mb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="material-symbols-outlined text-blue-500 text-sm flex-shrink-0">location_on</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-blue-800 truncate">{{ $selectedZoneObj->name }}</p>
                                        <p class="text-xs text-blue-500">
                                            {{ ucfirst($selectedZoneObj->level) }}
                                            @if($selectedZoneObj->branch) · {{ $selectedZoneObj->branch->name }} @endif
                                        </p>
                                    </div>
                                </div>
                                <button wire:click="clearZone" class="text-blue-400 hover:text-red-500 flex-shrink-0 ml-2" title="Quitar zona (global)">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2 bg-gray-50 border border-dashed border-gray-300 rounded-lg px-3 py-2.5 mb-2">
                                <span class="material-symbols-outlined text-gray-400 text-sm">public</span>
                                <span class="text-sm text-gray-500">Global (todas las zonas)</span>
                            </div>
                        @endif

                        <div class="relative mb-2">
                            <input type="text" wire:model.live="zoneSearch" placeholder="Buscar zona por nombre..."
                                class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                            @if($zoneSearch)
                            <button wire:click="$set('zoneSearch', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                            @endif
                        </div>

                        <div class="border border-gray-200 rounded-lg max-h-52 overflow-y-auto bg-white">
                            @if($zoneSearch)
                                @php
                                    $searchResults = $allZones->filter(fn($z) => stripos($z->name, $zoneSearch) !== false)->take(20);
                                @endphp
                                @forelse($searchResults as $zone)
                                    @php
                                        $ancestry = $this->zoneAncestry($zone->id);
                                        $isSelected = $zone_id === $zone->id;
                                    @endphp
                                    <button wire:click="selectZone({{ $zone->id }})"
                                        class="w-full text-left px-3 py-2.5 hover:bg-blue-50 transition flex items-center gap-2 {{ $isSelected ? 'bg-blue-50 border-l-2 border-blue-500' : '' }}">
                                        <span class="material-symbols-outlined text-sm flex-shrink-0 {{ $isSelected ? 'text-blue-600' : 'text-gray-400' }}">
                                            {{ $isSelected ? 'radio_button_checked' : 'radio_button_unchecked' }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm {{ $isSelected ? 'font-semibold text-blue-700' : 'text-gray-800' }} truncate">{{ $zone->name }}</p>
                                            <p class="text-xs text-gray-400 truncate">
                                                {{ ucfirst($zone->level) }}
                                                @if($zone->branch) · {{ $zone->branch->name }} @endif
                                                @if(count($ancestry) > 1)
                                                    · {{ implode(' > ', array_map(fn($a) => $a['name'], array_slice($ancestry, 0, -1))) }}
                                                @endif
                                            </p>
                                        </div>
                                    </button>
                                @empty
                                    <p class="text-xs text-gray-400 text-center py-4">Sin resultados para "{{ $zoneSearch }}"</p>
                                @endforelse
                            @else
                                @php
                                    $renderCampZone = function($zones, $depth = 0) use (&$renderCampZone, $ruleExpandedZones, $zone_id) {
                                        foreach ($zones as $zone):
                                            $hasChildren = $zone->children->count() > 0;
                                            $expanded = in_array($zone->id, $ruleExpandedZones);
                                            $selected = $zone_id === $zone->id;
                                @endphp
                                <div class="flex items-center gap-1 py-0.5 {{ $depth > 0 ? 'text-sm' : '' }}" style="padding-left: {{ $depth * 1.25 }}rem">
                                    @if($hasChildren)
                                    <button wire:click="ruleToggleExpand({{ $zone->id }})" class="w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-600 flex-shrink-0">
                                        <span class="material-symbols-outlined text-sm">{{ $expanded ? 'expand_more' : 'chevron_right' }}</span>
                                    </button>
                                    @else
                                    <span class="w-5 flex-shrink-0"></span>
                                    @endif
                                    <button wire:click="selectZone({{ $zone->id }})"
                                        class="flex items-center gap-1.5 min-w-0 text-left hover:text-blue-600 transition flex-1 {{ $selected ? 'text-blue-700 font-semibold' : 'text-gray-700' }}">
                                        <span class="material-symbols-outlined text-sm flex-shrink-0 {{ $selected ? 'text-blue-600' : 'text-gray-400' }}">
                                            {{ $selected ? 'radio_button_checked' : 'radio_button_unchecked' }}
                                        </span>
                                        <span class="truncate">{{ $zone->name }}</span>
                                        <span class="text-xs text-gray-400 flex-shrink-0">{{ ucfirst($zone->level) }}</span>
                                    </button>
                                </div>
                                @php
                                            if ($hasChildren && $expanded) {
                                                $renderCampZone($zone->children, $depth + 1);
                                            }
                                        endforeach;
                                    };
                                    $renderCampZone($rootZones);
                                @endphp
                                @if($allZones->isEmpty())
                                    <p class="text-xs text-gray-400 text-center py-4">No hay zonas disponibles.</p>
                                @endif
                            @endif
                        </div>

                        @if($zone_id)
                            <button wire:click="clearZone"
                                class="text-xs text-red-500 hover:text-red-700 mt-1 inline-block">Limpiar selección (usar todas las zonas)</button>
                        @endif
                    </div>
                    @if($category === 'promotion')
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.input type="date" wire:model="starts_at" label="Inicio" icon="calendar_month" />
                            <x-ui.input type="date" wire:model="ends_at" label="Fin" icon="event" />
                        </div>
                        <p class="text-xs text-gray-400">Promoción temporal: solo aplica durante estas fechas (ej. mes de independencia).</p>
                    @else
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                            <p class="text-xs text-blue-700 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                Regla de contrato permanente (aplica todo el contrato, sin fechas).
                            </p>
                        </div>
                    @endif
                    <div class="flex items-center gap-3 py-1">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm text-gray-600">Campaña activa</span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                    <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" icon="save" wire:click="save">Guardar campaña</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Confirmar eliminar --}}
    @if($confirmingAction === 'delete')
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
