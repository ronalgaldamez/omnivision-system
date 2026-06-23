<div>
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">shelves</span>
                        Gestión de Estanterías
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Organizá los espacios físicos donde se almacenan los productos.</p>
                </div>
                <button type="button" wire:click="openCreate"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">add</span>
                    Nueva estantería
                </button>
            </div>

            {{-- Tabs: Árbol / Visual --}}
            <div class="border-b border-gray-200 px-6">
                <div class="flex gap-6 -mb-px">
                    <button type="button" wire:click="setViewMode('tree')"
                        class="pb-3 pt-4 text-sm font-medium border-b-2 transition {{ $viewMode === 'tree' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-base align-middle mr-1.5">account_tree</span>
                        Árbol
                    </button>
                    <button type="button" wire:click="setViewMode('visual')"
                        class="pb-3 pt-4 text-sm font-medium border-b-2 transition {{ $viewMode === 'visual' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-base align-middle mr-1.5">grid_view</span>
                        Visual (Pared)
                    </button>
                </div>
            </div>

            <div class="p-6">
                {{-- ========== MODO ÁRBOL ========== --}}
                @if($viewMode === 'tree')
                    @forelse($shelves as $shelf)
                    <div class="mb-4">
                        @include('livewire.admin._shelf-node', ['shelf' => $shelf, 'level' => 0, 'showInlineForm' => $showInlineForm])
                    </div>
                    @empty
                    <div class="text-center py-12 text-gray-400">
                        <span class="material-symbols-outlined text-4xl">shelves</span>
                        <p class="mt-2 text-sm">No hay estanterías registradas. Creá la primera.</p>
                    </div>
                    @endforelse

                {{-- ========== MODO VISUAL (PARED/COLUMNAS) ========== --}}
                @else
                    @if($racks->isNotEmpty())
                    <div class="flex gap-4 overflow-x-auto pb-4" style="scroll-snap-type: x mandatory;">
                        @foreach($racks as $rack)
                        <div class="flex-shrink-0 bg-gray-50 rounded-xl border border-gray-200 overflow-hidden"
                            style="width: 240px; scroll-snap-align: start;">
                            {{-- Rack header --}}
                            <div class="bg-white border-b border-gray-200 px-3 py-2.5 flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-500 text-lg">shelves</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 font-mono truncate">{{ $rack->code }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $rack->label }}</p>
                                </div>
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ $rack->children->count() }}</span>
                            </div>

                            {{-- Containers list (Sortable) --}}
                            <div class="p-2 space-y-2 min-h-[120px]" data-sortable-rack="{{ $rack->id }}">
                                @forelse($rack->children as $container)
                                @php
                                    $totalProducts = $container->products->sum('pivot.quantity');
                                    $productCount = $container->products->count();
                                    $isEmpty = $totalProducts === 0;
                                    $isFull = $container->is_full;
                                    if ($isFull) {
                                        $borderColor = 'border-red-300';
                                        $bgColor = 'bg-red-50';
                                    } elseif ($isEmpty) {
                                        $borderColor = 'border-gray-200';
                                        $bgColor = 'bg-white';
                                    } elseif ($totalProducts < 10) {
                                        $borderColor = 'border-amber-200';
                                        $bgColor = 'bg-amber-50';
                                    } else {
                                        $borderColor = 'border-emerald-200';
                                        $bgColor = 'bg-emerald-50';
                                    }
                                @endphp
                                <div class="rounded-lg border-2 {{ $borderColor }} {{ $bgColor }} p-2.5 hover:shadow-md transition-shadow cursor-grab active:cursor-grabbing {{ $isFull ? 'opacity-80' : '' }}"
                                    data-container-id="{{ $container->id }}">
                                    <div class="flex items-start justify-between mb-1.5">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-gray-900 truncate">
                                                <span class="font-mono text-blue-600">{{ $container->code }}</span>
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">{{ $container->label }}</p>
                                        </div>
                                        @if($isFull)
                                        <span class="text-xs font-medium px-1.5 py-0.5 rounded-full flex-shrink-0 ml-1 bg-red-100 text-red-600">
                                            Lleno
                                        </span>
                                        @else
                                        <span class="text-xs font-medium px-1.5 py-0.5 rounded-full flex-shrink-0 ml-1 {{ $isEmpty ? 'bg-gray-100 text-gray-500' : ($totalProducts < 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            {{ $totalProducts }}
                                        </span>
                                        @endif
                                    </div>

                                    @if($productCount > 0)
                                    <div class="space-y-0.5 mt-1.5 pt-1.5 border-t border-gray-200/60">
                                        @foreach($container->products->take(3) as $product)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-600 truncate mr-1">{{ $product->name }}</span>
                                            <span class="text-gray-400 font-mono flex-shrink-0">x{{ $product->pivot->quantity }}</span>
                                        </div>
                                        @endforeach
                                        @if($productCount > 3)
                                        <p class="text-xs text-gray-400 pt-0.5">+{{ $productCount - 3 }} más</p>
                                        @endif
                                    </div>
                                    @else
                                    <p class="text-xs text-gray-400 mt-1 italic">Vacío</p>
                                    @endif

                                    <div class="flex items-center gap-1 mt-1.5 pt-1.5 border-t border-gray-200/60">
                                        <button type="button" wire:click="toggleFull({{ $container->id }})"
                                            class="p-1 {{ $container->is_full ? 'text-red-500' : 'text-gray-400 hover:text-gray-600' }} rounded transition"
                                            title="{{ $container->is_full ? 'Marcar disponible' : 'Marcar lleno' }}">
                                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                                        </button>
                                        <button type="button" wire:click="openEdit({{ $container->id }})"
                                            class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                            title="Editar">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                        <span class="text-xs text-gray-400 ml-auto">
                                            <span class="material-symbols-outlined text-xs align-text-bottom">drag_indicator</span>
                                        </span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-6 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                                    <span class="material-symbols-outlined text-xl">inventory_2</span>
                                    <p class="text-xs mt-1">Sin contenedores</p>
                                </div>
                                @endforelse
                            </div>

                            {{-- Inline form en modo visual --}}
                            @if($showInlineForm === $rack->id)
                            <div class="border-t border-blue-100 bg-blue-50/50 px-3 py-2">
                                <form wire:submit="quickCreate" class="space-y-2">
                                    <input type="text" wire:model="quickCode"
                                        class="w-full py-1.5 px-2.5 rounded-lg border border-gray-300 bg-white text-sm font-mono"
                                        placeholder="Código">
                                    <input type="text" wire:model="quickLabel"
                                        class="w-full py-1.5 px-2.5 rounded-lg border border-gray-300 bg-white text-sm"
                                        placeholder="Nombre del contenedor">
                                    @error('quickLabel') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    <div class="flex gap-2">
                                        <select wire:model="quickType"
                                            class="flex-1 py-1.5 px-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                                            <option value="shelf">Bandeja</option>
                                            <option value="bin">Caja / Bin</option>
                                            <option value="container">Contenedor</option>
                                            <option value="drawer">Gaveta</option>
                                        </select>
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                                            Crear
                                        </button>
                                        <button type="button" wire:click="$set('showInlineForm', null)"
                                            class="px-2 py-1.5 text-gray-400 hover:text-gray-600 transition">
                                            <span class="material-symbols-outlined text-lg">close</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @endif

                            {{-- Rack footer --}}
                            <div class="bg-white border-t border-gray-200 px-3 py-1.5">
                                <button type="button" wire:click="toggleInlineForm({{ $rack->id }})"
                                    class="w-full text-xs text-blue-600 hover:text-blue-800 inline-flex items-center justify-center gap-1 py-1">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Agregar contenedor
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12 text-gray-400">
                        <span class="material-symbols-outlined text-4xl">shelves</span>
                        <p class="mt-2 text-sm">No hay racks activos. Creá ubicaciones raíz para visualizarlas.</p>
                        <button type="button" wire:click="openCreate"
                            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">add</span>
                            Crear primera estantería
                        </button>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Modal crear/editar estantería --}}
    @if($showModal)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-lg" @click.away="open = false">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            @if($editingId)
                                Editar: {{ $code }}
                            @else
                                Nueva estantería
                            @endif
                        </h3>

                        @if(!$editingId)
                            <p class="text-sm text-gray-500">Creá una estantería principal. El código <strong>{{ $code }}</strong> se generará automáticamente. Después podés agregarle contenedores con el botón <strong>+</strong>.</p>
                        @endif

                        {{-- Al crear: solo nombre + descripción --}}
                        @if(!$editingId)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la estantería *</label>
                            <input type="text" wire:model="label" autofocus
                                class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm"
                                placeholder="Ej: Estantería Principal">
                            @error('label') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm resize-none"
                                placeholder="¿Qué se guarda aquí? Ej: Repuestos de cámaras y equipos de red"></textarea>
                        </div>

                        {{-- Al editar: todos los campos --}}
                        @else
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                                <input type="text" wire:model="code"
                                    class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                                @error('code') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select wire:model="type"
                                    class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                                    @foreach($typeOptions as $val => $txt)
                                    <option value="{{ $val }}">{{ $txt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Etiqueta</label>
                            <input type="text" wire:model="label"
                                class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                            @error('label') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea wire:model="description" rows="2"
                                class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bodega / Área</label>
                            <input type="text" wire:model="warehouse"
                                class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm"
                                placeholder="Ej: Bodega Principal">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación padre</label>
                            @if(!$parent_id)
                            <input type="text" value="Raíz — no aplica" disabled
                                class="w-full py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-400">
                            @else
                            <select wire:model="parent_id"
                                class="w-full py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                                <option value="">Ninguna (raíz)</option>
                                @foreach($availableParents->whereNull('parent_id') as $p)
                                @if($p->id !== $editingId)
                                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->label }}</option>
                                @endif
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Solo se muestran estanterías raíz. Los hijos siempre dependen de una raíz.</p>
                            @endif
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="is_active" class="rounded border-gray-300">
                            Activo
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="is_full" class="rounded border-gray-300">
                            <span class="text-red-600">Lleno — no permitir más productos</span>
                        </label>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            x-on:click="{{ $editingId ? 'if($wire.parent_id && !confirm(\'¿Estás seguro de mover esta ubicación?\')) return false;' : '' }}"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition">
                            {{ $editingId ? 'Guardar cambios' : 'Crear estantería' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal confirmar eliminación --}}
    @if($confirmingDelete)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">delete</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Eliminar ubicación</h3>
                    <p class="text-sm text-gray-600 mt-2">¿Estás seguro? No se puede eliminar si tiene sub-ubicaciones.</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="executeDelete"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700 transition">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => {
                    initSortableShelves();
                });
            });
            initSortableShelves();
        });

        function initSortableShelves() {
            document.querySelectorAll('[data-sortable-rack]').forEach(el => {
                if (el.sortableInstance) {
                    el.sortableInstance.destroy();
                }
                el.sortableInstance = new Sortable(el, {
                    group: 'shelves-containers',
                    animation: 200,
                    ghostClass: 'opacity-40',
                    dragClass: '!border-blue-400 !shadow-lg',
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                    onEnd: function (evt) {
                        const containerId = evt.item.dataset.containerId;
                        const newRackId = evt.to.dataset.sortableRack;
                        if (containerId && newRackId && evt.from !== evt.to) {
                            Livewire.dispatch('moveContainer', {
                                containerId: parseInt(containerId),
                                newParentId: parseInt(newRackId)
                            });
                        }
                    }
                });
            });
        }
    </script>
    @endpush
</div>
