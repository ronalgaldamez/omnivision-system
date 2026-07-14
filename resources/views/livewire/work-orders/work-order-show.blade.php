<div class="max-w-2xl mx-auto">
    <x-ui.card icon="work" title="OT: {{ $workOrder->code ?? 'OT-'.$workOrder->id }}" subtitle="Detalle de la orden de trabajo">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('mobile.work-orders.list') }}">Volver</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            {{-- ========== DATOS DEL CLIENTE ========== --}}
            <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">person</span>
                Datos del Cliente
            </h2>
            @if($workOrder->client)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Nombre</p>
                        <p class="text-gray-800 font-medium">{{ $workOrder->client->name }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">call</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Teléfono</p>
                        <p class="text-gray-700">
                            @if($workOrder->client->phones->isNotEmpty())
                            @foreach($workOrder->client->phones as $phone){{ $phone->number }}{{ !$loop->last ? ', ' : '' }}@endforeach
                            @else
                            {{ $workOrder->client->phone ?? '—' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">location_on</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Dirección</p>
                        <p class="text-gray-700">{{ $workOrder->client->address ?? '—' }}</p>
                    </div>
                </div>
                @if($workOrder->client->installation_address)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">home_pin</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Dirección de instalación</p>
                        <p class="text-gray-700">{{ $workOrder->client->installation_address }}</p>
                    </div>
                </div>
                @endif
                @if($workOrder->client->document_type && $workOrder->client->document_number)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">fingerprint</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ strtoupper($workOrder->client->document_type) }}</p>
                        <p class="text-gray-700">{{ $workOrder->client->document_number }}</p>
                    </div>
                </div>
                @endif
                @if($workOrder->client->email)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">mail</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Correo</p>
                        <p class="text-gray-700">{{ $workOrder->client->email }}</p>
                    </div>
                </div>
                @endif
                @if($workOrder->client->service)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">tv</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Servicio</p>
                        <p class="text-gray-700">{{ $workOrder->client->service }}</p>
                    </div>
                </div>
                @endif
                @if($workOrder->client->nro_luz)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">bolt</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">N.° de luz</p>
                        <p class="text-gray-700">{{ $workOrder->client->nro_luz }}</p>
                    </div>
                </div>
                @endif
                @if($workOrder->client->departamento || $workOrder->client->municipio || $workOrder->client->distrito)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">map</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Ubicación</p>
                        <p class="text-gray-700">{{ implode(', ', array_filter([$workOrder->client->departamento, $workOrder->client->municipio, $workOrder->client->distrito])) }}</p>
                    </div>
                </div>
                @endif
                @if($workOrder->client->branch)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">business</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Sucursal</p>
                        <p class="text-gray-700">{{ $workOrder->client->branch->name }}</p>
                    </div>
                </div>
                @endif
                @php $lat = $workOrder->latitude ?? $workOrder->client?->latitude; $lng = $workOrder->longitude ?? $workOrder->client?->longitude; @endphp
                @if($lat && $lng)
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">explore</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Coordenadas {{ $workOrder->latitude ? '(OT)' : '(Cliente)' }}</p>
                        <p class="text-gray-700">{{ $lat }}, {{ $lng }}</p>
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="flex items-center gap-3 p-4 bg-gray-50/50 rounded-lg border border-gray-100">
                <span class="material-symbols-outlined text-gray-400">person_off</span>
                <p class="text-sm text-gray-500 italic">Cliente no asignado a esta orden de trabajo</p>
            </div>
            @endif

            {{-- ========== DATOS TÉCNICOS ========== --}}
            <div class="border-t border-gray-200 pt-5 space-y-3">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">settings</span>
                    Datos Técnicos
                </h2>
                <form wire:submit.prevent="saveTechnicalData" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-gray-600">Nombre de perfil</label><input type="text" wire:model="profile_name" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">Contraseña de perfil</label><input type="text" wire:model="profile_password" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">Nombre wifi</label><input type="text" wire:model="wifi_name" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">Contraseña wifi</label><input type="text" wire:model="wifi_password" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">MAC</label><input type="text" wire:model="mac" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">PON</label><input type="text" wire:model="pon" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">Mufa</label><input type="text" wire:model="mufa" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                        <div><label class="block text-xs font-medium text-gray-600">Fecha de instalación</label><input type="date" wire:model="installation_date" {{ !$canEditTech ? 'disabled' : '' }} class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm disabled:bg-gray-100"></div>
                    </div>
                    @if($canEditTech)
                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="primary" icon="save">Guardar datos técnicos</x-ui.button>
                    </div>
                    @endif
                </form>
            </div>

            {{-- ========== INFORMACIÓN DEL TICKET ========== --}}
            @if($workOrder->ticket)
            <div class="border-t border-gray-200 pt-5 space-y-3">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">confirmation_number</span>
                    {{ $workOrder->ticket->ticket_code ?? 'Ticket #'.$workOrder->ticket->id }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">person</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Creado por</p>
                            <p class="text-gray-700">{{ $workOrder->ticket->createdBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">source</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Origen</p>
                            <p class="text-gray-700">{{ $this->getTicketOriginLabel() ?? '—' }}</p>
                        </div>
                    </div>
                    @if($workOrder->ticket->priority)
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">flag</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Prioridad</p>
                            <p class="text-gray-700">
                                @php $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja']; @endphp
                                <x-ui.badge variant="{{ match($workOrder->ticket->priority) { 'P1' => 'danger', 'P2' => 'warning', 'P3' => 'info', default => 'secondary' } }}">{{ $workOrder->ticket->priority }} - {{ $priorityLabels[$workOrder->ticket->priority] ?? $workOrder->ticket->priority }}</x-ui.badge>
                            </p>
                        </div>
                    </div>
                    @endif
                    @if($workOrder->ticket->description)
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                        <span class="material-symbols-outlined text-gray-400">description</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Problema Reportado</p>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $workOrder->ticket->description }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ========== INFORMACIÓN DE LA OT ========== --}}
            <div class="border-t border-gray-200 pt-5 space-y-3">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">engineering</span>
                    Información de la OT
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">person</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Creada por</p>@php $creator = $workOrder->createdBy; $rol = $creator?->getRoleNames()?->first(); @endphp<p class="text-gray-700">{{ $creator?->name ?? 'N/A' }}{{ $rol ? ' ('.strtoupper(str_replace('_', ' ', $rol)).')' : '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">handyman</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Tipo de servicio</p>
                            <p class="text-gray-700">{{ $workOrder->service_type ? str_replace('_', ' ', ucfirst($workOrder->service_type)) : '—' }}</p>
                        </div>
                    </div>
                    @if($workOrder->zone)
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">map</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Zona</p>
                            <p class="text-gray-700">{{ $workOrder->zone->name }}</p>
                        </div>
                    </div>
                    @endif
                    @if($workOrder->plan)
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">satellite_alt</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Plan</p>
                            <p class="text-gray-700">{{ $workOrder->plan->name }} @if($workOrder->plan->speed)({{ $workOrder->plan->speed }})@endif</p>
                        </div>
                    </div>
                    @endif
                    @if($workOrder->requires_noc)
                    <div class="flex items-start gap-3 p-3 bg-blue-50/50 rounded-lg border border-blue-200">
                        <span class="material-symbols-outlined text-blue-500">router</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Requiere NOC</p>
                            <p class="text-sm text-blue-700 font-medium">Sí — requiere intervención remota</p>
                        </div>
                    </div>
                    @endif
                    @if($workOrder->assigned_at)
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">assignment_ind</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Asignado el</p>
                            <p class="text-gray-700">{{ $workOrder->assigned_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">calendar_month</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Fecha programada</p>
                            @if($workOrder->scheduled_date)<p class="text-gray-700">{{ $workOrder->scheduled_date->format('d/m/Y') }}</p>
                            @else<p class="text-gray-400 italic flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> Pendiente de programación</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">flag</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Estado</p>
                            @if($workOrder->status == 'pending')<x-ui.badge variant="warning">Pendiente</x-ui.badge>
                            @elseif($workOrder->status == 'in_progress')<x-ui.badge variant="info">En progreso</x-ui.badge>
                            @elseif($workOrder->status == 'paused')<x-ui.badge variant="secondary">Pausada</x-ui.badge>
                            @elseif($workOrder->status == 'completed')<x-ui.badge variant="success">Completada</x-ui.badge>
                            @else<x-ui.badge variant="danger">Cancelada</x-ui.badge>
                            @endif
                        </div>
                    </div>
                    @if($workOrder->started_at || $workOrder->accumulated_seconds > 0)
                    <div class="flex items-start gap-3 p-3 bg-blue-50/50 rounded-lg border border-blue-100 sm:col-span-2">
                        <span class="material-symbols-outlined text-blue-500">schedule</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Tiempo trabajado</p>
                            @if($workOrder->started_at)<p class="text-sm text-gray-800 font-medium">Inició: {{ $workOrder->started_at->format('d/m/Y H:i') }}</p>@endif
                            @if($workOrder->accumulated_seconds > 0)
                                @php $hours = floor($workOrder->accumulated_seconds / 3600); $minutes = floor(($workOrder->accumulated_seconds % 3600) / 60); @endphp
                                <p class="text-xs text-gray-600">
                                    @if($workOrder->status === 'paused')Tiempo acumulado: {{ $hours }}h {{ $minutes }}m (pausada)
                                    @elseTiempo total anterior: {{ $hours }}h {{ $minutes }}m
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                        <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Notas</p>
                            <p class="text-gray-700">{{ $workOrder->notes ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($workOrder->products?->count())
            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                    Productos sugeridos
                </h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($workOrder->products as $item)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 text-gray-800">{{ $item->product->name }}</td>
                                <td class="px-4 py-3 text-center font-mono text-gray-800">{{ $item->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if($workOrder->latitude && $workOrder->longitude)
            <x-ui.button variant="success" icon="map" href="https://www.google.com/maps?q={{ $workOrder->latitude }},{{ $workOrder->longitude }}" target="_blank">Ver en Google Maps</x-ui.button>
            @endif

            @if(!in_array($workOrder->status, ['completed', 'cancelled']))
            <div class="border-t border-gray-200 pt-5 space-y-3">
                @if($workOrder->status === 'pending' && $hasOpenRequisition && !$hasAnotherInProgress)
                <button wire:click="promptStartWorkOrder"
                    class="block w-full text-center px-5 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-yellow-600 transition">
                    Iniciar OT
                </button>
                @elseif($workOrder->status === 'pending' && $hasAnotherInProgress)
                <div class="flex items-start gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <span class="material-symbols-outlined text-yellow-600">warning</span>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Ya tienes otra OT en progreso</p>
                        <p class="text-xs text-yellow-700">Finaliza o pausa esa OT antes de iniciar esta.</p>
                    </div>
                </div>
                @endif
                @if($workOrder->status === 'in_progress')
                <button wire:click="promptPauseWorkOrder"
                    class="block w-full text-center px-5 py-2.5 bg-gray-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-600 transition">
                    Pausar OT
                </button>
                @if(auth()->user()->can('complete work_orders'))
                <button wire:click="promptCompleteWorkOrder"
                    class="block w-full text-center px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                    Completar trabajo
                </button>
                @endif
                @endif
                @if($workOrder->status === 'paused')
                <button wire:click="promptResumeWorkOrder"
                    class="block w-full text-center px-5 py-2.5 bg-blue-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-600 transition">
                    Reanudar OT
                </button>
                @endif
            </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Modal de confirmación --}}
    @if($confirmingAction)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <x-ui.card>
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $confirmingMessage }}</p>
                </div>
                <x-slot:footer>
                    <x-ui.button variant="primary" wire:click="executeConfirmedAction">Sí, continuar</x-ui.button>
                    <x-ui.button variant="secondary" @click="open = false" wire:click="cancelConfirmation">Cancelar</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
    @endif

    @if($showConsumptionModal)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <x-ui.card icon="inventory_2" title="Material Utilizado en {{ $workOrder->code ?? 'OT-'.$workOrder->id }}">
                <x-slot:headerActions>
                    <button wire:click="closeConsumptionModal" class="text-gray-400 hover:text-gray-600 transition">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </x-slot:headerActions>
                <div class="p-5 space-y-4">
                    @if(!empty($availableProducts))
                    <p class="text-sm text-gray-600">Indica cuánto material usaste de tu requisición para esta OT.</p>
                    <div class="space-y-3">
                        @foreach($availableProducts as $index => $product)
                        <div class="flex items-center justify-between gap-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ $product['product_name'] }}</p>
                                <p class="text-xs text-gray-500">Disponible: {{ $product['available'] }}</p>
                            </div>
                            <input type="number" min="0" max="{{ $product['available'] }}" step="any"
                                wire:model.defer="consumptionQuantities.{{ $index }}"
                                class="w-20 text-center rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm py-1.5">
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500">No tienes productos disponibles en tu requisición activa.</p>
                    @endif
                </div>
                <x-slot:footer>
                    <x-ui.button variant="primary" wire:click="saveConsumption">Guardar consumo</x-ui.button>
                    <x-ui.button variant="secondary" wire:click="closeConsumptionModal">Omitir</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
    @endif

    @if($showWorkOrderSelectionModal)
    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-lg">
            <x-ui.card icon="playlist_add_check" title="Selecciona OTs para Vincular">
                <x-slot:headerActions>
                    <button wire:click="closeWorkOrderSelectionModal" class="text-gray-400 hover:text-gray-600 transition">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </x-slot:headerActions>
                <div class="p-5 space-y-4">
                    <p class="text-sm text-gray-600">Marca las Órdenes de Trabajo que deseas agregar a tu requisición activa.</p>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @forelse($eligibleWorkOrders as $wo)
                        <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                            <x-ui.checkbox value="{{ $wo['id'] }}" wire:model="selectedWorkOrdersForLink" />
                            <span class="text-sm text-gray-700">{{ $wo['name'] }}</span>
                        </label>
                        @empty
                        <p class="text-sm text-gray-500">No hay OTs pendientes sin vincular.</p>
                        @endforelse
                    </div>
                </div>
                <x-slot:footer>
                    <x-ui.button variant="primary" wire:click="linkSelectedWorkOrders">Vincular seleccionadas</x-ui.button>
                    <x-ui.button variant="secondary" wire:click="closeWorkOrderSelectionModal">Cancelar</x-ui.button>
                </x-slot:footer>
            </x-ui.card>
        </div>
    </div>
    @endif

    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>