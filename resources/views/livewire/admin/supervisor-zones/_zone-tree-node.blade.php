<div class="mb-2">
    <div class="flex items-center justify-between px-4 py-3 bg-white rounded-lg border hover:shadow-sm transition
        {{ $level === 0 ? 'border-gray-300' : 'border-gray-200' }}">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <span class="material-symbols-outlined text-gray-400 text-base shrink-0">
                {{ $level === 0 ? 'account_tree' : ($zone->children->isNotEmpty() ? 'folder' : 'pin_drop') }}
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $zone->name }}</p>
                <p class="text-xs text-gray-400">
                    {{ $zone->branch?->name ?? 'Sin sucursal' }}
                    <span class="text-gray-300 mx-1">·</span>
                    {{ ucfirst($zone->level) }}
                    @if($zone->parent)
                        <span class="text-gray-300 mx-1">·</span>
                        Hereda de: <span class="font-medium">{{ $zone->parent->name }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0 ml-4">
            {{-- Supervisores directos --}}
            <div class="flex flex-wrap gap-1 justify-end max-w-xs">
                @forelse($zone->supervisors as $sup)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        {{ $sup->name }}
                        <button type="button" wire:click="removeAssignment({{ $zone->id }}, {{ $sup->id }})"
                            wire:confirm="¿Remover a {{ $sup->name }} de {{ $zone->name }}?"
                            class="hover:text-red-600 transition">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </span>
                @empty
                    @php
                        $inherited = $zone->inheritedSupervisors();
                    @endphp
                    @if($inherited->isNotEmpty())
                        @foreach($inherited as $sup)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 italic" title="Heredado de {{ $zone->parent?->name }}">
                                {{ $sup->name }}
                                <span class="material-symbols-outlined text-xs">arrow_downward</span>
                            </span>
                        @endforeach
                    @else
                        <span class="text-xs text-gray-400 italic">Sin asignar</span>
                    @endif
                @endforelse
            </div>

            <button type="button" wire:click="editAssignments({{ $zone->id }})"
                class="px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition shrink-0">
                Asignar
            </button>
        </div>
    </div>

    {{-- Hijos --}}
    @if($zone->children->isNotEmpty())
        <div class="ml-6 mt-1 space-y-1 border-l-2 border-gray-100 pl-3">
            @foreach($zone->children as $child)
                @include('livewire.admin.supervisor-zones._zone-tree-node', [
                    'zone' => $child,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
