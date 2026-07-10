<div>
    <div class="max-w-7xl mx-auto">
        <x-ui.card icon="supervisor_account" title="Supervisores por Zona" subtitle="Asigná los supervisores de campo responsables de cada zona. Los supervisores se heredan automáticamente a las sub-zonas.">
            @forelse($rootZones as $zone)
                @include('livewire.admin.supervisor-zones._zone-tree-node', [
                    'zone' => $zone,
                    'level' => 0,
                ])
            @empty
                <div class="text-center py-12 text-gray-400">
                    <span class="material-symbols-outlined text-4xl">location_off</span>
                    <p class="mt-2 text-sm">No hay zonas registradas. Creálas en Admin → Planes y Zonas.</p>
                </div>
            @endforelse
        </x-ui.card>
    </div>

    {{-- Modal de asignación --}}
    @if($showAssignModal && $editingZone)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Asignar supervisores</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Zona: <span class="font-medium">{{ $editingZone->name }}</span>
                            <span class="text-gray-300 mx-1">·</span>
                            <span class="capitalize">{{ $editingZone->level }}</span>
                            @if($editingZone->parent)
                                <span class="text-gray-300 mx-1">·</span>
                                Hereda de: {{ $editingZone->parent->name }}
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supervisores disponibles</label>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($allSupervisors as $sup)
                                <label class="flex items-center gap-3 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                                    <input type="checkbox" value="{{ $sup->id }}"
                                        wire:model.live="selectedSupervisors"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $sup->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $sup->email }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if($editingZone->children->isNotEmpty())
                        <label class="flex items-center gap-3 px-3 py-2 rounded-lg border border-amber-200 bg-amber-50 cursor-pointer transition">
                            <input type="checkbox" wire:model.live="applyToChildren"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <div>
                                <p class="text-sm font-medium text-amber-800">Aplicar también a todas las sub-zonas</p>
                                <p class="text-xs text-amber-600">Esto sobrescribirá cualquier asignación existente en las zonas hijas.</p>
                            </div>
                        </label>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showAssignModal', false)"
                        class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="saveAssignments"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Guardar asignaciones
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>