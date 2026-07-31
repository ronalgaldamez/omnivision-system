<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Reglas de Planes</h1>
            <p class="text-sm text-gray-500 mt-1">Definí beneficios y restricciones por plan, zona y plazo.</p>
        </div>
    </div>

    <x-ui.card>
        {{-- Selector de plan --}}
        <div class="mb-6">
            <x-ui.select wire:model.live="selectedPlanId" label="Seleccionar plan" icon="assignment">
                <option value="">-- Seleccionar plan --</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }} ({{ str_replace('_', ' ', $plan->service_type) }})</option>
                @endforeach
            </x-ui.select>
        </div>

        @if($selectedPlanId)
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Reglas configuradas</h3>
                <x-ui.button variant="primary" icon="add_circle" wire:click="openRuleModal" class="text-xs">
                    Agregar regla
                </x-ui.button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-3 py-2">Zona</th>
                            <th class="px-3 py-2">Plazo</th>
                            <th class="px-3 py-2">Regla</th>
                            <th class="px-3 py-2">Valor</th>
                            <th class="px-3 py-2">Condición</th>
                            <th class="px-3 py-2">Activo</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($planRules as $rule)
                            <tr class="border-t border-gray-100 hover:bg-gray-50">
                                <td class="px-3 py-2.5">{{ $rule->zone?->name ?? 'Todas' }}</td>
                                <td class="px-3 py-2.5">{{ $rule->term_months }} meses</td>
                                <td class="px-3 py-2.5">{{ $this->ruleKeyOptions()[$rule->rule_key] ?? $rule->rule_key }}</td>
                                <td class="px-3 py-2.5 font-mono text-xs">
                                    {{ is_array($rule->rule_value) ? ($rule->rule_value['value'] ?? json_encode($rule->rule_value)) : $rule->rule_value }}
                                </td>
                                <td class="px-3 py-2.5">{{ $this->conditionOptions()[$rule->condition ?? ''] ?? '' }}</td>
                                <td class="px-3 py-2.5">
                                    <button wire:click="toggleRule({{ $rule->id }})" class="text-lg">
                                        {{ $rule->is_active ? '✅' : '⛔' }}
                                    </button>
                                </td>
                                <td class="px-3 py-2.5 flex gap-1">
                                    <button wire:click="openRuleModal({{ $rule->id }})" class="text-blue-600 hover:underline text-xs">Editar</button>
                                    <button wire:click="deleteRule({{ $rule->id }})" onclick="return confirm('¿Eliminar regla?')" class="text-red-600 hover:underline text-xs">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-400">No hay reglas para este plan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">{{ $editingRuleId ? 'Editar' : 'Nueva' }} regla</h3>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.select wire:model="ruleZoneId" label="Zona" icon="map">
                        <option value="">Todas las zonas</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                            <option value="{{ $z->id }}">{{ $z->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input type="number" wire:model="ruleTermMonths" label="Plazo (meses)" icon="calendar_month" min="1" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.select wire:model="ruleKey" label="Tipo de regla" icon="tune">
                        <option value="">Seleccionar</option>
                        @foreach($this->ruleKeyOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model="ruleCondition" label="Condición" icon="policy">
                        @foreach($this->conditionOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.input type="text" wire:model="ruleValue" label="Valor" icon="data_object"
                    placeholder='Ej: {"meters":150} o "true" o "3"' />
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="ruleActive" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="text-sm text-gray-700">Regla activa</span>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">Cancelar</button>
                <button wire:click="saveRule" class="px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
