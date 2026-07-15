@php
    $hasChildren = $zone->children->count() > 0;
    $isExpanded = in_array($zone->id, $expandedZones);
    $isSelected = (string) $selectedZoneId === (string) $zone->id;
@endphp
<div class="relative" style="padding-left: {{ $depth * 1.25 }}rem;">
    @if($depth > 0)
    <div class="absolute left-0 top-0 bottom-0" style="width: 1.25rem;">
        <div class="absolute left-[0.5rem] top-0 bottom-0 border-l border-gray-200"></div>
    </div>
    @endif
    <div class="relative flex items-center gap-1 py-0.5 px-1.5 rounded hover:bg-gray-100 cursor-pointer group {{ $isSelected ? 'bg-blue-100 ring-1 ring-blue-200' : '' }}"
        wire:click="selectZone({{ $zone->id }})">
        @if($depth > 0)
        <div class="absolute -left-[0.25rem] top-1/2 w-2.5 h-0 border-t border-gray-200"></div>
        @endif

        @if($hasChildren)
        <button wire:click.stop="toggleExpandZone({{ $zone->id }})" class="flex-shrink-0 text-gray-400 hover:text-gray-600 w-4 h-4 flex items-center justify-center">
            <span class="material-symbols-outlined text-xs">{{ $isExpanded ? 'expand_more' : 'chevron_right' }}</span>
        </button>
        @else
        <span class="flex-shrink-0 w-4 h-4 flex items-center justify-center">
            <span class="material-symbols-outlined text-xs {{ $isSelected ? 'text-blue-400' : 'text-gray-300' }}">circle</span>
        </span>
        @endif

        <span class="text-xs {{ $isSelected ? 'text-blue-700 font-semibold' : 'text-gray-700' }} truncate">{{ $zone->name }}</span>
        <span class="text-[10px] text-gray-400 bg-gray-100 px-1 rounded">{{ substr(ucfirst($zone->level), 0, 6) }}</span>

        @if($isSelected)
        <span class="ml-auto text-blue-500">
            <span class="material-symbols-outlined text-xs">check_circle</span>
        </span>
        @endif
    </div>

    @if($hasChildren && $isExpanded)
        @foreach($zone->children as $child)
            @include('livewire.supervisor._zone-tree', [
                'zone' => $child,
                'depth' => $depth + 1,
                'allZones' => $allZones,
                'selectedZoneId' => $selectedZoneId,
            ])
        @endforeach
    @endif
</div>
