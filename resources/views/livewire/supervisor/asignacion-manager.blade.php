<div class="max-w-6xl mx-auto">
    <x-ui.card icon="assignment_ind" title="Asignaciones" subtitle="Asigná vehículos y zonas a los encargados.">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add" wire:click="openForm">Nueva Asignación</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-xs">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por encargado o placa..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <span class="text-xs text-gray-400">{{ $asignaciones->total() }} asignación(es)</span>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Encargado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Auxiliar</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Vehículo</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Zona</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Desde</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($asignaciones as $a)
                        <tr class="hover:bg-gray-50/80 transition {{ !$a->is_active ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    <span class="font-medium text-gray-800">{{ $a->encargado->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <select wire:change="assignAuxiliar({{ $a->id }}, $event.target.value)"
                                    class="text-xs rounded border-gray-300 w-32 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    <option value="">— Sin auxiliar —</option>
                                    @foreach($tecnicos->where('id', '!=', $a->encargado_id) as $t)
                                    <option value="{{ $t->id }}" @selected($a->auxiliar_id == $t->id)>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($a->vehicle)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 rounded-md text-xs">
                                    <span class="material-symbols-outlined text-gray-500 text-sm">directions_car</span>
                                    <span class="font-mono text-gray-700">{{ $a->vehicle->placa }}</span>
                                    <span class="text-gray-400">·</span>
                                    <span class="text-gray-500">{{ $a->vehicle->marca }}</span>
                                </div>
                                @else
                                <span class="text-gray-400 italic">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $a->zone->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($a->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    Activa
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-50 text-gray-500 rounded-full text-xs font-medium">
                                    Finalizada
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $a->assigned_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openForm({{ $a->id }})" title="Editar"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    @if($a->is_active)
                                    <button wire:click="deactivate({{ $a->id }})" title="Finalizar"
                                        wire:confirm="¿Finalizar esta asignación?"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <span class="material-symbols-outlined text-sm">block</span>
                                    </button>
                                    @else
                                    <button wire:click="reactivate({{ $a->id }})" title="Reactivar"
                                        class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                        <span class="material-symbols-outlined text-sm">refresh</span>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">assignment</span>
                                <p class="text-gray-500">No hay asignaciones</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($asignaciones->hasPages())
            <div class="pt-2">{{ $asignaciones->links() }}</div>
            @endif
        </div>
    </x-ui.card>

    <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 {{ $showForm ? '' : 'hidden' }}">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800">{{ $editingId ? 'Editar' : 'Nueva' }} Asignación</h2>
                <button wire:click="$set('showForm', false)" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Encargado *</label>
                        <select wire:model="encargado_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach($encargados as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        @error('encargado_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vehículo *</label>
                        <select wire:model="vehicle_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach($vehiculos as $v)
                            <option value="{{ $v->id }}">{{ $v->placa }} — {{ $v->marca }} {{ $v->modelo }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Zona</label>
                    @if($selectedZoneName)
                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                        <span class="material-symbols-outlined text-sm text-blue-500">location_on</span>
                        <span class="text-sm font-medium text-blue-700">{{ $selectedZoneName }}</span>
                        <button type="button" wire:click="removeZone" class="ml-auto text-blue-400 hover:text-blue-600">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    @else
                    <div class="text-sm text-gray-400 italic mb-2">Seleccioná una zona en el árbol</div>
                    @endif
                    <div class="mt-2 border border-gray-200 rounded-lg max-h-48 overflow-y-auto p-2 bg-gray-50/50">
                        <div class="relative mb-2">
                            <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
                            <input type="text" wire:model.live="zoneSearch" placeholder="Buscar zona..."
                                class="w-full pl-7 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        @forelse($branches as $branch)
                            @php $branchRoots = $rootZones->where('branch_id', $branch->id); @endphp
                            @if($branchRoots->count() > 0)
                            <div class="mb-2 last:mb-0">
                                <div class="px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded mb-1">{{ $branch->name }}</div>
                                @foreach($branchRoots as $rootZone)
                                    @include('livewire.supervisor._zone-tree', [
                                        'zone' => $rootZone,
                                        'depth' => 0,
                                        'allZones' => $allZones,
                                        'selectedZoneId' => $zone_id,
                                    ])
                                @endforeach
                            </div>
                            @endif
                        @empty
                        <p class="text-xs text-gray-400 text-center py-3">No hay sucursales activas</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Asignado desde</label>
                    <input type="date" wire:model="assigned_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="$set('showForm', false)"
                        class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        {{ $editingId ? 'Guardar cambios' : 'Crear asignación' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
