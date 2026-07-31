<div>
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
                        <label class="text-xs text-gray-500 mb-2 block"><span class="material-symbols-outlined text-xs align-text-bottom">trending_up</span> Historial de cambios de precio base</label>
                        <div class="space-y-1.5">
                            @foreach($viewingPlanHistories as $vh)
                            <div class="flex items-center justify-between px-3 py-1.5 bg-gray-50 rounded-lg text-xs">
                                <div>
                                    @if($vh->old_price !== null) <span class="text-gray-500">${{ number_format($vh->old_price, 2) }}</span> <span class="text-gray-300 mx-1">→</span> @endif
                                    <span class="font-semibold {{ $vh->new_price > $vh->old_price ? 'text-red-600' : ($vh->new_price < $vh->old_price ? 'text-blue-600' : 'text-gray-700') }}">${{ number_format($vh->new_price, 2) }}</span>
                                    @if($vh->old_price !== null && $vh->new_price !== null)
                                        @if($vh->new_price > $vh->old_price) <span class="material-symbols-outlined text-sm text-red-500 align-text-bottom">arrow_upward</span> @elseif($vh->new_price < $vh->old_price) <span class="material-symbols-outlined text-sm text-blue-500 align-text-bottom">arrow_downward</span> @endif
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
