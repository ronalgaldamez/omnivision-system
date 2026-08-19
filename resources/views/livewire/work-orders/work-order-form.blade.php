<div class="max-w-5xl mx-auto">
    <x-ui.card icon="{{ $orderId ? 'edit' : 'engineering' }}" title="{{ $orderId ? 'Editar' : 'Nueva' }} Orden de Trabajo" subtitle="{{ $orderId ? 'Modifica los datos de la orden' : 'Asigna un técnico y registra una nueva orden' }}">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('work-orders.index') }}">Volver al listado</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6">
            <form wire:submit.prevent="save" class="space-y-6">

                {{-- ========== SECCIÓN 1: SERVICIO ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">handyman</span>
                        Tipo de Servicio
                        @if(!$canEditNocAndService)
                            <span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Solo lectura</span>
                        @endif
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo de servicio *</label>
                            @if($canEditNocAndService)
                                <select wire:model.live="service_type_id"
                                    class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                    <option value="">Seleccione</option>
                                    @foreach($serviceTypes as $type)
                                        <option value="{{ $type->id }}">{{ str_replace('_', ' ', $type->name) }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="w-full px-3 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-700">
                                    @php $selectedType = $serviceTypes->firstWhere('id', $service_type_id); @endphp
                                    {{ $selectedType ? str_replace('_', ' ', $selectedType->name) : 'No definido' }}
                                </div>
                            @endif
                            @error('service_type_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">¿Requiere NOC?</label>
                                <p class="text-xs text-gray-500 mt-0.5">Si se activa, el ticket se enviará al panel NOC.</p>
                            </div>
                            @if($canEditNocAndService)
                                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                    <input type="checkbox" wire:model.live="requires_noc" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            @else
                                <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $requires_noc ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $requires_noc ? 'Sí' : 'No' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ========== SECCIÓN 2: ASIGNACIÓN ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">assignment_ind</span>
                        Asignación
                        @if($orderId && $acceptedAt)
                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Aceptada {{ $acceptedAt->format('H:i') }}</span>
                        @elseif($orderId)
                            <span class="ml-2 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">Pendiente de aceptación</span>
                        @endif
                    </h2>
                    @if($orderId && !$acceptedAt)
                        <x-ui.alert variant="warning" title="OT no aceptada">
                            <p>Esta OT aún no ha sido aceptada. Aceptala desde el listado de órdenes para poder asignar técnico, auxiliar y vehículo.</p>
                        </x-ui.alert>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @if($canAssign && (!$orderId || $acceptedAt))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    Técnico *
                                </label>
                                @if($technician_id)
                                <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-gray-200 bg-white">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="material-symbols-outlined text-gray-400 flex-shrink-0">engineering</span>
                                        <span class="text-sm font-medium text-gray-800 truncate">{{ $technicianSearch }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-medium">Principal</span>
                                        @if($this->technicianLoad > 0)
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-medium" title="OTs activas asignadas">{{ $this->technicianLoad }} OTs</span>
                                        @endif
                                        <button type="button" wire:click="openTechnicianModal"
                                            class="px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition">Cambiar</button>
                                        <button type="button" wire:click="clearTechnician"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Quitar técnico">
                                            <span class="material-symbols-outlined text-lg">close</span>
                                        </button>
                                    </div>
                                </div>
                                @else
                                <div class="flex gap-2" x-data="{ focused: false }">
                                    <div class="relative flex-1">
                                        <input type="text" wire:model.live.debounce.300ms="technicianSearch"
                                            @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                            placeholder="Buscar técnico por nombre..."
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                        @if(count($technicianResults) > 0)
                                            <ul x-show="focused" x-transition
                                                class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                                @foreach($technicianResults as $tech)
                                                    <li wire:click="selectTechnician({{ $tech->id }})"
                                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                        <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $tech->name }}</span>
                                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Principal</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="openTechnicianModal"
                                        class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                        title="Ver todos los técnicos">
                                        <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                        <span class="hidden sm:inline">Ver todos</span>
                                    </button>
                                </div>
                                @endif
                                @error('technician_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">handyman</span>
                                    Auxiliar
                                </label>
                                @if($auxiliar_technician_id)
                                <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-gray-200 bg-white">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="material-symbols-outlined text-gray-400 flex-shrink-0">handyman</span>
                                        <span class="text-sm font-medium text-gray-800 truncate">{{ $auxiliarSearch }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-medium">Auxiliar</span>
                                        <button type="button" wire:click="openAuxiliarModal"
                                            class="px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition">Cambiar</button>
                                        <button type="button" wire:click="clearAuxiliar"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Quitar auxiliar">
                                            <span class="material-symbols-outlined text-lg">close</span>
                                        </button>
                                    </div>
                                </div>
                                @else
                                <div class="flex gap-2" x-data="{ focused: false }">
                                    <div class="relative flex-1">
                                        <input type="text" wire:model.live.debounce.300ms="auxiliarSearch"
                                            @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                            placeholder="Buscar auxiliar por nombre..."
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                        @if(count($auxiliarResults) > 0)
                                            <ul x-show="focused" x-transition
                                                class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                                @foreach($auxiliarResults as $tech)
                                                    <li wire:click="selectAuxiliar({{ $tech->id }})"
                                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                        <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $tech->name }}</span>
                                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Auxiliar</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="openAuxiliarModal"
                                        class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                        title="Ver todos los auxiliares">
                                        <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                        <span class="hidden sm:inline">Ver todos</span>
                                    </button>
                                </div>
                                @endif
                                @error('auxiliar_technician_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if(!$orderId || $acceptedAt)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">directions_car</span>
                                Vehículo
                                @if($vehicle_id)
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-medium">Asignado</span>
                                @endif
                            </label>
                            @if($vehicle_id)
                            <div class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg border border-gray-200 bg-white">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="material-symbols-outlined text-gray-400">directions_car</span>
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $vehicleSearch }}</span>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button type="button" wire:click="openVehicleModal"
                                        class="px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition">Cambiar</button>
                                    <button type="button" wire:click="clearVehicle"
                                        class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Quitar vehículo">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="flex gap-2" x-data="{ focused: false }">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="vehicleSearch"
                                        @focus="focused = true" @blur="setTimeout(() => focused = false, 200)"
                                        placeholder="Buscar por placa o marca..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if(count($vehicleResults) > 0)
                                        <ul x-show="focused" x-transition
                                            class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100 ring-1 ring-black/5">
                                            @foreach($vehicleResults as $veh)
                                                <li wire:click="selectVehicle({{ $veh->id }})"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                                    <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $veh->placa }} · {{ $veh->marca }}</span>
                                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $veh->modelo }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <button type="button" wire:click="openVehicleModal"
                                    class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                    title="Ver todos los vehículos">
                                    <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                    <span class="hidden sm:inline">Ver todos</span>
                                </button>
                            </div>
                            @endif
                            <p class="text-[10px] text-gray-400 mt-1">Se sugiere el vehículo del encargado al elegir el técnico. Podés cambiarlo.</p>
                            @error('vehicle_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="{{ $canAssign ? 'md:col-span-2' : '' }}">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                Cliente *
                            </label>
                            @if($canChangeClient)
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="clientSearch"
                                        placeholder="Buscar por nombre o teléfono..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if(!empty($clientSearchResults))
                                        <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                            @foreach($clientSearchResults as $client)
                                                <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}', '{{ $client->phone }}')"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                    <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                                    <span class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <x-ui.button variant="success" icon="person_add" wire:click="openClientModal">Nuevo</x-ui.button>
                            </div>
                            @else
                            <div class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm">
                                <span class="material-symbols-outlined text-gray-400">person</span>
                                <span class="text-gray-700 font-medium">{{ $selectedClient?->name ?? 'No especificado' }}</span>
                                @if($selectedClient?->phone)
                                    <span class="text-xs text-gray-400">{{ $selectedClient->phone }}</span>
                                @endif
                                <span class="ml-auto px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-[10px] font-medium">Solo admin puede cambiar</span>
                            </div>
                            @endif
                            @error('client_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if($selectedClient)
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-400">info</span>
                                Datos del cliente seleccionado
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 text-sm">
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">person</span>
                                    <div><p class="text-xs text-gray-500">Nombre</p><p class="text-gray-800 font-medium">{{ $selectedClient->name }}</p></div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">call</span>
                                    <div><p class="text-xs text-gray-500">Teléfono</p><p class="text-gray-800">{{ $selectedClient->phone ?? '—' }}</p></div>
                                </div>
                                @if($selectedClient->document_type && $selectedClient->document_number)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">fingerprint</span>
                                        <div><p class="text-xs text-gray-500">{{ strtoupper($selectedClient->document_type) }}</p><p class="text-gray-800">{{ $selectedClient->document_number }}</p></div>
                                    </div>
                                @endif
                                @if($selectedClient->email)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">mail</span>
                                        <div><p class="text-xs text-gray-500">Correo</p><p class="text-gray-800">{{ $selectedClient->email }}</p></div>
                                    </div>
                                @endif
                                @if($selectedClient->address)
                                    <div class="flex items-start gap-2 col-span-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                        <div><p class="text-xs text-gray-500">Dirección</p><p class="text-gray-800">{{ $selectedClient->address }}</p></div>
                                    </div>
                                @endif
                                @if($selectedClient->installation_address)
                                    <div class="flex items-start gap-2 col-span-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">home_pin</span>
                                        <div><p class="text-xs text-gray-500">Instalación</p><p class="text-gray-800">{{ $selectedClient->installation_address }}</p></div>
                                    </div>
                                @endif
                                @if($selectedClient->service)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">tv</span>
                                        <div><p class="text-xs text-gray-500">Servicio</p><p class="text-gray-800">{{ $selectedClient->service }}</p></div>
                                    </div>
                                @endif
                                @if($selectedClient->nro_luz)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">bolt</span>
                                        <div><p class="text-xs text-gray-500">N.° de luz</p><p class="text-gray-800">{{ $selectedClient->nro_luz }}</p></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ========== SECCIÓN 3: PROGRAMACIÓN ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">calendar_month</span>
                        Programación
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">calendar_today</span>
                                Fecha programada
                                @if(!$scheduled_date)
                                    <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pendiente</span>
                                @endif
                            </label>
                            <div class="relative">
                                <input type="date" wire:model="scheduled_date"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                            </div>
                            @error('scheduled_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-400 mt-1">Si no se programa, quedará como pendiente de programación.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">flag</span>
                                Estado
                            </label>
                            <div class="relative">
                                <select wire:model="status"
                                    {{ !$canEditStatus ? 'disabled' : '' }}
                                    class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none {{ !$canEditStatus ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}">
                                    <option value="pending">Pendiente</option>
                                    <option value="in_progress">En progreso</option>
                                    <option value="completed">Completada</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">info</span>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                            @if(!$canEditStatus)
                                <p class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">lock</span>
                                    Solo el administrador puede modificar el estado.
                                </p>
                            @endif
                            @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- ========== SECCIÓN 4: UBICACIÓN ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">location_on</span>
                        Ubicación
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">pin_drop</span>
                                Latitud
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="latitude" readonly
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 shadow-sm text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                            </div>
                            @error('latitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">pin_drop</span>
                                Longitud
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="longitude" readonly
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 shadow-sm text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                            </div>
                            @error('longitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">map</span>
                            Mapa
                        </label>
                        <div id="map" style="height: 300px; width: 100%;"
                            class="rounded-lg border border-gray-300 shadow-sm"></div>
                        <div class="flex gap-2 mt-3">
                            <x-ui.button variant="success" icon="my_location" id="getLocationBtn">Mi ubicación</x-ui.button>
                            <x-ui.button variant="secondary" icon="delete" id="clearLocationBtn">Limpiar</x-ui.button>
                        </div>
                    </div>
                </div>

                {{-- ========== SECCIÓN 5: DATOS TÉCNICOS ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">settings</span>
                        Datos Técnicos
                        @if($technicalDataLoaded)
                            <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Precargados del cliente</span>
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500">Estos campos serán llenados por el técnico durante la instalación.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">person</span>
                                Nombre de perfil
                            </label>
                            <input type="text" wire:model="profile_name" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="Ej: Usuario1">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">key</span>
                                Contraseña de perfil
                            </label>
                            <input type="text" wire:model="profile_password" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="Contraseña">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">wifi</span>
                                Nombre wifi
                            </label>
                            <input type="text" wire:model="wifi_name" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="SSID">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">key</span>
                                Contraseña wifi
                            </label>
                            <input type="text" wire:model="wifi_password" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="Contraseña">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">lan</span>
                                MAC
                            </label>
                            <input type="text" wire:model="mac" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="00:00:00:00:00:00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">settings_input_antenna</span>
                                PON
                            </label>
                            <input type="text" wire:model="pon" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="PON">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">cable</span>
                                Mufa
                            </label>
                            <input type="text" wire:model="mufa" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100" placeholder="Número de mufa">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">calendar_today</span>
                                Fecha de instalación
                            </label>
                            <input type="date" wire:model="installation_date" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100">
                        </div>
                    </div>
                </div>

                {{-- ========== SECCIÓN 6: NOTAS ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">sticky_note_2</span>
                        Notas
                    </h2>
                    <div class="relative">
                        <textarea wire:model="notes" rows="3"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Notas o indicaciones adicionales"></textarea>
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('notes') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- ========== RESUMEN DE ASIGNACIÓN EN VIVO ========== --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">assignment_turned_in</span>
                        Resumen de la asignación
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 text-sm">
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Técnico</p>
                            <p class="font-medium text-gray-800 truncate">{{ $technicianSearch ?: '—' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Auxiliar</p>
                            <p class="font-medium text-gray-800 truncate">{{ $auxiliarSearch ?: '—' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Vehículo</p>
                            <p class="font-medium text-gray-800 truncate">{{ $vehicleSearch ?: '—' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Fecha programada</p>
                            <p class="font-medium text-gray-800">{{ $scheduled_date ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button variant="ghost" href="{{ route('work-orders.index') }}">Cancelar</x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="save">Guardar</x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.card>

    {{-- Modal para crear cliente --}}
    @if($showClientModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-3xl max-h-[85vh] overflow-y-auto">
                <x-ui.card>
                    <x-slot:headerActions>
                        <button type="button" wire:click="closeClientModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <div class="p-5">
                        <livewire:clients.client-form :key="$modalKey" />
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal listado de técnicos (principales) --}}
    @if($showTechnicianModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <x-ui.card>
                    <x-slot:headerActions>
                        <button type="button" wire:click="closeTechnicianModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <div class="p-5">
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-gray-500">engineering</span>
                            Técnicos (principales)
                        </h3>
                        <div class="space-y-1 max-h-80 overflow-y-auto">
                            @forelse($technicianList as $tech)
                                <button type="button" wire:click="selectTechnician({{ $tech->id }}); closeTechnicianModal()"
                                    class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-blue-50 transition text-sm flex items-center justify-between group">
                                    <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $tech->name }}</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Principal</span>
                                </button>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-6">No hay técnicos registrados</p>
                            @endforelse
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal listado de auxiliares --}}
    @if($showAuxiliarModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <x-ui.card>
                    <x-slot:headerActions>
                        <button type="button" wire:click="closeAuxiliarModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <div class="p-5">
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-gray-500">handyman</span>
                            Auxiliares
                        </h3>
                        <div class="space-y-1 max-h-80 overflow-y-auto">
                            @forelse($auxiliarList as $tech)
                                <button type="button" wire:click="selectAuxiliar({{ $tech->id }}); closeAuxiliarModal()"
                                    class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-blue-50 transition text-sm flex items-center justify-between group">
                                    <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $tech->name }}</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Auxiliar</span>
                                </button>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-6">No hay auxiliares registrados</p>
                            @endforelse
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal listado de vehículos --}}
    @if($showVehicleModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <x-ui.card>
                    <x-slot:headerActions>
                        <button type="button" wire:click="closeVehicleModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <div class="p-5">
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-gray-500">directions_car</span>
                            Vehículos (activos)
                        </h3>
                        <div class="space-y-1 max-h-80 overflow-y-auto">
                            @forelse($vehicleList as $veh)
                                <button type="button" wire:click="selectVehicle({{ $veh->id }}); closeVehicleModal()"
                                    class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-blue-50 transition text-sm flex items-center justify-between group">
                                    <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ $veh->placa }} · {{ $veh->marca }}</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $veh->modelo }}</span>
                                </button>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-6">No hay vehículos activos registrados</p>
                            @endforelse
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @endif

    <div x-data="{ toasts: [] }"
        x-on:show-toast.window="toasts.push({ id: Date.now() + Math.random(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => toasts.shift(), 3500)"
        class="fixed bottom-5 right-5 z-50 flex flex-col-reverse gap-2 items-end"
        style="max-height: 80vh; overflow-y: auto;">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100">
                <div x-show="toast.type === 'success'"
                    class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 whitespace-nowrap">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span x-text="toast.message" class="text-sm font-medium"></span>
                </div>
                <div x-show="toast.type === 'error'"
                    class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 whitespace-nowrap">
                    <span class="material-symbols-outlined">error</span>
                    <span x-text="toast.message" class="text-sm font-medium"></span>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            if (typeof L === 'undefined') { console.warn('Leaflet no está cargado. El mapa no funcionará.'); return; }
            var mapContainer = document.getElementById('map');
            if (!mapContainer) return;
            var map = L.map('map').setView([13.6929, -89.2182], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>' }).addTo(map);
            var marker = null;
            var lat = @json($latitude ?? null);
            var lng = @json($longitude ?? null);
            if (lat && lng) { map.setView([lat, lng], 15); marker = L.marker([lat, lng]).addTo(map); }
            map.on('click', function (e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng).addTo(map);
                @this.set('latitude', e.latlng.lat);
                @this.set('longitude', e.latlng.lng);
            });
            var getLocationBtn = document.getElementById('getLocationBtn');
            var clearLocationBtn = document.getElementById('clearLocationBtn');
            if (getLocationBtn) {
                getLocationBtn.addEventListener('click', function () {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function (position) {
                            var pos = [position.coords.latitude, position.coords.longitude];
                            map.setView(pos, 15);
                            if (marker) map.removeLayer(marker);
                            marker = L.marker(pos).addTo(map);
                            @this.set('latitude', pos[0]);
                            @this.set('longitude', pos[1]);
                        });
                    } else { alert('Geolocalización no soportada'); }
                });
            }
            if (clearLocationBtn) {
                clearLocationBtn.addEventListener('click', function () {
                    if (marker) map.removeLayer(marker);
                    @this.set('latitude', null);
                    @this.set('longitude', null);
                    map.setView([13.6929, -89.2182], 13);
                });
            }
        });
        document.addEventListener('livewire:init', () => {
            Livewire.on('clientCreated', ({ id, name, phone }) => {
                @this.call('selectClient', id, name, phone);
                @this.call('closeClientModal');
            });
        });
    </script>
@endpush