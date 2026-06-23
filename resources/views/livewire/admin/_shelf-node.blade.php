<div class="border border-gray-200 rounded-lg {{ $level > 0 ? 'ml-6 mt-2' : '' }} {{ $shelf->is_active ? '' : 'opacity-60' }}">
    <div class="flex items-center justify-between px-4 py-3 {{ $level > 0 ? 'bg-gray-50/50' : 'bg-white' }}">
        <div class="flex items-center gap-3 min-w-0">
            <span class="material-symbols-outlined {{ $shelf->is_full ? 'text-red-400' : 'text-gray-400' }} text-lg">
                @switch($shelf->type)
                    @case('rack') shelves @break
                    @case('shelf') view_list @break
                    @case('bin') inventory_2 @break
                    @case('container') box @break
                    @case('drawer') stacks @break
                    @default shelf @break
                @endswitch
            </span>
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                    <span class="text-blue-600 font-mono">{{ $shelf->code }}</span>
                    — {{ $shelf->label }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ ucfirst($shelf->type) }}
                    @if($shelf->description) · {{ Str::limit($shelf->description, 60) }} @endif
                    @if($shelf->warehouse) · {{ $shelf->warehouse }} @endif
                    @if($shelf->is_full) · <span class="text-red-500 font-medium">Lleno</span> @endif
                    @if(!$shelf->is_active) · <span class="text-red-500">Inactivo</span> @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
            <button type="button" wire:click="toggleInlineForm({{ $shelf->id }})"
                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                title="Agregar contenedor">
                <span class="material-symbols-outlined text-base">add</span>
            </button>
            <button type="button" wire:click="toggleFull({{ $shelf->id }})"
                class="p-1.5 {{ $shelf->is_full ? 'text-red-500 hover:bg-red-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100' }} rounded-lg transition"
                title="{{ $shelf->is_full ? 'Marcar disponible' : 'Marcar como lleno' }}">
                <span class="material-symbols-outlined text-base">inventory_2</span>
            </button>
            <button type="button" wire:click="openEdit({{ $shelf->id }})"
                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"
                title="Editar">
                <span class="material-symbols-outlined text-base">edit</span>
            </button>
            <button type="button" wire:click="promptDelete({{ $shelf->id }})"
                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                title="Eliminar">
                <span class="material-symbols-outlined text-base">delete</span>
            </button>
        </div>
    </div>

    {{-- Inline quick-create form (solo para hijos) --}}
    @if($showInlineForm === $shelf->id)
        <div class="border-t border-blue-100 bg-blue-50/50 px-4 py-3">
            <form wire:submit="quickCreate" class="flex items-end gap-3">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código</label>
                    <input type="text" wire:model="quickCode"
                        class="w-full py-1.5 px-2.5 rounded-lg border border-gray-300 bg-white text-sm font-mono"
                        placeholder="Auto">
                </div>
                <div class="flex-[2] min-w-0">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Etiqueta</label>
                    <input type="text" wire:model="quickLabel"
                        class="w-full py-1.5 px-2.5 rounded-lg border border-gray-300 bg-white text-sm"
                        placeholder="Nombre del contenedor">
                    @error('quickLabel') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                    <select wire:model="quickType"
                        class="w-full py-1.5 px-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                        <option value="shelf">Bandeja</option>
                        <option value="bin">Caja / Bin</option>
                        <option value="container">Contenedor</option>
                        <option value="drawer">Gaveta</option>
                    </select>
                </div>
                <button type="submit"
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">
                    Crear
                </button>
                <button type="button" wire:click="$set('showInlineForm', null)"
                    class="px-2 py-1.5 text-gray-400 hover:text-gray-600 transition">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </form>
        </div>
    @endif
</div>

@if($shelf->children->isNotEmpty())
    @foreach($shelf->children as $child)
        @include('livewire.admin._shelf-node', ['shelf' => $child, 'level' => $level + 1, 'showInlineForm' => $showInlineForm])
    @endforeach
@endif
