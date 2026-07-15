<div class="max-w-6xl mx-auto">
    <x-ui.card icon="directions_car" title="Vehículos" subtitle="Registro de vehículos asignables a encargados.">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add" wire:click="openForm">Nuevo Vehículo</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-xs">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por placa, marca..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <span class="text-xs text-gray-400">{{ $vehiculos->total() }} vehículo(s)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($vehiculos as $v)
                <div class="rounded-xl border border-gray-200 overflow-hidden hover:shadow-sm transition {{ $v->estado !== 'activo' ? 'opacity-60' : '' }}">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-400">directions_car</span>
                            <span class="font-mono font-bold text-gray-800">{{ $v->placa }}</span>
                            @php
                                $badgeClass = match($v->estado) {
                                    'activo' => 'bg-green-50 text-green-700',
                                    'averiado' => 'bg-red-50 text-red-700',
                                    'mantenimiento' => 'bg-amber-50 text-amber-700',
                                    'baja' => 'bg-gray-100 text-gray-500',
                                    default => 'bg-gray-50 text-gray-600',
                                };
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeClass }}">{{ ucfirst($v->estado) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="openForm({{ $v->id }})" title="Editar"
                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                        </div>
                    </div>
                    <div class="px-4 py-3 grid grid-cols-2 gap-2 text-xs">
                        <div><span class="text-gray-400">Marca:</span> <span class="text-gray-700 font-medium">{{ $v->marca }}</span></div>
                        <div><span class="text-gray-400">Modelo:</span> <span class="text-gray-700 font-medium">{{ $v->modelo }}</span></div>
                        @if($v->anio)<div><span class="text-gray-400">Año:</span> <span class="text-gray-700">{{ $v->anio }}</span></div>@endif
                        @if($v->color)<div><span class="text-gray-400">Color:</span> <span class="text-gray-700">{{ $v->color }}</span></div>@endif
                        @if($v->tipo)<div><span class="text-gray-400">Tipo:</span> <span class="text-gray-700">{{ $v->tipo }}</span></div>@endif
                        @if($v->encargadoActual?->encargado)
                        <div class="col-span-2 mt-1 pt-2 border-t border-gray-100">
                            <span class="text-gray-400">Asignado a:</span>
                            <span class="text-blue-700 font-medium">{{ $v->encargadoActual->encargado->name }}</span>
                        </div>
                        @endif
                    </div>
                    @if($v->notas)
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 text-xs text-gray-500">{{ $v->notas }}</div>
                    @endif
                </div>
                @empty
                <div class="col-span-2 text-center py-12 text-gray-500 bg-gray-50/50 rounded-xl border border-gray-200">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">directions_car_off</span>
                    <p>No hay vehículos registrados</p>
                </div>
                @endforelse
            </div>

            @if($vehiculos->hasPages())
            <div class="pt-2">{{ $vehiculos->links() }}</div>
            @endif
        </div>
    </x-ui.card>

    <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 {{ $showForm ? '' : 'hidden' }}">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800">{{ $editingId ? 'Editar' : 'Nuevo' }} Vehículo</h2>
                <button wire:click="$set('showForm', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Placa *</label>
                        <input type="text" wire:model="placa" placeholder="ABC-123" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('placa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Marca *</label>
                        <input type="text" wire:model="marca" placeholder="Toyota" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('marca') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Modelo *</label>
                        <input type="text" wire:model="modelo" placeholder="Hilux" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('modelo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Año</label>
                        <input type="number" wire:model="anio" placeholder="2024" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('anio') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                        <input type="text" wire:model="color" placeholder="Blanco" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('color') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                        <input type="text" wire:model="tipo" placeholder="Pickup" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('tipo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select wire:model="estado" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="activo">Activo</option>
                            <option value="averiado">Averiado</option>
                            <option value="mantenimiento">Mantenimiento</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                    <textarea wire:model="notas" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    @error('notas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="$set('showForm', false)"
                        class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        {{ $editingId ? 'Guardar cambios' : 'Registrar vehículo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
