@php
    $hasChildren = $zone->children->count() > 0;
    $isExpanded = in_array($zone->id, $expandedZones);
    $isSelected = $selectedZoneId === $zone->id;
@endphp
<div class="relative" style="padding-left: {{ $depth * 1.5 }}rem;">
    @if($depth > 0)
    <div class="absolute left-0 top-0 bottom-0" style="width: 1.5rem;">
        <div class="absolute left-[0.6rem] top-0 bottom-0 border-l-2 border-gray-200"></div>
    </div>
    @endif
    <div class="relative flex items-center gap-1 py-1 px-2 rounded-lg hover:bg-gray-50 group {{ $isSelected ? 'bg-blue-50 ring-1 ring-blue-200' : '' }}">
        @if($depth > 0)
        <div class="absolute -left-[0.35rem] top-1/2 w-3 h-0 border-t-2 border-gray-200"></div>
        @endif

        @if($hasChildren)
        <button wire:click="toggleExpand({{ $zone->id }})" class="flex-shrink-0 text-gray-400 hover:text-gray-600 w-5 h-5 flex items-center justify-center">
            <span class="material-symbols-outlined text-sm">{{ $isExpanded ? 'expand_more' : 'chevron_right' }}</span>
        </button>
        @else
        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center">
            <span class="material-symbols-outlined text-sm text-gray-300">circle</span>
        </span>
        @endif

        <button wire:click="selectZone({{ $zone->id }})"
            class="flex-1 text-left text-sm {{ $isSelected ? 'text-blue-700 font-semibold' : 'text-gray-800' }} hover:text-blue-600 truncate">
            {{ $zone->name }}
        </button>

        <span class="flex-shrink-0 text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ ucfirst($zone->level) }}</span>

        <button wire:click="openSubZoneModal({{ $zone->id }})"
            class="flex-shrink-0 opacity-0 group-hover:opacity-100 text-green-600 hover:text-green-700 transition-opacity"
            title="Agregar sub-zona">
            <span class="material-symbols-outlined text-base">add_circle</span>
        </button>

        <button wire:click="openZoneModal({{ $zone->id }})"
            class="flex-shrink-0 opacity-0 group-hover:opacity-100 text-blue-500 hover:text-blue-700 transition-opacity"
            title="Editar">
            <span class="material-symbols-outlined text-base">edit</span>
        </button>

        <button wire:click="promptDeleteZone({{ $zone->id }})"
            class="flex-shrink-0 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-opacity"
            title="Eliminar">
            <span class="material-symbols-outlined text-base">remove_circle</span>
        </button>
    </div>

    @if($hasChildren && $isExpanded)
        @foreach($zone->children as $child)
            @include('livewire.admin.plans._zone-tree', ['zone' => $child, 'depth' => $depth + 1, 'expandedZones' => $expandedZones, 'selectedZoneId' => $selectedZoneId])
        @endforeach
    @endif
</div>
