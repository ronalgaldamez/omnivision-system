<div class="max-w-4xl mx-auto">
    <x-ui.card title="Actualizaciones del sistema" icon="new_releases" subtitle="Gestioná las novedades que se muestran a los usuarios">
        <x-slot:headerActions>
            <x-ui.button variant="secondary" size="sm" icon="terminal" wire:click="importFromGit">Importar desde Git</x-ui.button>
            <x-ui.button variant="primary" size="sm" icon="add" wire:click="openCreate">Nueva actualización</x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-2">
            @forelse($entries as $entry)
                <div class="flex items-start justify-between gap-3 p-4 border border-gray-200 rounded-xl {{ $entry->published_at ? 'bg-white' : 'bg-amber-50/40 border-amber-200' }}">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            @if($entry->version)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 font-mono">{{ $entry->version }}</span>
                            @endif
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $entry->title }}</p>
                            @if(!$entry->published_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700">Borrador</span>
                            @endif
                        </div>
                        @if($entry->description)
                            <p class="text-xs text-gray-600 mt-1 whitespace-pre-line">{{ $entry->description }}</p>
                        @endif
                        <p class="text-[10px] text-gray-400 mt-2">
                            {{ $entry->published_at ? 'Publicada: ' . $entry->published_at->format('d/m/Y H:i') : 'Sin publicar' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button wire:click="edit({{ $entry->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </button>
                        <button wire:click="delete({{ $entry->id }})" onclick="return confirm('¿Eliminar esta actualización?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-gray-300 text-5xl mb-3 block">new_releases</span>
                    <p class="text-gray-500 font-medium">Sin actualizaciones registradas</p>
                    <p class="text-sm text-gray-400 mt-1">Creá una actualización o importá los últimos commits de Git</p>
                </div>
            @endforelse
        </div>
    </x-ui.card>

    {{-- Modal crear/editar --}}
    @if($showModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="relative w-full max-w-lg">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $editingId ? 'Editar' : 'Nueva' }} actualización</h3>
                        <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <x-forms.group name="version" label="Versión (opcional)">
                            <x-ui.input type="text" wire:model="version" placeholder="Ej: v2.1.0" />
                        </x-forms.group>
                        <x-forms.group name="title" label="Título *">
                            <x-ui.input type="text" wire:model="title" placeholder="Ej: Panel de Bodega renovado" />
                        </x-forms.group>
                        <x-forms.group name="description" label="Descripción">
                            <x-ui.textarea wire:model="description" rows="3" placeholder="Detalle de lo que cambió..." />
                        </x-forms.group>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="publishNow" class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500/30">
                            <span class="text-xs text-gray-600">Publicar ahora (si no, queda como borrador)</span>
                        </label>
                    </div>
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                        <x-ui.button variant="secondary" wire:click="$set('showModal', false)">Cancelar</x-ui.button>
                        <x-ui.button variant="primary" icon="save" wire:click="save">Guardar</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
