<div class="max-w-7xl mx-auto">
    <x-ui.card icon="apartment" title="Estructura" subtitle="Empresas (razón social) y las sucursales que las componen">
        {{-- Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex gap-1 px-6">
                <button wire:click="setTab('companies')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'companies' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">apartment</span>
                    Empresas
                </button>
                <button wire:click="setTab('branches')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'branches' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">store</span>
                    Sucursales
                </button>
            </nav>
        </div>

        <div class="p-6">
            @if($activeTab === 'companies')
                <div class="space-y-4">
                    @livewire('admin.companies.company-index', key('company-index'))
                </div>
            @else
                <div class="space-y-4">
                    @livewire('admin.branches.branch-index', key('branch-index'))
                </div>
            @endif
        </div>
    </x-ui.card>
</div>
