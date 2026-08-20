<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700">Tarifas de instalación por zona</h3>
            <p class="text-xs text-gray-500">Cada zona define su propio cargo de instalación y el recargo por exceso de distancia. No depende del plan.</p>
        </div>
        <x-ui.button variant="primary" icon="add_circle" wire:click="openModal">Agregar tarifa</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left px-4 py-3 font-medium">Zona</th>
                    <th class="text-left px-4 py-3 font-medium">Servicio</th>
                    <th class="text-center px-4 py-3 font-medium">Metros cubiertos</th>
                    <th class="text-right px-4 py-3 font-medium">Cargo base</th>
                    <th class="text-right px-4 py-3 font-medium">Recargo x50m</th>
                    <th class="text-center px-4 py-3 font-medium">Activo</th>
                    <th class="text-right px-4 py-3 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rules as $rule)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-4 py-3 text-gray-800">{{ $rule->zone?->name ?? 'Global (todas)' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge variant="neutral">{{ ucfirst($rule->service_type) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $rule->covered_meters }} m</td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-gray-800">${{ number_format($rule->fee, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">${{ number_format($rule->excess_per_50m, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $rule->id }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition
                                {{ $rule->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                <span class="material-symbols-outlined text-sm">{{ $rule->is_active ? 'check_circle' : 'cancel' }}</span>
                                {{ $rule->is_active ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openModal({{ $rule->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button wire:click="promptDelete({{ $rule->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">handyman</span>
                            No hay tarifas de instalación configuradas. Agregá una para cada zona.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal agregar/editar tarifa --}}
    @if($showModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-xl">
            <div class="bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">handyman</span>
                        {{ $editingId ? 'Editar tarifa' : 'Nueva tarifa' }} de instalación
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    {{-- Zona --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Zona</label>

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
                                    $renderFeeZone = function($zones, $depth = 0) use (&$renderFeeZone, $ruleExpandedZones, $zone_id) {
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
                                                $renderFeeZone($zone->children, $depth + 1);
                                            }
                                        endforeach;
                                    };
                                    $renderFeeZone($rootZones);
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

                    {{-- Servicios a los que aplica la tarifa --}}
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
                                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition
                                    {{ in_array($val, $services) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                    <input type="checkbox" wire:model="services" value="{{ $val }}" class="accent-blue-600">
                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Marcá uno o varios. Se creará una tarifa por cada servicio marcado.</p>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <x-ui.input type="number" wire:model="covered_meters" label="Metros cubiertos" icon="straighten" min="1" placeholder="150" />
                        <x-ui.input type="number" wire:model="fee" label="Cargo base ($)" icon="attach_money" step="0.01" min="0" placeholder="25" />
                        <x-ui.input type="number" wire:model="excess_per_50m" label="Recargo x50m ($)" icon="local_atm" step="0.01" min="0" placeholder="5" />
                    </div>
                    <p class="text-xs text-gray-400">"Cargo base" cubre hasta los metros indicados. Si el cliente excede, se suma el recargo por cada 50m adicionales.</p>

                    <div class="flex items-center gap-3 py-1">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm text-gray-600">Tarifa activa</span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                    <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" icon="save" wire:click="save">Guardar tarifa</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL CONFIRMAR ELIMINAR --}}
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
