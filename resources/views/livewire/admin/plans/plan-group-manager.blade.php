<div>
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
                @php $groupPlans = $group->plans->take(5); @endphp
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
