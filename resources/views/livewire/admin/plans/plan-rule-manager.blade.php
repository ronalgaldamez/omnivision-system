<div>
    {{-- Selector de plan tipo modal --}}
    <div>
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Seleccionar plan</label>

        @if($rulePlanId)
            @php $selectedPlan = $allPlans->firstWhere('id', $rulePlanId); @endphp
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 flex-1 max-w-sm">
                    <span class="material-symbols-outlined text-blue-500 text-sm flex-shrink-0">subscriptions</span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-blue-800 truncate">{{ $selectedPlan?->name ?? 'Plan #'.$rulePlanId }}</p>
                        <p class="text-xs text-blue-500">{{ str_replace('_', ' + ', ucfirst($selectedPlan?->service_type ?? '')) }}</p>
                    </div>
                </div>
                <button wire:click="clearRulePlan" class="text-gray-400 hover:text-red-500 flex-shrink-0" title="Quitar plan">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
                <button wire:click="openRulePlanModal"
                    class="inline-flex items-center gap-1 px-3 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                    title="Ver todos los planes">
                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                    <span class="hidden sm:inline">Cambiar</span>
                </button>
            </div>
        @else
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 bg-gray-50 border border-dashed border-gray-300 rounded-lg px-3 py-2 flex-1 max-w-sm">
                    <span class="material-symbols-outlined text-gray-400 text-sm">subscriptions</span>
                    <span class="text-sm text-gray-500">Ningún plan seleccionado</span>
                </div>
                <button wire:click="openRulePlanModal"
                    class="inline-flex items-center gap-1 px-3 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                    title="Ver todos los planes">
                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                    <span class="hidden sm:inline">Ver todos</span>
                </button>
            </div>
        @endif
    </div>

    @if($rulePlanId)
    <div class="flex items-center justify-between mt-6">
        <h3 class="text-sm font-semibold text-gray-700">Reglas configuradas</h3>
        <x-ui.button variant="primary" icon="add_circle" wire:click="openRuleModal">Agregar regla</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 mt-3">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left px-4 py-3 font-medium">Zona</th>
                    <th class="text-left px-4 py-3 font-medium">Plazo</th>
                    <th class="text-left px-4 py-3 font-medium">Regla</th>
                    <th class="text-left px-4 py-3 font-medium">Valor</th>
                    <th class="text-left px-4 py-3 font-medium">Condición</th>
                    <th class="text-center px-4 py-3 font-medium">Activo</th>
                    <th class="text-right px-4 py-3 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($planRules as $rule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span>{{ $rule->zone?->name ?? 'Todas las zonas' }}</span>
                                @if($rule->zone)
                                    <span class="text-xs text-gray-400">{{ ucfirst($rule->zone->level) }}</span>
                                @endif
                            </div>
                            @if($rule->inherited_by_count > 0)
                                <div class="mt-0.5">
                                    <x-ui.badge variant="info"><span class="material-symbols-outlined text-xs align-text-bottom">arrow_downward</span> +{{ $rule->inherited_by_count }} {{ $rule->inherited_by_count === 1 ? 'zona hija' : 'zonas hijas' }}</x-ui.badge>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $rule->term_months }} meses</td>
                        <td class="px-4 py-3">{{ $this->ruleKeyOptions()[$rule->rule_key] ?? $rule->rule_key }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ is_array($rule->rule_value) ? (implode(', ', array_filter($rule->rule_value, fn($v) => is_scalar($v)))) : $rule->rule_value }}</td>
                        <td class="px-4 py-3">{{ $this->conditionOptions()[$rule->condition ?? ''] ?? '' }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleRule({{ $rule->id }})">
                                <x-ui.badge variant="{{ $rule->is_active ? 'success' : 'neutral' }}">{{ $rule->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openRuleModal({{ $rule->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <button wire:click="promptDeleteRule({{ $rule->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="Eliminar">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No hay reglas configuradas para este plan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- MODAL LISTA DE PLANES --}}
    @if($showRulePlanModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">subscriptions</span>
                        Seleccionar Plan
                    </h3>
                    <button wire:click="closeRulePlanModal" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-4 space-y-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="rulePlanListSearch"
                            placeholder="Filtrar por nombre..."
                            class="w-full pl-9 pr-10 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                        @if($rulePlanListSearch)
                        <button wire:click="$set('rulePlanListSearch', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <button wire:click="$set('rulePlanFilterType', '')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $rulePlanFilterType === '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Todos</button>
                        <button wire:click="$set('rulePlanFilterType', 'internet')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $rulePlanFilterType === 'internet' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Internet</button>
                        <button wire:click="$set('rulePlanFilterType', 'cable')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $rulePlanFilterType === 'cable' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Cable</button>
                        <button wire:click="$set('rulePlanFilterType', 'internet_cable')" class="px-2.5 py-1 text-xs font-medium rounded-lg transition {{ $rulePlanFilterType === 'internet_cable' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Internet + Cable</button>
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($rulePlanList as $plan)
                        <button type="button" wire:click="selectRulePlan({{ $plan->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-lg transition flex items-center gap-3 {{ $plan->id === $rulePlanId ? 'bg-blue-50 border border-blue-200' : '' }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $plan->name }}</span>
                                    @php
                                        $badgeV = match($plan->service_type) {
                                            'internet' => 'info',
                                            'cable' => 'warning',
                                            'internet_cable' => 'success',
                                            default => 'neutral'
                                        };
                                    @endphp
                                    <x-ui.badge variant="{{ $badgeV }}">{{ str_replace('_', ' + ', ucfirst($plan->service_type)) }}</x-ui.badge>
                                </div>
                                <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                                    @if($plan->speed)<span>{{ $plan->speed }}</span>@endif
                                    @if($plan->channels)<span>{{ $plan->channels }} canales</span>@endif
                                    @if($plan->rules_count > 0)
                                        <x-ui.badge variant="success">{{ $plan->rules_count }} {{ $plan->rules_count === 1 ? 'regla' : 'reglas' }}</x-ui.badge>
                                    @endif
                                    <span class="ml-auto font-medium text-gray-500">${{ number_format($plan->base_price, 2) }}</span>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="px-4 py-12 text-center text-sm text-gray-400">
                            <span class="material-symbols-outlined text-gray-300 text-3xl mb-2">search_off</span>
                            <p>No se encontraron planes{{ $rulePlanListSearch ? ' para "'.$rulePlanListSearch.'"' : '' }}.</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                    <x-ui.button variant="ghost" wire:click="closeRulePlanModal">Cancelar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL REGLA --}}
    @if($showRuleModal)
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 w-full max-w-xl">
            <div class="bg-white rounded-2xl shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">tune</span>
                        {{ $editingRuleId ? 'Editar regla' : 'Nueva regla' }}
                    </h3>
                    <button wire:click="$set('showRuleModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Zona</label>

                        @php $selectedZoneObj = $ruleZoneId ? $allZones->firstWhere('id', $ruleZoneId) : null; @endphp
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
                                <button wire:click="$set('ruleZoneId', null)" class="text-blue-400 hover:text-red-500 flex-shrink-0 ml-2" title="Quitar zona">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2 bg-gray-50 border border-dashed border-gray-300 rounded-lg px-3 py-2.5 mb-2">
                                <span class="material-symbols-outlined text-gray-400 text-sm">public</span>
                                <span class="text-sm text-gray-500">Todas las zonas (regla global)</span>
                            </div>
                        @endif

                        <div class="relative mb-2">
                            <input type="text" wire:model.live="ruleZoneSearch" placeholder="Buscar zona por nombre..."
                                class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                            @if($ruleZoneSearch)
                            <button wire:click="$set('ruleZoneSearch', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                            @endif
                        </div>

                        <div class="border border-gray-200 rounded-lg max-h-52 overflow-y-auto bg-white">
                            @if($ruleZoneSearch)
                                @php
                                    $searchResults = $allZones->filter(fn($z) => stripos($z->name, $ruleZoneSearch) !== false)->take(20);
                                @endphp
                                @forelse($searchResults as $zone)
                                    @php
                                        $ancestry = $this->zoneAncestry($zone->id);
                                        $isSelected = $ruleZoneId === $zone->id;
                                    @endphp
                                    <button wire:click="ruleSelectZone({{ $zone->id }})"
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
                                    <p class="text-xs text-gray-400 text-center py-4">Sin resultados para "{{ $ruleZoneSearch }}"</p>
                                @endforelse
                            @else
                                @php
                                    $renderRuleZone = function($zones, $depth = 0) use (&$renderRuleZone, $ruleExpandedZones, $ruleZoneId) {
                                        foreach ($zones as $zone):
                                            $hasChildren = $zone->children->count() > 0;
                                            $expanded = in_array($zone->id, $ruleExpandedZones);
                                            $selected = $ruleZoneId === $zone->id;
                                @endphp
                                <div class="flex items-center gap-1 py-0.5 {{ $depth > 0 ? 'text-sm' : '' }}" style="padding-left: {{ $depth * 1.25 }}rem">
                                    @if($hasChildren)
                                    <button wire:click="ruleToggleExpand({{ $zone->id }})" class="w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-600 flex-shrink-0">
                                        <span class="material-symbols-outlined text-sm">{{ $expanded ? 'expand_more' : 'chevron_right' }}</span>
                                    </button>
                                    @else
                                    <span class="w-5 flex-shrink-0"></span>
                                    @endif
                                    <button wire:click="ruleSelectZone({{ $zone->id }})"
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
                                                $renderRuleZone($zone->children, $depth + 1);
                                            }
                                        endforeach;
                                    };
                                    $renderRuleZone($rootZones);
                                @endphp
                                @if($allZones->isEmpty())
                                    <p class="text-xs text-gray-400 text-center py-4">No hay zonas disponibles.</p>
                                @endif
                            @endif
                        </div>

                        @if($ruleZoneId)
                            <button wire:click="$set('ruleZoneId', null)"
                                class="text-xs text-red-500 hover:text-red-700 mt-1 inline-block">Limpiar selección (usar todas las zonas)</button>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.input type="number" wire:model="ruleTermMonths" label="Plazo (meses)" icon="calendar_month" min="1" />
                        <x-ui.select wire:model="ruleKey" label="Tipo de regla" icon="tune">
                            <option value="">Seleccionar</option>
                            @foreach($this->ruleKeyOptions() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <x-ui.input type="text" wire:model="ruleValue" label="Valor" icon="data_object"
                        placeholder='{"meters":150} o "5"' />
                    <x-ui.select wire:model="ruleCondition" label="Condición" icon="policy">
                        @foreach($this->conditionOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <div class="flex items-center gap-3 py-1">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="ruleActive" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm text-gray-600">Regla activa</span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                    <x-ui.button variant="secondary" wire:click="$set('showRuleModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" icon="save" wire:click="saveRule">Guardar</x-ui.button>
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
