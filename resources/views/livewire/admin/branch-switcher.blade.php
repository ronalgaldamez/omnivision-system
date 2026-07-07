<div class="relative" x-data="{ open: false }">
    <button type="button" @click="open = !open"
        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition border"
        :class="open ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'">
        <span class="material-symbols-outlined text-sm">store</span>
        <span>
            @if ($activeBranchId)
                {{ $branches->firstWhere('id', (int) $activeBranchId)?->name ?? 'Sucursal' }}
            @else
                Global
            @endif
        </span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 mt-1.5 w-64 bg-white rounded-xl border border-gray-200 shadow-xl z-50 overflow-hidden ring-1 ring-black/5">
        <div class="py-1 max-h-80 overflow-y-auto">
            <button type="button" wire:click="switchBranch('')" @click="open = false"
                class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition flex items-center justify-between {{ !$activeBranchId ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">public</span>
                    Global
                </span>
                @if (!$activeBranchId)
                    <span class="material-symbols-outlined text-blue-600 text-sm">check</span>
                @endif
            </button>
            @foreach ($branches as $branch)
                <button type="button" wire:click="switchBranch('{{ $branch->id }}')" @click="open = false"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition flex items-center justify-between {{ (string) $activeBranchId === (string) $branch->id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">store</span>
                        {{ $branch->name }}
                    </span>
                    @if ((string) $activeBranchId === (string) $branch->id)
                        <span class="material-symbols-outlined text-blue-600 text-sm">check</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
