{{-- resources/views/livewire/work-orders/work-order-form.blade.php --}}
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">
                            {{ $orderId ? 'edit' : 'engineering' }}
                        </span>
                        {{ $orderId ? 'Editar' : 'Nueva' }} Orden de Trabajo
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $orderId ? 'Modifica los datos de la orden' : 'Asigna un técnico y registra una nueva orden' }}
                    </p>
                </div>
                <a href="{{ route('work-orders.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

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
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Técnico --}}
                        @if($canAssign)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    Técnico *
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="technicianSearch"
                                        placeholder="Buscar técnico por nombre..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if(!empty($technicianResults) && !$technician_id)
                                        <ul
                                            class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                            @foreach($technicianResults as $tech)
                                                <li wire:click="selectTechnician({{ $tech->id }})"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                    <span class="font-medium text-gray-800">{{ $tech->name }}</span>
                                                    <span class="text-xs text-gray-500">(ID: {{ $tech->id }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                @error('technician_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Cliente --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                Cliente *
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" wire:model.live.debounce.300ms="clientSearch"
                                        placeholder="Buscar por nombre o teléfono..."
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                    @if(!empty($clientSearchResults))
                                        <ul
                                            class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                            @foreach($clientSearchResults as $client)
                                                <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}', '{{ $client->phone }}')"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                    <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                                    <span
                                                        class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <button type="button" wire:click="openClientModal"
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-base">person_add</span>
                                    Nuevo
                                </button>
                            </div>
                            @error('client_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Datos del cliente seleccionado (ANCHO COMPLETO) --}}
                    @if($selectedClient)
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-3">
                                <span class="material-symbols-outlined text-gray-400">info</span>
                                Datos del cliente seleccionado
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 text-sm">
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">person</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Nombre</p>
                                        <p class="text-gray-800 font-medium">{{ $selectedClient->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">call</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Teléfono</p>
                                        <p class="text-gray-800">{{ $selectedClient->phone ?? '—' }}</p>
                                    </div>
                                </div>
                                @if($selectedClient->document_type && $selectedClient->document_number)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">fingerprint</span>
                                        <div>
                                            <p class="text-xs text-gray-500">{{ strtoupper($selectedClient->document_type) }}
                                            </p>
                                            <p class="text-gray-800">{{ $selectedClient->document_number }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($selectedClient->email)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">mail</span>
                                        <div>
                                            <p class="text-xs text-gray-500">Correo</p>
                                            <p class="text-gray-800">{{ $selectedClient->email }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($selectedClient->address)
                                    <div class="flex items-start gap-2 col-span-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                        <div>
                                            <p class="text-xs text-gray-500">Dirección</p>
                                            <p class="text-gray-800">{{ $selectedClient->address }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($selectedClient->installation_address)
                                    <div class="flex items-start gap-2 col-span-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">home_pin</span>
                                        <div>
                                            <p class="text-xs text-gray-500">Instalación</p>
                                            <p class="text-gray-800">{{ $selectedClient->installation_address }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($selectedClient->service)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">tv</span>
                                        <div>
                                            <p class="text-xs text-gray-500">Servicio</p>
                                            <p class="text-gray-800">{{ $selectedClient->service }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($selectedClient->nro_luz)
                                    <div class="flex items-start gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">bolt</span>
                                        <div>
                                            <p class="text-xs text-gray-500">N.° de luz</p>
                                            <p class="text-gray-800">{{ $selectedClient->nro_luz }}</p>
                                        </div>
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
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event</span>
                            </div>
                            @error('scheduled_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-400 mt-1">Si no se programa, quedará como pendiente de programación.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">flag</span>
                                Estado
                            </label>
                            <div class="relative">
                                <select wire:model="status"
                                    class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                    <option value="pending">Pendiente</option>
                                    <option value="in_progress">En progreso</option>
                                    <option value="completed">Completada</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">info</span>
                                <span
                                    class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                            </div>
                            @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
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
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                            </div>
                            @error('latitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">pin_drop</span>
                                Longitud
                            </label>
                            <div class="relative">
                                <input type="text" wire:model="longitude" readonly
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 shadow-sm text-sm">
                                <span
                                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">location_on</span>
                            </div>
                            @error('longitude') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
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
                            <button type="button" id="getLocationBtn"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                                <span class="material-symbols-outlined text-base">my_location</span>
                                Mi ubicación
                            </button>
                            <button type="button" id="clearLocationBtn"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition shadow-sm">
                                <span class="material-symbols-outlined text-base">delete</span>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ========== SECCIÓN 5: DATOS TÉCNICOS ========== --}}
                <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-5 space-y-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">settings</span>
                        Datos Técnicos
                        @if($technicalDataLoaded)
                            <span
                                class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Precargados
                                del cliente</span>
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500">Estos campos serán llenados por el técnico durante la instalación.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">person</span>
                                Nombre de perfil
                            </label>
                            <input type="text" wire:model="profile_name" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="Ej: Usuario1">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">key</span>
                                Contraseña de perfil
                            </label>
                            <input type="text" wire:model="profile_password" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="Contraseña">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">wifi</span>
                                Nombre wifi
                            </label>
                            <input type="text" wire:model="wifi_name" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="SSID">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">key</span>
                                Contraseña wifi
                            </label>
                            <input type="text" wire:model="wifi_password" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="Contraseña">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">lan</span>
                                MAC
                            </label>
                            <input type="text" wire:model="mac" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="00:00:00:00:00:00">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span
                                    class="material-symbols-outlined text-gray-400 text-sm">settings_input_antenna</span>
                                PON
                            </label>
                            <input type="text" wire:model="pon" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="PON">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">cable</span>
                                Mufa
                            </label>
                            <input type="text" wire:model="mufa" {{ !$canEditTech || $technicalDataLoaded ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"
                                placeholder="Número de mufa">
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
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('notes') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Botones de acción --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('work-orders.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal para crear cliente --}}
    @if($showClientModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-3xl max-h-[85vh] overflow-y-auto">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">person_add</span>
                            Nuevo Cliente
                        </h3>
                        <button type="button" wire:click="closeClientModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5">
                        <livewire:clients.client-form :key="$modalKey" />
                    </div>
                </div>
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
            if (typeof L === 'undefined') {
                console.warn('Leaflet no está cargado. El mapa no funcionará.');
                return;
            }

            var mapContainer = document.getElementById('map');
            if (!mapContainer) return;

            var map = L.map('map').setView([13.6929, -89.2182], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
            }).addTo(map);
            var marker = null;

            var lat = @json($latitude ?? null);
            var lng = @json($longitude ?? null);
            if (lat && lng) {
                map.setView([lat, lng], 15);
                marker = L.marker([lat, lng]).addTo(map);
            }

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
                    } else {
                        alert('Geolocalización no soportada');
                    }
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