<div class="max-w-4xl mx-auto" x-data="{ tab: 'general', selectedService: null }">
    <x-ui.card icon="person" :title="$client->name" subtitle="Detalle del cliente">
        <x-slot:headerActions>
            <a href="{{ route('admin.clients.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver al listado
            </a>
        </x-slot:headerActions>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 -mx-6 px-6 mb-6">
            <nav class="flex gap-0">
                <button @click="tab = 'general'"
                    :class="tab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    Datos generales
                </button>
                <button @click="tab = 'services'; selectedService = null"
                    :class="tab === 'services' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    Servicios
                </button>
                <button @click="tab = 'history'"
                    :class="tab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition">
                    Historial
                </button>
            </nav>
        </div>

        {{-- Pestaña 1: Datos generales --}}
        <div x-show="tab === 'general'" x-cloak>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500">Nombre</p>
                        <p class="text-gray-800 font-medium">{{ $client->name }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">fingerprint</span>
                    <div>
                        <p class="text-xs text-gray-500">Documento</p>
                        <p class="text-gray-800">
                            @if($client->document_type)
                                {{ strtoupper($client->document_type) }}: {{ $client->document_number }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">call</span>
                    <div>
                        <p class="text-xs text-gray-500">Teléfonos</p>
                        @forelse($client->phones as $phone)
                            <p class="text-gray-800">{{ $phone->number }} ({{ $phone->type }})</p>
                        @empty
                            <p class="text-gray-800">{{ $client->phone ?? '—' }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">mail</span>
                    <div>
                        <p class="text-xs text-gray-500">Correo</p>
                        <p class="text-gray-800">{{ $client->email ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">location_on</span>
                    <div>
                        <p class="text-xs text-gray-500">Dirección</p>
                        <p class="text-gray-800">{{ $client->address ?? '—' }}</p>
                    </div>
                </div>
                @if($client->installation_address)
                <div class="flex items-start gap-3 p-3 bg-blue-50/50 rounded-lg border border-blue-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-blue-500">home_pin</span>
                    <div>
                        <p class="text-xs text-blue-400">Dirección de instalación</p>
                        <p class="text-gray-800">{{ $client->installation_address }}</p>
                    </div>
                </div>
                @endif
                @if($client->latitude && $client->longitude)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">explore</span>
                    <div>
                        <p class="text-xs text-gray-500">Coordenadas</p>
                        <p class="text-gray-800 font-mono">{{ $client->latitude }}, {{ $client->longitude }}</p>
                    </div>
                </div>
                @endif
                @if($client->nro_luz)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">bolt</span>
                    <div>
                        <p class="text-xs text-gray-500">N.° de luz</p>
                        <p class="text-gray-800">{{ $client->nro_luz }}</p>
                    </div>
                </div>
                @endif
                @if($client->service)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">tv</span>
                    <div>
                        <p class="text-xs text-gray-500">Servicio contratado</p>
                        <p class="text-gray-800">{{ $client->service }}</p>
                    </div>
                </div>
                @endif
                @if($client->branch)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">business</span>
                    <div>
                        <p class="text-xs text-gray-500">Sucursal</p>
                        <p class="text-gray-800">{{ $client->branch->name }}</p>
                    </div>
                </div>
                @endif
                @if($client->zone)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">map</span>
                    <div>
                        <p class="text-xs text-gray-500">Zona</p>
                        <p class="text-gray-800">{{ $client->zone->name }} ({{ ucfirst($client->zone->level) }})</p>
                    </div>
                </div>
                @endif
                @if($client->plan)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">subscriptions</span>
                    <div>
                        <p class="text-xs text-gray-500">Plan</p>
                        <p class="text-gray-800">{{ $client->plan->name }} @if($client->plan->speed)({{ $client->plan->speed }})@endif</p>
                    </div>
                </div>
                @endif
                @if($client->notes)
                <div class="flex items-start gap-3 p-3 bg-gray-50/70 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                    <div>
                        <p class="text-xs text-gray-500">Notas</p>
                        <p class="text-gray-800 whitespace-pre-wrap">{{ $client->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Pestaña 2: Servicios --}}
        <div x-show="tab === 'services'" x-cloak>
            @if($completedWorkOrders->isEmpty())
                <p class="text-gray-500 text-center py-8">No hay servicios instalados registrados para este cliente.</p>
            @else
                <div class="space-y-3">
                    @foreach($completedWorkOrders as $wo)
                        <div class="border border-gray-200 rounded-xl overflow-hidden" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition text-left">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $wo->service_type ?? 'Instalación' }}</p>
                                    <p class="text-xs text-gray-500">Instalado: {{ $wo->completed_date?->format('d/m/Y') ?? '—' }}</p>
                                </div>
                                <span class="material-symbols-outlined text-gray-400 transition-transform"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="px-4 pb-4 pt-2 bg-white">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500">Nombre WiFi</p>
                                        <p class="text-gray-800 font-medium">{{ $wo->wifi_name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Contraseña WiFi</p>
                                        <p class="text-gray-800 font-medium">{{ $wo->wifi_password ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Perfil</p>
                                        <p class="text-gray-800 font-medium">{{ $wo->profile_name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Contraseña perfil</p>
                                        <p class="text-gray-800 font-medium">{{ $wo->profile_password ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">MAC</p>
                                        <p class="text-gray-800 font-mono">{{ $wo->mac ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">PON</p>
                                        <p class="text-gray-800 font-mono">{{ $wo->pon ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Mufa</p>
                                        <p class="text-gray-800 font-medium">{{ $wo->mufa ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Fecha instalación</p>
                                        <p class="text-gray-800">{{ $wo->installation_date?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pestaña 3: Historial --}}
        <div x-show="tab === 'history'" x-cloak>
            @if($tickets->isEmpty())
                <p class="text-gray-500 text-center py-8">No hay tickets registrados para este cliente.</p>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Código</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Fecha</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Estado</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tickets as $ticket)
                                <tr class="hover:bg-gray-50/80">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $ticket->ticket_code ?? '#' . $ticket->id }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <x-ui.badge :variant="match($ticket->status) { 'resolved' => 'success', 'in_progress' => 'info', 'pending' => 'warning', default => 'neutral' }">
                                            {{ match($ticket->status) { 'resolved' => 'Resuelto', 'in_progress' => 'En progreso', 'pending' => 'Pendiente', default => $ticket->status } }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 max-w-xs truncate">{{ $ticket->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </x-ui.card>
</div>