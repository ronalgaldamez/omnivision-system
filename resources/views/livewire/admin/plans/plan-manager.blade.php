<div class="max-w-7xl mx-auto">
    <x-ui.card icon="assignment" title="Gestión de Planes y Zonas" subtitle="Administrá zonas geográficas, planes de servicio y precios por zona." class="overflow-visible">
        {{-- Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="flex gap-1 px-6">
                <button wire:click="setTab('zones')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'zones' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">map</span>
                    Zonas y Precios
                </button>
                <button wire:click="setTab('plans')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'plans' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">subscriptions</span>
                    Planes
                </button>
                <button wire:click="setTab('groups')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'groups' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">folder</span>
                    Grupos
                </button>
                <button wire:click="setTab('history')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'history' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">history</span>
                    Historial
                </button>
                <button wire:click="setTab('rules')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'rules' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">tune</span>
                    Reglas
                </button>
            </nav>
        </div>

        <div class="p-6">
            @if($activeTab === 'zones')
            <div class="space-y-6">
                @livewire('admin.plans.zone-manager', key('zone-manager'))
            </div>
            @elseif($activeTab === 'plans')
            <div class="space-y-4">
                @livewire('admin.plans.plan-list', key('plan-list'))
            </div>
            @elseif($activeTab === 'groups')
            <div class="space-y-4">
                @livewire('admin.plans.plan-group-manager', key('group-manager'))
            </div>
            @elseif($activeTab === 'history')
            <div class="space-y-4">
                @livewire('admin.plans.plan-history', key('plan-history'))
            </div>
            @elseif($activeTab === 'rules')
            <div class="space-y-4 px-6 py-4">
                @livewire('admin.plans.plan-rule-manager', key('rule-manager'))
            </div>
            @endif
        </div>
    </x-ui.card>
</div>
