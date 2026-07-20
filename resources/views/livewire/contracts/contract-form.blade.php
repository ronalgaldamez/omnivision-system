<div class="max-w-3xl mx-auto">
    <x-ui.card icon="description" title="Nuevo Contrato" subtitle="Crear contrato de servicio sin ticket previo">

        {{-- Cliente --}}
        <div class="space-y-3 pb-6 border-b border-gray-100" x-data="{ showList: false }">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person</span>
                Cliente
            </label>

            @if($selectedClient)
                <div class="flex items-center justify-between bg-gray-50 rounded-lg border border-gray-200 p-3">
                    <div>
                        <p class="font-medium text-gray-800">{{ $selectedClient->name }}</p>
                        <p class="text-xs text-gray-500">{{ $selectedClient->phone }} @if($selectedClient->document_number) | {{ $selectedClient->document_number }} @endif</p>
                    </div>
                    <button type="button" wire:click="$set('selectedClient', null); $set('client_id', ''); $set('clientSearch', '')"
                        class="text-sm text-red-600 hover:text-red-700 font-medium">Cambiar</button>
                </div>
            @else
                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <x-ui.input type="text" wire:model.live.debounce.300ms="clientSearch" icon="search" placeholder="Buscar cliente por nombre, teléfono o DUI..." />
                        @if($clientSearchResults)
                            <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach($clientSearchResults as $client)
                                    <button type="button" wire:click="selectClient({{ $client['id'] }})"
                                        class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                        <span class="font-medium text-gray-800">{{ $client['name'] }}</span>
                                        <span class="text-gray-500 ml-2">{{ $client['phone'] ?? '' }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <button type="button" wire:click="$set('showClientListModal', true)"
                        class="inline-flex items-center gap-1 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Ver todos
                    </button>
                    <button type="button" wire:click="$set('showClientModal', true)"
                        class="inline-flex items-center gap-1 px-3 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <span class="material-symbols-outlined text-base">add</span>
                        Nuevo
                    </button>
                </div>
            @endif
        </div>

        {{-- Datos del contrato --}}
        <div class="space-y-4 pt-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.select wire:model.live="service_type" label="Tipo de servicio" required icon="settings">
                    <option value="">Seleccionar tipo de servicio</option>
                    @foreach($availableServiceTypes as $st)
                        <option value="{{ $st['name'] }}">{{ $st['name'] }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="plan_id" label="Plan" icon="assignment">
                    <option value="">Sin plan</option>
                    @foreach($availablePlans as $p)
                        <option value="{{ $p['id'] }}">{{ $p['name'] }} @if($p['speed'])({{ $p['speed'] }})@endif — ${{ number_format($p['base_price'], 2) }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.select wire:model="zone_id" label="Zona" icon="map">
                    <option value="">Sin zona</option>
                    @foreach($availableZones as $z)
                        <option value="{{ $z['id'] }}">{{ $z['name'] }}</option>
                    @endforeach
                </x-ui.select>

                <div>
                    <x-ui.input type="number" wire:model="price" icon="attach_money" label="Precio" placeholder="0.00" step="0.01" min="0" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-ui.input type="date" wire:model="contract_date" label="Fecha del contrato" required icon="calendar_today" />
                <x-ui.select wire:model="status" label="Estado" icon="toggle_on">
                    <option value="active">Activo</option>
                    <option value="suspended">Suspendido</option>
                    <option value="cancelled">Cancelado</option>
                </x-ui.select>
            </div>

            <x-ui.textarea wire:model="installation_address" icon="edit_note" label="Dirección de instalación" rows="2"
                placeholder="Dirección donde se instalará el servicio" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input type="text" wire:model="latitude" icon="pin_drop" label="Latitud" placeholder="13.6929" />
                <x-ui.input type="text" wire:model="longitude" icon="pin_drop" label="Longitud" placeholder="-89.2182" />
            </div>
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 mt-6">
            <a href="{{ route('contracts.index') }}" wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</a>
            <x-ui.button type="button" variant="primary" icon="save" wire:click="save">Guardar contrato</x-ui.button>
        </div>
    </x-ui.card>

    {{-- Modal: Lista de clientes --}}
    @if($showClientListModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="$set('showClientListModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden" wire:click.self.stop>
                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Seleccionar cliente</h3>
                        <button type="button" wire:click="$set('showClientListModal', false)"
                            class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-lg transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <x-ui.input type="text" wire:model.live.debounce.300ms="clientListSearch" icon="search" placeholder="Buscar cliente..." />
                </div>
                <div class="max-h-80 overflow-y-auto p-2">
                    @forelse($clientListResults as $client)
                        <button type="button" wire:click="selectFromList({{ $client['id'] }})"
                            class="w-full text-left px-4 py-3 hover:bg-gray-50 rounded-lg text-sm border-b border-gray-100 last:border-0">
                            <span class="font-medium text-gray-800">{{ $client['name'] }}</span>
                            <span class="text-gray-500 ml-2">{{ $client['phone'] ?? '' }}</span>
                        </button>
                    @empty
                        <p class="text-gray-500 text-sm text-center py-8">Escribí al menos 2 caracteres para buscar.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Registrar cliente --}}
    @if($showClientModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="$set('showClientModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto p-6" wire:click.self.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Registrar Cliente</h3>
                    <button type="button" wire:click="$set('showClientModal', false)"
                        class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <livewire:clients.client-form key="contract-client-form" />
            </div>
        </div>
    @endif
</div>
