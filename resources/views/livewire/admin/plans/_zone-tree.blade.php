@php
    $hasChildren = $zone->children->count() > 0;
    $isExpanded = in_array($zone->id, $expandedZones);
    $isSelected = $selectedZoneId === $zone->id;
    if ($zone->has_internet && $zone->has_cable) {
        $serviceBadge = ['label' => 'Internet + Cable', 'class' => 'bg-green-100 text-green-700'];
    } elseif ($zone->has_internet) {
        $serviceBadge = ['label' => 'Solo Internet', 'class' => 'bg-blue-100 text-blue-700'];
    } elseif ($zone->has_cable) {
        $serviceBadge = ['label' => 'Solo Cable', 'class' => 'bg-amber-100 text-amber-700'];
    } else {
        $serviceBadge = ['label' => 'Sin servicio', 'class' => 'bg-gray-100 text-gray-400'];
    }
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
            class="flex-shrink-0 text-left text-sm {{ $isSelected ? 'text-blue-700 font-semibold' : 'text-gray-800' }} hover:text-blue-600 truncate min-w-0">
            {{ $zone->name }}
        </button>

        <span class="flex-shrink-0 text-xs {{ $serviceBadge['class'] }} px-1.5 py-0.5 rounded font-medium">{{ $serviceBadge['label'] }}</span>

        <span class="flex-shrink-0 text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ ucfirst($zone->level) }}</span>

        <div wire:click.away="$set('zoneActionMenu', null)" class="relative flex-shrink-0">
            <button wire:click="toggleZoneMenu({{ $zone->id }})"
                class="opacity-0 group-hover:opacity-100 text-green-600 hover:text-green-700 transition-opacity"
                title="Acciones">
                <span class="material-symbols-outlined text-base">add_circle</span>
            </button>
            @if($zoneActionMenu === $zone->id)
            <div class="absolute left-0 top-7 z-50 w-52 bg-white rounded-lg shadow-lg border border-gray-200 py-1 text-sm">
                <button wire:click="openSubZoneModal({{ $zone->id }})"
                    class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2 text-gray-700">
                    <span class="material-symbols-outlined text-sm text-green-600">add</span>
                    Agregar sub-zona
                </button>
            </div>
            @endif
        </div>

        <button wire:click="viewZone({{ $zone->id }})"
            class="flex-shrink-0 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 transition-opacity"
            title="Ver detalles">
            <span class="material-symbols-outlined text-base">visibility</span>
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
