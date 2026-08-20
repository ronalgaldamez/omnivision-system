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
                <button wire:click="setTab('install_fees')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'install_fees' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">handyman</span>
                    Tarifas instalación
                </button>
                <button wire:click="setTab('promotions')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'promotions' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">campaign</span>
                    Promociones
                </button>
                <button wire:click="setTab('contract_rules')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === 'contract_rules' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span class="material-symbols-outlined text-base align-text-bottom me-1">assignment_late</span>
                    Reglas de contrato
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
            @elseif($activeTab === 'install_fees')
            <div class="space-y-4">
                @livewire('admin.plans.install-fee-manager', key('install-fee-manager'))
            </div>
            @elseif($activeTab === 'promotions')
            <div class="space-y-4">
                @livewire('admin.plans.campaign-manager', ['category' => 'promotion'], key('promotion-manager'))
            </div>
            @elseif($activeTab === 'contract_rules')
            <div class="space-y-4">
                @livewire('admin.plans.campaign-manager', ['category' => 'contract_rule'], key('contract-rule-manager'))
            </div>
            @endif
        </div>
    </x-ui.card>
</div>
