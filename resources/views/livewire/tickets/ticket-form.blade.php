<div class="max-w-3xl mx-auto">
    <x-ui.card icon="confirmation_number" title="{{ $ticketId ? 'Editar Ticket' : 'Nuevo Ticket' }}" subtitle="{{ $ticketId ? 'Modificar solicitud de servicio' : 'Generar una nueva solicitud de servicio' }}">
        <x-slot:headerActions>
            @if ($ticketOpened)
                <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-1.5">
                    <span class="material-symbols-outlined text-green-600 text-lg">timer</span>
                    <span class="text-sm font-mono font-medium text-green-800" wire:poll.1s="updateElapsedSeconds">
                        {{ gmdate('H:i:s', $elapsedSeconds) }}
                    </span>
                </div>
            @endif
            @if ($isDraft)
                <x-ui.badge variant="warning">Borrador</x-ui.badge>
            @endif
            <a href="{{ route('tickets.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-700 hover:text-gray-900 font-medium transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver al listado
            </a>
        </x-slot:headerActions>

        {{-- Tipo de servicio + Abrir Ticket (pre-apertura) --}}
        @if (!$ticketId && !$ticketOpened)
            <div class="pb-5 border-b border-gray-100 mb-6">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">handyman</span>
                    Tipo de servicio <span class="text-red-500">*</span>
                </label>
                @if ($service_type_id)
                    <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                        <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $serviceTypes->firstWhere('id', $service_type_id)?->name }}</p>
                            @php $selSt = $serviceTypes->firstWhere('id', $service_type_id); @endphp
                            @if($selSt?->requires_contract)
                                <x-ui.badge variant="success" size="sm" class="mt-1">Requiere Contrato</x-ui.badge>
                            @elseif($selSt?->requires_potential)
                                <x-ui.badge variant="warning" size="sm" class="mt-1">Cliente Potencial</x-ui.badge>
                            @elseif($selSt?->requires_ot)
                                <x-ui.badge variant="warning" size="sm" class="mt-1">Requiere OT</x-ui.badge>
                            @elseif($selSt?->requires_noc)
                                <x-ui.badge variant="info" size="sm" class="mt-1">Requiere NOC</x-ui.badge>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" wire:click="clearServiceType"
                                class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar tipo de servicio">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <x-ui.input type="text" wire:model.live.debounce.300ms="serviceTypeSearch"
                                placeholder="Buscar tipo de servicio..." icon="search" />
                            @if (count($serviceTypeResults) > 0)
                                <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                    @foreach ($serviceTypeResults as $st)
                                        <li wire:click="selectServiceType({{ $st->id }})"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                            <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ str_replace('_', ' ', $st->name) }}</span>
                                            @if($st->requires_contract)
                                                <x-ui.badge variant="success" size="sm">Requiere Contrato</x-ui.badge>
                                            @elseif($st->requires_potential)
                                                <x-ui.badge variant="warning" size="sm">Cliente Potencial</x-ui.badge>
                                            @elseif($st->requires_ot)
                                                <x-ui.badge variant="warning" size="sm">Requiere OT</x-ui.badge>
                                            @elseif($st->requires_noc)
                                                <x-ui.badge variant="info" size="sm">Requiere NOC</x-ui.badge>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <button type="button" wire:click="openServiceTypeModal"
                            class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                            title="Ver todos los tipos de servicio">
                            <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                            <span class="hidden sm:inline">Ver todos</span>
                        </button>
                    </div>
                @endif
                @error('service_type_id')
                    <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            {{-- Base de Conocimiento --}}
            @if (count($knowledgeArticles) > 0)
                <div x-data="{ openArticle: null, filter: 'all' }" wire:key="kb-pre-{{ $service_type_id }}"
                    class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-3 mb-6">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-sm">menu_book</span>
                        Información Técnica
                    </h3>

                    @php $categories = $knowledgeArticles->pluck('category')->filter()->unique(); @endphp
                    @if ($categories->count() > 1)
                        <div class="flex flex-wrap gap-1">
                            <button type="button" @click="filter = 'all'"
                                :class="filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'"
                                class="px-2.5 py-1 rounded-full text-xs font-medium transition">Todas</button>
                            @foreach ($categories as $cat)
                                <button type="button" @click="filter = '{{ $cat }}'"
                                    :class="filter === '{{ $cat }}' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'"
                                    class="px-2.5 py-1 rounded-full text-xs font-medium transition">{{ $cat }}</button>
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach ($knowledgeArticles as $article)
                            <div x-show="filter === 'all' || filter === '{{ $article->category }}'"
                                class="bg-white rounded-lg border border-gray-200">
                                <button type="button"
                                    @click="openArticle = (openArticle === {{ $article->id }} ? null : {{ $article->id }})"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span>{{ $article->title }}</span>
                                        @if ($article->priority)
                                            <x-ui.badge :variant="match($article->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                                {{ $article->priority }} - @php $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja']; @endphp
                                                {{ $priorityLabels[$article->priority] ?? $article->priority }}
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                    <span class="material-symbols-outlined text-base transition-transform flex-shrink-0 ml-2"
                                        :class="openArticle === {{ $article->id }} ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                <div x-show="openArticle === {{ $article->id }}" x-collapse
                                    class="px-3 pb-3 text-xs text-gray-600 whitespace-pre-line">
                                    {{ $article->content }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-6 flex justify-center">
                <x-ui.button type="button" variant="success" icon="play_arrow" wire:click="confirmOpen" size="lg"
                    :disabled="!$service_type_id">
                    Abrir Ticket
                </x-ui.button>
            </div>
        @endif

        <form wire:submit.prevent="promptSave" class="space-y-6">
            @if ($ticketOpened || $ticketId)
            {{-- Tipo de servicio (cambiable incluso después de abrir) --}}
            @if ($ticketOpened)
                <div class="pb-5 border-b border-gray-100">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-sm">handyman</span>
                        Tipo de servicio
                    </label>
                    @if ($service_type_id)
                        <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $serviceTypes->firstWhere('id', $service_type_id)?->name ?? '—' }}</p>
                                @php $selSt2 = $serviceTypes->firstWhere('id', $service_type_id); @endphp
                                @if($selSt2?->requires_contract)
                                    <x-ui.badge variant="success" size="sm" class="mt-1">Requiere Contrato</x-ui.badge>
                                @elseif($selSt2?->requires_potential)
                                    <x-ui.badge variant="warning" size="sm" class="mt-1">Cliente Potencial</x-ui.badge>
                                @elseif($selSt2?->requires_ot)
                                    <x-ui.badge variant="warning" size="sm" class="mt-1">Requiere OT</x-ui.badge>
                                @elseif($selSt2?->requires_noc)
                                    <x-ui.badge variant="info" size="sm" class="mt-1">Requiere NOC</x-ui.badge>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" wire:click="openServiceTypeModal"
                                    class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
                            </div>
                        </div>
                    @else
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <x-ui.input type="text" wire:model.live.debounce.300ms="serviceTypeSearch"
                                    placeholder="Buscar tipo de servicio..." icon="search" />
                                @if (count($serviceTypeResults) > 0)
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach ($serviceTypeResults as $st)
                                        <li wire:click="selectServiceType({{ $st->id }})"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                            <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ str_replace('_', ' ', $st->name) }}</span>
                                            @if($st->requires_contract)
                                                <x-ui.badge variant="success" size="sm">Requiere Contrato</x-ui.badge>
                                            @elseif($st->requires_potential)
                                                <x-ui.badge variant="warning" size="sm">Cliente Potencial</x-ui.badge>
                                            @elseif($st->requires_ot)
                                                <x-ui.badge variant="warning" size="sm">Requiere OT</x-ui.badge>
                                            @elseif($st->requires_noc)
                                                <x-ui.badge variant="info" size="sm">Requiere NOC</x-ui.badge>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <button type="button" wire:click="openServiceTypeModal"
                                class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                                title="Ver todos los tipos de servicio">
                                <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                                <span class="hidden sm:inline">Ver todos</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Cliente --}}
            <div class="pb-5 border-b border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">person</span>
                    Cliente <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <x-ui.input type="text" wire:model.live.debounce.300ms="clientSearch"
                            placeholder="Buscar por nombre o teléfono..." icon="search"
                            :disabled="!$editingEnabled" />
                        @if (count($clientSearchResults) > 0)
                            <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                @foreach ($clientSearchResults as $client)
                                    <li wire:click="selectClient({{ $client->id }}, '{{ $client->name }}', '{{ $client->phone }}')"
                                        class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer transition text-sm flex items-center justify-between">
                                        <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @if ($editingEnabled)
                        <x-ui.button type="button" :variant="$selectedClient ? 'warning' : 'success'" :icon="$selectedClient ? 'edit' : 'person_add'" wire:click="openClientModal({{ $selectedClient?->id ?? 'null' }})">
                            {{ $selectedClient ? 'Editar' : 'Nuevo' }}
                        </x-ui.button>
                    @endif
                </div>
                @error('client_id')
                    <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                @enderror

                @if ($selectedClient)
                    @php
                        $clientBranch = $selectedClient->branch;
                        $clientZone = $selectedClient->zone;
                        $clientPlan = $selectedClient->plan;
                        $clientPhones = $selectedClient->phones;
                    @endphp
                    <div class="mt-4 bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-gray-500 text-sm">info</span>
                            Datos del cliente seleccionado
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
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
                            @if ($selectedClient->document_type && $selectedClient->document_number)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">fingerprint</span>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ strtoupper($selectedClient->document_type) }}</p>
                                        <p class="text-gray-800">{{ $selectedClient->document_number }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($clientBranch)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">business</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Sucursal</p>
                                        <p class="text-gray-800 font-medium">{{ $clientBranch->name }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($clientZone)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">map</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Zona</p>
                                        <p class="text-gray-800">{{ $clientZone->name }}</p>
                                        @if($clientZone->parent)
                                            <p class="text-[10px] text-gray-400">{{ $clientZone->parent->name }}{{ $clientZone->parent->parent ? ' → ' . $clientZone->parent->parent->name : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($clientPlan)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">assignment</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Plan</p>
                                        <p class="text-gray-800">{{ $clientPlan->name }}</p>
                                        @if($clientPlan->speed)
                                            <p class="text-[10px] text-gray-400">{{ $clientPlan->speed }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->email)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">mail</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Correo</p>
                                        <p class="text-gray-800">{{ $selectedClient->email }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->address)
                                <div class="flex items-start gap-2 col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Dirección</p>
                                        <p class="text-gray-800">{{ $selectedClient->address }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->installation_address)
                                <div class="flex items-start gap-2 col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">home_pin</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Instalación</p>
                                        <p class="text-gray-800">{{ $selectedClient->installation_address }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($selectedClient->service)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">tv</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Servicio</p>
                                        <p class="text-gray-800">{{ $selectedClient->service }}</p>
                                    </div>
                                </div>
                            @endif
                            @if(count($quickReferencePlans) > 0)
                                @php
                                    $internetPlans = $quickReferencePlans->where('service_type', 'internet');
                                    $comboPlans = $quickReferencePlans->where('service_type', 'internet_cable');
                                @endphp
                                <div class="col-span-full mt-2 pt-3 border-t border-gray-200">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="material-symbols-outlined text-amber-600 text-sm">sell</span>
                                        <span class="text-xs font-semibold text-amber-800 uppercase tracking-wide">Planes de Referencia</span>
                                        @if($isPotentialClient)
                                            <x-ui.badge variant="warning" size="sm">Cliente Potencial</x-ui.badge>
                                        @endif
                                    </div>

                                    {{-- Internet --}}
                                    @if($internetPlans->count() > 0)
                                        <div class="mb-3">
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <span class="material-symbols-outlined text-blue-600 text-sm">wifi</span>
                                                <span class="text-xs font-semibold text-blue-800 uppercase tracking-wide">Internet</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($internetPlans as $plan)
                                                    @php
                                                        $refPrice = $zone_id
                                                            ? optional(\App\Models\Zone::find($zone_id))->getEffectivePriceForPlan($plan)
                                                            : $plan->base_price;
                                                    @endphp
                                                    <button type="button" wire:click="addPlanReference({{ $plan->id }})"
                                                        class="group relative flex flex-col items-start gap-1 px-3 py-2 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 hover:border-blue-400 transition text-left min-w-[140px]">
                                                        <span class="text-xs font-semibold text-blue-900 group-hover:text-blue-950">{{ $plan->name }}</span>
                                                        <span class="text-[10px] text-blue-700 leading-tight">
                                                            @if($plan->speed)⚡ {{ $plan->speed }} @endif
                                                        </span>
                                                        <span class="text-xs font-bold text-blue-800">${{ number_format($refPrice ?? $plan->base_price, 2) }}</span>
                                                        <span class="absolute inset-0 rounded-lg ring-1 ring-inset ring-blue-300/0 group-hover:ring-blue-400/50 transition"></span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Internet + Cable --}}
                                    @if($comboPlans->count() > 0)
                                        <div>
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <span class="material-symbols-outlined text-orange-600 text-sm">live_tv</span>
                                                <span class="text-xs font-semibold text-orange-800 uppercase tracking-wide">Internet + Cable</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($comboPlans as $plan)
                                                    @php
                                                        $refPrice = $zone_id
                                                            ? optional(\App\Models\Zone::find($zone_id))->getEffectivePriceForPlan($plan)
                                                            : $plan->base_price;
                                                    @endphp
                                                    <button type="button" wire:click="addPlanReference({{ $plan->id }})"
                                                        class="group relative flex flex-col items-start gap-1 px-3 py-2 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 hover:border-orange-400 transition text-left min-w-[140px]">
                                                        <span class="text-xs font-semibold text-orange-900 group-hover:text-orange-950">{{ $plan->name }}</span>
                                                        <span class="text-[10px] text-orange-700 leading-tight">
                                                            @if($plan->speed)⚡ {{ $plan->speed }} @endif
                                                            @if($plan->channels) 📺 {{ $plan->channels }} canales @endif
                                                        </span>
                                                        <span class="text-xs font-bold text-orange-800">${{ number_format($refPrice ?? $plan->base_price, 2) }}</span>
                                                        <span class="absolute inset-0 rounded-lg ring-1 ring-inset ring-orange-300/0 group-hover:ring-orange-400/50 transition"></span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <p class="text-[10px] text-gray-400 mt-2">Hacé clic en un plan para agregarlo como referencia en la descripción.</p>
                                </div>
                            @endif
                            @if ($selectedClient->nro_luz)
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">bolt</span>
                                    <div>
                                        <p class="text-xs text-gray-500">N.° de luz</p>
                                        <p class="text-gray-800">{{ $selectedClient->nro_luz }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($clientPhones && $clientPhones->count() > 0)
                                <div class="flex items-start gap-2 col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">phonelink</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Teléfonos adicionales</p>
                                        @foreach($clientPhones as $cp)
                                            <p class="text-gray-800">{{ $cp->number }} <span class="text-[10px] text-gray-400">({{ $cp->type }})</span></p>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Descripción --}}
            <x-ui.textarea wire:model.live="description" icon="edit_note" rows="3" placeholder="Describe el problema o servicio..."
                :disabled="!$editingEnabled" required />

            @if ($ticketId && !$ticketOpened)
            {{-- Tipo de servicio (editable solo en edición) --}}
            <div class="pb-5 border-b border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">handyman</span>
                    Tipo de servicio <span class="text-red-500">*</span>
                </label>
                @if ($service_type_id)
                    <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                        <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $serviceTypes->firstWhere('id', $service_type_id)?->name }}</p>
                            @php $selSt3 = $serviceTypes->firstWhere('id', $service_type_id); @endphp
                            @if($selSt3?->requires_contract)
                                <x-ui.badge variant="success" size="sm" class="mt-1">Requiere Contrato</x-ui.badge>
                            @elseif($selSt3?->requires_potential)
                                <x-ui.badge variant="warning" size="sm" class="mt-1">Cliente Potencial</x-ui.badge>
                            @elseif($selSt3?->requires_ot)
                                <x-ui.badge variant="warning" size="sm" class="mt-1">Requiere OT</x-ui.badge>
                            @elseif($selSt3?->requires_noc)
                                <x-ui.badge variant="info" size="sm" class="mt-1">Requiere NOC</x-ui.badge>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" wire:click="openServiceTypeModal"
                                class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
                            <button type="button" wire:click="clearServiceType"
                                class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar tipo de servicio">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <x-ui.input type="text" wire:model.live.debounce.300ms="serviceTypeSearch"
                                placeholder="Buscar tipo de servicio..." icon="search" />
                            @if (count($serviceTypeResults) > 0)
                                <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                    @foreach ($serviceTypeResults as $st)
                                        <li wire:click="selectServiceType({{ $st->id }})"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between group">
                                            <span class="font-medium text-gray-800 group-hover:text-blue-700">{{ str_replace('_', ' ', $st->name) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <button type="button" wire:click="openServiceTypeModal"
                            class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                            title="Ver todos los tipos de servicio">
                            <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                            <span class="hidden sm:inline">Ver todos</span>
                        </button>
                    </div>
                @endif
                @error('service_type_id')
                    <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                @enderror
            </div>
            @endif

            {{-- Base de Conocimiento --}}
            @if (count($knowledgeArticles) > 0)
                <div x-data="{ openArticle: null, filter: 'all' }" wire:key="kb-{{ $service_type_id }}"
                    class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-3">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-sm">menu_book</span>
                        Información Técnica
                    </h3>

                    @php $categories = $knowledgeArticles->pluck('category')->filter()->unique(); @endphp
                    @if ($categories->count() > 1)
                        <div class="flex flex-wrap gap-1">
                            <button type="button" @click="filter = 'all'"
                                :class="filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'"
                                class="px-2.5 py-1 rounded-full text-xs font-medium transition">Todas</button>
                            @foreach ($categories as $cat)
                                <button type="button" @click="filter = '{{ $cat }}'"
                                    :class="filter === '{{ $cat }}' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'"
                                    class="px-2.5 py-1 rounded-full text-xs font-medium transition">{{ $cat }}</button>
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach ($knowledgeArticles as $article)
                            <div x-show="filter === 'all' || filter === '{{ $article->category }}'"
                                class="bg-white rounded-lg border border-gray-200">
                                <button type="button"
                                    @click="openArticle = (openArticle === {{ $article->id }} ? null : {{ $article->id }})"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span>{{ $article->title }}</span>
                                        @if ($article->priority)
                                            <x-ui.badge :variant="match($article->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'neutral' }">
                                                {{ $article->priority }} - @php $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja']; @endphp
                                                {{ $priorityLabels[$article->priority] ?? $article->priority }}
                                            </x-ui.badge>
                                        @endif
                                    </div>
                                    <span class="material-symbols-outlined text-base transition-transform flex-shrink-0 ml-2"
                                        :class="openArticle === {{ $article->id }} ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                <div x-show="openArticle === {{ $article->id }}" x-collapse
                                    class="px-3 pb-3 text-xs text-gray-600 whitespace-pre-line">
                                    {{ $article->content }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Switches: Crear OT / Requiere NOC / Crear Contrato --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-5 border-b border-gray-100">
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <x-ui.toggle label="Crear OT" description="Generar orden de trabajo directamente desde el ticket."
                        wire:model.live="create_ot" on-color="amber"
                        :disabled="!$editingEnabled || !$canToggleOt" />
                    @if ($create_ot)
                        <x-ui.badge variant="warning" class="mt-2">OT: Sí</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" class="mt-2">OT: No</x-ui.badge>
                    @endif
                </div>
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <x-ui.toggle label="¿Requiere intervención del NOC?" description="Se enviará al panel NOC para su resolución."
                        wire:model.live="requires_noc"
                        :disabled="!$editingEnabled || !$canToggleNoc" />
                    @if ($requires_noc)
                        <x-ui.badge variant="info" class="mt-2">NOC: Sí</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" class="mt-2">NOC: No</x-ui.badge>
                    @endif
                </div>
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <x-ui.toggle label="Crear Contrato" description="Generar contrato de servicio automáticamente."
                        wire:model.live="requires_contract" on-color="green"
                        :disabled="!$editingEnabled || !$canToggleContract" />
                    @if ($requires_contract)
                        <x-ui.badge variant="success" class="mt-2">Contrato: Sí</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral" class="mt-2">Contrato: No</x-ui.badge>
                    @endif
                </div>
            </div>

            {{-- Info: cuándo usar OT vs NOC vs Contrato --}}
            <x-ui.alert variant="warning" title="¿Cuándo usar cada opción?">
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Crear OT</strong> — El problema requiere visita técnica en campo. Se genera una orden de trabajo para que el supervisor asigne a un técnico.</li>
                    <li><strong>Requiere NOC</strong> — El problema puede resolverse de forma remota (configuración, señal, plataforma). El NOC lo evaluará y, si no puede resolverlo, generará una OT.</li>
                    <li><strong>Crear Contrato</strong> — Se genera un contrato de servicio al resolver el ticket.</li>
                </ul>
            </x-ui.alert>

            {{-- Origen --}}
            <div class="pb-5 border-b border-gray-100">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500 text-sm">source</span>
                    Origen del ticket
                </label>
                <x-ui.select wire:model.live="origin" icon="source" :disabled="!$editingEnabled">
                    <option value="Facebook Messenger">Facebook Messenger</option>
                    <option value="SMS WhatsApp">SMS WhatsApp</option>
                    <option value="Llamada de WhatsApp">Llamada de WhatsApp</option>
                    <option value="Llamada Telefónica">Llamada Telefónica</option>
                    <option value="SMS">SMS</option>
                    <option value="Presencial">Presencial</option>
                    <option value="Otros">Otros</option>
                </x-ui.select>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="secondary" wire:click="goBack">
                    {{ $ticketOpened ? 'Salir' : 'Cancelar' }}
                </x-ui.button>
                @if ($ticketOpened)
                    <x-ui.button type="button" variant="danger" icon="block" wire:click="confirmCancel">
                        Cancelar Ticket
                    </x-ui.button>
                    @if ($requires_noc)
                        <x-ui.button type="button" variant="primary" icon="send" wire:click="confirmGenerate">
                            Generar Ticket
                        </x-ui.button>
                    @elseif($requires_contract)
                        <x-ui.button type="button" variant="info" icon="description" wire:click="confirmGenerateContract">
                            Enviar a Contratos
                        </x-ui.button>
                    @elseif($create_ot)
                        <x-ui.button type="button" variant="warning" icon="engineering" wire:click="confirmSolve">
                            Crear OT y Finalizar
                        </x-ui.button>
                    @else
                        <x-ui.button type="button" variant="success" icon="check_circle" wire:click="confirmSolve">
                            Solucionar Ticket
                        </x-ui.button>
                    @endif
                @else
                    <x-ui.button type="submit" variant="primary" icon="save">
                        {{ $ticketId ? 'Actualizar Ticket' : 'Crear Ticket' }}
                    </x-ui.button>
                @endif
            </div>
            @endif
        </form>
    </x-ui.card>

    {{-- Modal para crear cliente --}}
    @if ($showClientModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            x-data="{ open: true }" x-show="open" x-cloak>
            <div class="relative mx-auto p-5 w-full max-w-3xl max-h-[85vh] overflow-y-auto">
                <x-ui.card overflow="visible">
                    <x-slot:headerActions>
                        <button type="button" @click="$wire.closeClientModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <livewire:clients.client-form :client-id="$editingClientId" :key="$modalKey" />
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación: Nuevo cliente --}}
    @if ($confirmingNewClient)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-amber-100 mb-4">
                            <span class="material-symbols-outlined text-amber-600 text-2xl">warning</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">¿Registrar nuevo cliente?</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Al crear un nuevo cliente, los datos del ticket actual se reiniciarán y podrías perder la información que ya habías ingresado.
                        </p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="warning" wire:click="proceedToNewClient">Sí, continuar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelNewClient">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación de guardado --}}
    @if ($confirmingSave)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            ¿Estás seguro de guardar este ticket?
                            @if (!$ticketId && $create_ot)
                                <span class="block text-xs text-amber-600 mt-1">Se creará una orden de trabajo a partir del ticket.</span>
                            @elseif(!$ticketId && $requires_noc)
                                <span class="block text-xs text-blue-600 mt-1">El ticket será escalado al panel NOC.</span>
                            @elseif(!$ticketId)
                                <span class="block text-xs text-gray-500 mt-1">El ticket quedará registrado sin generar OT.</span>
                            @endif
                        </p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="primary" wire:click="executeSave">Sí, continuar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelSave">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación para Solucionar Ticket --}}
    @if ($confirmingSolve)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                            <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $create_ot ? '¿Crear OT?' : '¿Solucionar ticket?' }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-2">
                            @if ($create_ot)
                                Se creará una orden de trabajo vinculada al ticket. El ticket quedará <strong>en seguimiento</strong> hasta que la OT se complete.
                            @else
                                Se guardarán todos los datos y se marcará el ticket como resuelto.
                            @endif
                            El cronómetro se detendrá.
                        </p>
                        @if ($elapsedSeconds > 0)
                            <p class="text-sm text-gray-500 mt-1">
                                Tiempo transcurrido: <strong>{{ gmdate('H:i:s', $elapsedSeconds) }}</strong>
                            </p>
                        @endif
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" :variant="$create_ot ? 'warning' : 'success'" wire:click="executeSolve">
                            {{ $create_ot ? 'Sí, crear OT' : 'Sí, solucionar' }}
                        </x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelSolve">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación para Abrir Ticket --}}
    @if ($confirmingOpen)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                            <span class="material-symbols-outlined text-green-600 text-2xl">play_arrow</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">¿Iniciar ticket?</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Se abrirá un nuevo ticket y comenzará el cronómetro de atención.
                        </p>
                    </div>
                    <x-slot:footer>
                        <div class="flex justify-center gap-3">
                            <x-ui.button type="button" variant="success" wire:click="executeOpen">Sí, iniciar</x-ui.button>
                            <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelOpen">Cancelar</x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación para Generar Ticket (NOC) --}}
    @if ($confirmingGenerate)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">send</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">¿Generar ticket para NOC?</h3>
                        <p class="text-sm text-gray-600 mt-2">El ticket se enviará al panel del NOC para su revisión y derivación.</p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="primary" wire:click="executeGenerate">Sí, generar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelGenerate">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Confirmación para Enviar a Contratos --}}
    @if ($confirmingGenerateContract)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-indigo-100 mb-4">
                            <span class="material-symbols-outlined text-indigo-600 text-2xl">description</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">¿Enviar a Contratos?</h3>
                        <p class="text-sm text-gray-600 mt-2">El ticket se enviará a la bandeja de Contratos para su revisión y generación del contrato + OT.</p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="primary" wire:click="executeGenerateContract">Sí, enviar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelGenerateContract">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal de cancelación con motivo obligatorio --}}
    @if ($confirmingCancel)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                            <span class="material-symbols-outlined text-red-600 text-2xl">block</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Cancelar Ticket</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Se detendrá el cronómetro y el ticket quedará como <strong>cancelado</strong>.
                        </p>
                        @if ($elapsedSeconds > 0)
                            <p class="text-sm text-gray-500 mt-1">
                                Tiempo transcurrido: <strong>{{ gmdate('H:i:s', $elapsedSeconds) }}</strong>
                            </p>
                        @endif
                        <div class="mt-4 text-left">
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                Motivo de cancelación <span class="text-red-500">*</span>
                            </label>
                            <x-ui.textarea wire:model="cancelReason" rows="3" placeholder="Ej: El cliente ya no quiere el servicio..." />
                            @error('cancelReason')
                                <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <x-slot:footer>
                        <x-ui.button type="button" variant="danger" wire:click="executeCancel">Sí, cancelar ticket</x-ui.button>
                        <x-ui.button type="button" variant="secondary" @click="open = false" wire:click="cancelCancel">Volver</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    {{-- Modal de lista de tipos de servicio --}}
    @if ($showServiceTypeModal)
        <div x-data="{ show: true }" x-show="show" x-cloak
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-lg">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">handyman</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Seleccionar tipo de servicio</h3>
                                <p class="text-xs text-gray-500">Elegí un tipo de servicio de la lista</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeServiceTypeModal" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>
                    <div class="p-4 border-b border-gray-100">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            <input type="text" wire:model.live.debounce.300ms="serviceTypeListSearch"
                                placeholder="Filtrar tipo de servicio..."
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        </div>
                    </div>
                    <div class="p-2 max-h-80 overflow-y-auto">
                        @forelse($serviceTypeList as $st)
                            <button type="button" wire:click="selectServiceType({{ $st->id }})"
                                class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                        <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-blue-600">handyman</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ str_replace('_', ' ', $st->name) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if($st->requires_contract)
                                        <x-ui.badge variant="success" size="sm">Requiere Contrato</x-ui.badge>
                                    @elseif($st->requires_potential)
                                        <x-ui.badge variant="warning" size="sm">Cliente Potencial</x-ui.badge>
                                    @elseif($st->requires_ot)
                                        <x-ui.badge variant="warning" size="sm">Requiere OT</x-ui.badge>
                                    @elseif($st->requires_noc)
                                        <x-ui.badge variant="info" size="sm">Requiere NOC</x-ui.badge>
                                    @endif
                                    <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-lg">chevron_right</span>
                                </div>
                            </button>
                        @empty
                            <div class="py-12 text-center">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">search_off</span>
                                <p class="text-gray-500 text-sm">No se encontraron tipos de servicio</p>
                                <p class="text-xs text-gray-400 mt-1">Probá con otro término de búsqueda</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <x-ui.button variant="secondary" wire:click="closeServiceTypeModal">Cerrar</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
         x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
         x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
         x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
         style="display: none;">
        <div x-show="toastType === 'success'"
             class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
             class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'info'"
             class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>