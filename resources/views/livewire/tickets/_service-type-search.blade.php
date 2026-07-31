<div class="flex gap-2">
    <div class="relative flex-1">
        <x-ui.input type="text" wire:model.live.debounce.300ms="serviceTypeSearch"
            placeholder="Buscar tipo de servicio..." icon="search" />
        @if (count($serviceTypeResults) > 0)
            <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                @include('livewire.tickets._service-type-search-results', ['showBadges' => $showBadges ?? true])
            </ul>
        @endif
    </div>
    @if ($showVerTodos ?? true)
        <button type="button" wire:click="openServiceTypeModal"
            class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
            title="Ver todos los tipos de servicio">
            <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
            <span class="hidden sm:inline">Ver todos</span>
        </button>
    @endif
</div>
