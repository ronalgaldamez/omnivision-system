<div class="max-w-2xl mx-auto pb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Encabezado --}}
        <div
            class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 bg-blue-50 p-1.5 rounded-lg">work</span>
                    {{ $workOrder->code ?? 'OT-' . $workOrder->id }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5 ml-11">Detalle de la orden de trabajo</p>
            </div>
            <a href="{{ route('mobile.work-orders.list') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg font-medium">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver
            </a>
        </div>

        <div class="p-5 space-y-6">
            {{-- ========== DATOS DEL CLIENTE ========== --}}
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <span
                        class="material-symbols-outlined text-blue-600 bg-blue-50 p-1 rounded-lg text-xl">person</span>
                    <h2 class="text-base font-bold text-gray-800">Datos del Cliente</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">person</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Nombre</p>
                            <p class="text-gray-800 font-medium text-sm truncate">
                                {{ $workOrder->client->name ?? 'No especificado' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">fingerprint</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Documento</p>
                            <p class="text-gray-800 font-medium text-sm truncate">
                                @if ($workOrder->client->document_type && $workOrder->client->document_number)
                                    {{ strtoupper($workOrder->client->document_type) }}:
                                    {{ $workOrder->client->document_number }}
                                @else
                                    No especificado
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">call</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Teléfono</p>
                            <p class="text-gray-700 text-sm truncate">
                                @if ($workOrder->client && $workOrder->client->phones->isNotEmpty())
                                    @foreach ($workOrder->client->phones as $phone)
                                        {{ $phone->number }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    {{ $workOrder->client->phone ?? '—' }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">mail</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Correo</p>
                            <p class="text-gray-700 text-sm truncate">{{ $workOrder->client->email ?? '—' }}</p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100 sm:col-span-2">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">location_on</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Dirección</p>
                            <p class="text-gray-700 text-sm">{{ $workOrder->client->address ?? '—' }}</p>
                        </div>
                    </div>

                    @if ($workOrder->client->installation_address)
                        <div
                            class="flex items-center gap-3 p-3 bg-blue-50/50 rounded-xl border border-blue-100 sm:col-span-2">
                            <span
                                class="material-symbols-outlined text-blue-500 bg-white p-1.5 rounded-lg shadow-sm">home_pin</span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-blue-400 uppercase tracking-wider font-medium">Dir.
                                    instalación</p>
                                <p class="text-gray-800 text-sm font-medium">
                                    {{ $workOrder->client->installation_address }}
                                </p>
                            </div>
                        </div>
                    @endif
                    @if ($workOrder->client->service)
                        <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                            <span
                                class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">tv</span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Servicio</p>
                                <p class="text-gray-700 text-sm">{{ $workOrder->client->service }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($workOrder->client->nro_luz)
                        <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                            <span
                                class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">bolt</span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">N.° de luz</p>
                                <p class="text-gray-700 text-sm">{{ $workOrder->client->nro_luz }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($workOrder->latitude && $workOrder->longitude)
                        <div
                            class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100 sm:col-span-2">
                            <span
                                class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">explore</span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Coordenadas
                                </p>
                                <p class="text-gray-700 text-sm font-mono">{{ $workOrder->latitude }},
                                    {{ $workOrder->longitude }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ========== DATOS TÉCNICOS ========== --}}
            <section class="bg-gray-50/30 rounded-2xl p-4 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span
                            class="material-symbols-outlined text-orange-600 bg-orange-50 p-1 rounded-lg text-xl">settings</span>
                        <h2 class="text-base font-bold text-gray-800">Datos Técnicos</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($isDraft)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-[11px] font-medium">
                                <span class="material-symbols-outlined text-sm">edit_note</span>
                                Borrador
                            </span>
                        @endif
                        @if ($canEditTech && !$isEditing)
                            <button type="button" wire:click="enableEditing"
                                class="px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-amber-600 transition inline-flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                Editar
                            </button>
                        @endif
                    </div>
                </div>
                <form wire:submit.prevent="saveTechnicalData" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Campos del formulario --}}
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">person</span>Nombre de
                                perfil *
                            </label>
                            <input type="text" wire:model.live="profile_name"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                            @error('profile_name')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">key</span>Contraseña
                                perfil *
                            </label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" wire:model.live="profile_password"
                                    {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                    class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                                <button type="button" x-on:click="show = !show"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                                    tabindex="-1" {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}>
                                    <span class="material-symbols-outlined text-sm"
                                        x-text="show ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            @error('profile_password')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">wifi</span>Nombre wifi *
                            </label>
                            <input type="text" wire:model.live="wifi_name"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                            @error('wifi_name')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">key</span>Contraseña wifi
                                *
                            </label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" wire:model.live="wifi_password"
                                    {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                    class="w-full px-3 py-2 pr-10 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                                <button type="button" x-on:click="show = !show"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                                    tabindex="-1" {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}>
                                    <span class="material-symbols-outlined text-sm"
                                        x-text="show ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            @error('wifi_password')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">lan</span>MAC *
                            </label>
                            <input type="text" wire:model.live="mac" x-data
                                x-on:input="
                                    let raw = $el.value.replace(/[^0-9a-fA-F]/g, '').slice(0,12);
                                    let formatted = '';
                                    for (let i = 0; i < raw.length; i += 2) {
                                        if (i > 0) formatted += ':';
                                        formatted += raw.substring(i, i+2);
                                    }
                                    if ($el.value !== formatted) {
                                        $el.value = formatted;
                                        $el.setSelectionRange(formatted.length, formatted.length);
                                        $el.dispatchEvent(new Event('input', { bubbles: true }));
                                    }
                                " placeholder="00:00:00:00:00:00" maxlength="17"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                            @error('mac')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span
                                    class="material-symbols-outlined text-gray-400 text-sm">settings_input_antenna</span>PON
                                *
                            </label>
                            <input type="text" wire:model.live="pon"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                            @error('pon')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">cable</span>Mufa *
                            </label>
                            <input type="text" wire:model.live="mufa"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                            @error('mufa')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span
                                    class="material-symbols-outlined text-gray-400 text-sm">calendar_today</span>Fecha
                                instalación *
                            </label>
                            <input type="date" wire:model.live="installation_date"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                            @error('installation_date')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">explore</span>Latitud *
                            </label>
                            <input type="text" wire:model.live="latitude"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"
                                placeholder="14.105025">
                            @error('latitude')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-sm">explore</span>Longitud *
                            </label>
                            <input type="text" wire:model.live="longitude"
                                {{ !$canEditTech || !$isEditing ? 'disabled' : '' }}
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm disabled:bg-gray-100/80 disabled:text-gray-500 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"
                                placeholder="-89.148894">
                            @error('longitude')
                                <span class="text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- MAPA siempre visible, con interacción condicional --}}
                    <div class="mt-3">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-sm">map</span>Mapa
                        </label>
                        <div id="map" style="height: 200px; width: 100%;" data-latitude="{{ $latitude }}"
                            data-longitude="{{ $longitude }}"
                            data-editable="{{ $canEditTech && $isEditing ? 'true' : 'false' }}"
                            class="rounded-xl border border-gray-200 shadow-inner {{ !$canEditTech || !$isEditing ? 'opacity-75 pointer-events-none' : '' }}">
                        </div>
                        @if ($canEditTech && $isEditing)
                            <div class="flex gap-2 mt-2">
                                <button type="button" id="getLocationBtn"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                                    <span class="material-symbols-outlined text-sm">my_location</span>Mi ubicación
                                </button>
                                <button type="button" id="clearLocationBtn"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                                    <span class="material-symbols-outlined text-sm">delete</span>Limpiar
                                </button>
                            </div>
                        @endif
                    </div>

                    @if ($canEditTech && $isEditing)
                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">save</span>Guardar datos técnicos
                            </button>
                        </div>
                    @endif
                </form>
            </section>

            {{-- ========== INFORMACIÓN DEL TICKET ========== --}}
            @if ($workOrder->ticket)
                <section>
                    <div class="flex items-center gap-2 mb-3">
                        <span
                            class="material-symbols-outlined text-purple-600 bg-purple-50 p-1 rounded-lg text-xl">confirmation_number</span>
                        <h2 class="text-base font-bold text-gray-800">Ticket #{{ $workOrder->ticket->id }}</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                            <span
                                class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">person</span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Creado por
                                </p>
                                <p class="text-gray-700 text-sm">{{ $workOrder->ticket->createdBy->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                            <span
                                class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">source</span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Origen</p>
                                <p class="text-gray-700 text-sm">{{ $this->getTicketOriginLabel() ?? '—' }}</p>
                            </div>
                        </div>
                        @if ($workOrder->ticket->priority)
                            <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                                <span
                                    class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">flag</span>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Prioridad
                                    </p>
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($workOrder->ticket->priority)
                                            @case('P1')
                                                bg-red-100 text-red-700
                                            @break
                                            @case('P2')
                                                bg-orange-100 text-orange-700
                                            @break
                                            @case('P3')
                                                bg-blue-100 text-blue-700
                                            @break
                                            @case('P4')
                                                bg-gray-100 text-gray-600
                                            @break
                                        @endswitch">
                                        {{ $workOrder->ticket->priority }} -
                                        {{ ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja'][$workOrder->ticket->priority] ?? $workOrder->ticket->priority }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if ($workOrder->ticket->description)
                            <div
                                class="flex items-start gap-3 p-3 bg-red-50/50 rounded-xl border border-red-100 sm:col-span-2">
                                <span
                                    class="material-symbols-outlined text-red-400 bg-white p-1.5 rounded-lg shadow-sm">description</span>
                                <div>
                                    <p class="text-[10px] text-red-400 uppercase tracking-wider font-medium">Problema
                                        Reportado</p>
                                    <p class="text-gray-800 text-sm whitespace-pre-wrap">
                                        {{ $workOrder->ticket->description }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- ========== INFORMACIÓN DE LA OT ========== --}}
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <span
                        class="material-symbols-outlined text-indigo-600 bg-indigo-50 p-1 rounded-lg text-xl">engineering</span>
                    <h2 class="text-base font-bold text-gray-800">Información de la OT</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">person</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Creada por</p>
                            @php
                                $creator = $workOrder->createdBy;
                                $rol = $creator?->getRoleNames()?->first();
                            @endphp
                            <p class="text-gray-700 text-sm">
                                {{ $creator?->name ?? 'N/A' }}{{ $rol ? ' (' . strtoupper(str_replace('_', ' ', $rol)) . ')' : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">calendar_month</span>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Fecha programada
                            </p>
                            <p class="text-gray-700 text-sm">{{ $workOrder->scheduled_date?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">flag</span>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Estado</p>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'in_progress' => 'bg-blue-50 text-blue-700',
                                    'paused' => 'bg-gray-100 text-gray-600',
                                    'completed' => 'bg-emerald-50 text-emerald-700',
                                ];
                                $statusIcons = [
                                    'pending' => 'schedule',
                                    'in_progress' => 'autorenew',
                                    'paused' => 'pause_circle',
                                    'completed' => 'check_circle',
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'in_progress' => 'En progreso',
                                    'paused' => 'Pausada',
                                    'completed' => 'Completada',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$workOrder->status] ?? 'bg-red-50 text-red-700' }}">
                                <span
                                    class="material-symbols-outlined text-sm">{{ $statusIcons[$workOrder->status] ?? 'cancel' }}</span>
                                {{ $statusLabels[$workOrder->status] ?? 'Cancelada' }}
                            </span>
                        </div>
                    </div>
                    @if ($workOrder->started_at || $workOrder->accumulated_seconds > 0 || $workOrder->completed_date)
                        <div class="flex items-start gap-3 p-3 bg-blue-50/50 rounded-xl border border-blue-100 sm:col-span-2">
                            <span class="material-symbols-outlined text-blue-500 bg-white p-1.5 rounded-lg shadow-sm flex-shrink-0">schedule</span>
                            <div class="w-full min-w-0">
                                <p class="text-[10px] text-blue-400 uppercase tracking-wider font-medium mb-2">Tiempo de trabajo</p>
                                
                                {{-- Inicio --}}
                                @if($workOrder->started_at)
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600">Inicio:</span>
                                    <span class="text-gray-800 font-medium">{{ $workOrder->started_at->format('d/m/Y h:i A') }}</span>
                                </div>
                                @endif

                                {{-- Pausas (solo si hay) --}}
                                @if(isset($pauses) && count($pauses) > 0)
                                    <div class="mt-2 pt-2 border-t border-blue-200">
                                        <span class="text-xs text-gray-500 font-medium">Pausas:</span>
                                        @foreach($pauses as $pause)
                                            <div class="flex items-center justify-between text-xs mt-1 ml-2">
                                                <span class="text-gray-600">
                                                    {{ $pause->paused_at->format('h:i A') }} - 
                                                    {{ $pause->resumed_at ? $pause->resumed_at->format('h:i A') : 'En pausa' }}
                                                </span>
                                                @if($pause->resumed_at)
                                                <span class="text-gray-500 font-mono">
                                                    ({{ gmdate('i:s', $pause->paused_at->diffInSeconds($pause->resumed_at)) }})
                                                </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Fin o En curso --}}
                                @if($workOrder->completed_date)
                                <div class="flex items-center justify-between text-sm mt-2 pt-2 border-t border-blue-200">
                                    <span class="text-gray-600">Fin:</span>
                                    <span class="text-gray-800 font-medium">{{ $workOrder->completed_date->format('d/m/Y h:i A') }}</span>
                                </div>
                                @elseif($workOrder->status === 'in_progress')
                                <div class="flex items-center justify-between text-sm mt-2 pt-2 border-t border-blue-200">
                                    <span class="text-gray-600">En curso:</span>
                                    <span class="text-blue-700 font-bold" wire:poll.1s="updateTimers">{{ gmdate('H:i:s', $totalWorkedSeconds) }}</span>
                                </div>
                                @elseif($workOrder->status === 'paused')
                                <div class="flex items-center justify-between text-sm mt-2 pt-2 border-t border-blue-200">
                                    <span class="text-gray-600">En pausa</span>
                                    <span class="text-gray-800 font-medium">—</span>
                                </div>
                                @endif

                                {{-- Total trabajado --}}
                                <div class="mt-2 pt-2 border-t border-blue-200 flex items-center justify-between">
                                    <span class="text-xs text-blue-600 font-medium">Total trabajado</span>
                                    <span class="text-sm font-bold text-blue-700">
                                        @if($workOrder->status === 'in_progress')
                                            <span wire:poll.1s="updateTimers">{{ gmdate('H:i:s', $totalWorkedSeconds) }}</span>
                                        @else
                                            {{ gmdate('H:i:s', $workOrder->accumulated_seconds) }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div
                        class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-xl border border-gray-100 sm:col-span-2">
                        <span
                            class="material-symbols-outlined text-gray-400 bg-white p-1.5 rounded-lg shadow-sm">sticky_note_2</span>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Notas</p>
                            <p class="text-gray-700 text-sm">{{ $workOrder->notes ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Productos sugeridos --}}
            @if ($workOrder->products?->count())
                <section>
                    <div class="flex items-center gap-2 mb-3">
                        <span
                            class="material-symbols-outlined text-teal-600 bg-teal-50 p-1 rounded-lg text-xl">inventory_2</span>
                        <h2 class="text-base font-bold text-gray-800">Productos sugeridos</h2>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-100 shadow-sm bg-white">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th
                                        class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto</th>
                                    <th
                                        class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($workOrder->products as $item)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-2.5 text-gray-800">{{ $item->product->name }}</td>
                                        <td class="px-4 py-2.5 text-center font-mono text-gray-700">
                                            {{ $item->quantity }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            {{-- Enlace Google Maps --}}
            @if ($workOrder->latitude && $workOrder->longitude)
                <a href="https://www.google.com/maps?q={{ $workOrder->latitude }},{{ $workOrder->longitude }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-xl shadow-sm hover:bg-green-700 transition w-full justify-center sm:w-auto">
                    <span class="material-symbols-outlined text-base">map</span>
                    Ver en Google Maps
                </a>
            @endif

            {{-- Botones de acción --}}
            @if (!in_array($workOrder->status, ['completed', 'cancelled']))
                <div class="space-y-2.5 pt-2">
                    @if ($workOrder->status === 'pending' && $hasOpenRequisition && !$hasAnotherInProgress)
                        <button wire:click="promptStartWorkOrder"
                            class="w-full px-5 py-3 bg-amber-500 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-amber-600 transition inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">play_arrow</span>Iniciar OT
                        </button>
                    @elseif($workOrder->status === 'pending' && !$hasOpenRequisition)
                        @if ($technicianHasOpenRequisition)
                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                                <span class="material-symbols-outlined text-amber-600">warning</span>
                                <div>
                                    <p class="text-sm font-medium text-amber-800">OT no vinculada</p>
                                    <p class="text-xs text-amber-700">Selecciona OTs para vincular a tu requisición
                                        activa.</p>
                                </div>
                            </div>
                            <button wire:click="openWorkOrderSelectionModal"
                                class="w-full px-5 py-3 bg-purple-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-purple-700 transition">
                                Vincular OTs a requisición activa
                            </button>
                        @else
                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                                <span class="material-symbols-outlined text-amber-600">warning</span>
                                <div>
                                    <p class="text-sm font-medium text-amber-800">Requisición pendiente</p>
                                    <p class="text-xs text-amber-700">Debes crear una requisición de material para esta
                                        OT.</p>
                                </div>
                            </div>
                            <a href="{{ route('technician.requisitions.create') }}"
                                class="w-full px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-blue-700 transition inline-flex items-center justify-center gap-2">
                                Crear requisición de material
                            </a>
                        @endif
                    @elseif($workOrder->status === 'pending' && $hasAnotherInProgress)
                        <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                            <span class="material-symbols-outlined text-amber-600">warning</span>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Ya tienes otra OT en progreso</p>
                                <p class="text-xs text-amber-700">Finaliza o pausa esa OT antes de iniciar esta.</p>
                            </div>
                        </div>
                    @endif

                    @if ($workOrder->status === 'in_progress')
                        <button wire:click="promptPauseWorkOrder"
                            class="w-full px-5 py-3 bg-gray-500 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-600 transition inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">pause</span>Pausar OT
                        </button>
                        @if (auth()->user()->can('complete work_orders') && $canEditTech && !$isEditing && $technicalDataComplete)
                            <button wire:click="promptCompleteWorkOrder"
                                class="w-full px-5 py-3 bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-emerald-700 transition inline-flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">check_circle</span>Completar trabajo
                            </button>
                        @elseif(auth()->user()->can('complete work_orders') && $canEditTech && !$isEditing && !$technicalDataComplete)
                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                                <span class="material-symbols-outlined text-amber-600">info</span>
                                <div>
                                    <p class="text-sm font-medium text-amber-800">Datos técnicos incompletos</p>
                                    <p class="text-xs text-amber-700">Completá y guardá los datos técnicos antes de
                                        finalizar la OT.</p>
                                </div>
                            </div>
                        @endif
                    @endif
                    @if ($workOrder->status === 'paused')
                        <button wire:click="promptResumeWorkOrder"
                            class="w-full px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-blue-700 transition inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">replay</span>Reanudar OT
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de confirmación --}}
    @if ($confirmingAction)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">{{ $confirmingMessage }}</p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="executeConfirmedAction"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Sí, continuar
                        </button>
                        <button @click="open = false" wire:click="cancelConfirmation"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de consumo de material --}}
    @if ($showConsumptionModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Material Utilizado en {{ $workOrder->code ?? 'OT-' . $workOrder->id }}
                        </h3>
                        <button wire:click="closeConsumptionModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        @if (count($availableProducts) > 0)
                            <p class="text-sm text-gray-600">Indica cuánto material usaste de tu requisición para esta
                                OT.</p>
                            <div class="space-y-3">
                                @foreach ($availableProducts as $index => $product)
                                    <div class="flex items-center justify-between gap-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-800">
                                                {{ $product['product_name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">Disponible: {{ $product['available'] }}
                                            </p>
                                        </div>
                                        <input type="number" min="0" max="{{ $product['available'] }}"
                                            step="any"
                                            wire:model.defer="consumptionQuantities.{{ $index }}"
                                            class="w-20 text-center rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm py-1.5">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No tienes productos disponibles en tu requisición activa.
                            </p>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="saveConsumption"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Guardar consumo
                        </button>
                        <button wire:click="closeConsumptionModal"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Omitir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de selección de OTs para vincular --}}
    @if ($showWorkOrderSelectionModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">playlist_add_check</span>
                            Selecciona OTs para Vincular
                        </h3>
                        <button wire:click="closeWorkOrderSelectionModal"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">Marca las Órdenes de Trabajo que deseas agregar a tu
                            requisición activa.</p>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @forelse($eligibleWorkOrders as $wo)
                                <label
                                    class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                                    <input type="checkbox" value="{{ $wo['id'] }}"
                                        wire:model="selectedWorkOrdersForLink"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                    <span class="text-sm text-gray-700">{{ $wo['name'] }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No hay OTs pendientes sin vincular.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="linkSelectedWorkOrders"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Vincular seleccionadas
                        </button>
                        <button wire:click="closeWorkOrderSelectionModal"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Toast --}}
    <div x-data="{ toasts: [] }" x-on:show-toast.window="toasts.push({ id: Date.now() + Math.random(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => toasts.shift(), 3500)"
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
                <div x-show="toast.type === 'info'"
                    class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 whitespace-nowrap">
                    <span class="material-symbols-outlined">info</span>
                    <span x-text="toast.message" class="text-sm font-medium"></span>
                </div>
            </div>
        </template>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>

@push('scripts')
    <script>
        let mapInstance = null;
        let leafletMarker = null;

        function setMapInteraction(editable) {
            if (!mapInstance) return;
            if (editable) {
                mapInstance.dragging.enable();
                mapInstance.touchZoom.enable();
                mapInstance.doubleClickZoom.enable();
                mapInstance.scrollWheelZoom.enable();
            } else {
                mapInstance.dragging.disable();
                mapInstance.touchZoom.disable();
                mapInstance.doubleClickZoom.disable();
                mapInstance.scrollWheelZoom.disable();
                mapInstance.off('click');
            }
        }

        function initLeafletMap() {
            const container = document.getElementById('map');
            if (!container) return;

            if (mapInstance) {
                mapInstance.remove();
                mapInstance = null;
            }

            const lat = container.dataset.latitude ? parseFloat(container.dataset.latitude) : null;
            const lng = container.dataset.longitude ? parseFloat(container.dataset.longitude) : null;
            const editable = container.dataset.editable === 'true';

            const map = L.map(container).setView([14.105135, -89.148899], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
            }).addTo(map);

            if (lat && lng) {
                map.setView([lat, lng], 15);
                leafletMarker = L.marker([lat, lng]).addTo(map);
            }

            if (editable) {
                map.on('click', function(e) {
                    if (leafletMarker) map.removeLayer(leafletMarker);
                    leafletMarker = L.marker(e.latlng).addTo(map);
                    @this.set('latitude', e.latlng.lat.toFixed(6));
                    @this.set('longitude', e.latlng.lng.toFixed(6));
                });
            }

            mapInstance = map;
            setMapInteraction(editable);

            const getLocationBtn = document.getElementById('getLocationBtn');
            const clearLocationBtn = document.getElementById('clearLocationBtn');
            if (getLocationBtn) {
                getLocationBtn.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            const pos = [position.coords.latitude, position.coords.longitude];
                            map.setView(pos, 15);
                            if (leafletMarker) map.removeLayer(leafletMarker);
                            leafletMarker = L.marker(pos).addTo(map);
                            @this.set('latitude', pos[0].toFixed(6));
                            @this.set('longitude', pos[1].toFixed(6));
                        });
                    } else {
                        alert('Geolocalización no soportada');
                    }
                });
            }
            if (clearLocationBtn) {
                clearLocationBtn.addEventListener('click', function() {
                    if (leafletMarker) {
                        map.removeLayer(leafletMarker);
                        leafletMarker = null;
                    }
                    @this.set('latitude', null);
                    @this.set('longitude', null);
                    map.setView([14.105135, -89.148899], 13);
                });
            }
        }

        document.addEventListener('livewire:initialized', function() {
            setTimeout(initLeafletMap, 100);
        });

        Livewire.hook('morph.updated', ({
            el,
            component
        }) => {
            if (el.querySelector('#map')) {
                setTimeout(initLeafletMap, 50);
            }
        });

        Livewire.hook('morph.updating', ({
            el,
            component
        }) => {
            const mapContainer = el.querySelector('#map');
            if (mapContainer && mapInstance) {
                mapInstance.remove();
                mapInstance = null;
            }
        });
    </script>
@endpush