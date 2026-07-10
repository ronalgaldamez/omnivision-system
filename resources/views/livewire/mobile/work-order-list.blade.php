<div class="max-w-4xl mx-auto" wire:poll.30s="$refresh">
    <x-ui.card icon="engineering" title="Mis Órdenes de Trabajo" subtitle="{{ $orders->total() }} órdenes asignadas">
        <x-slot:headerActions>
            <x-ui.button variant="warning" icon="add_circle" wire:click="openCreateModal">+ Nueva OT en Campo</x-ui.button>
            <x-ui.button variant="success" icon="map" href="{{ route('mobile.work-orders.map') }}">Ver en mapa</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por cliente o dirección..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="pending,in_progress,paused">Pendientes / En progreso / Pausadas</option>
                        <option value="pending">Solo pendientes</option>
                        <option value="in_progress">Solo en progreso</option>
                        <option value="paused">Solo pausadas</option>
                        <option value="completed">Completadas</option>
                        <option value="cancelled">Canceladas</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($orders as $order)
                    <a href="{{ route('mobile.work-orders.show', $order->id) }}" 
                       class="block bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 group">
                        <div class="p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold font-mono tracking-wide group-hover:bg-blue-100 transition">
                                        <span class="material-symbols-outlined text-sm">tag</span>
                                        {{ $order->code ?? 'OT-'.$order->id }}
                                    </span>
                                    <span class="text-xs text-gray-500 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                                        {{ $order->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </span>
                                    @if($order->ticket?->priority)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            @switch($order->ticket->priority)
                                                @case('P1') bg-red-50 text-red-700 @break
                                                @case('P2') bg-orange-50 text-orange-700 @break
                                                @case('P3') bg-blue-50 text-blue-700 @break
                                                @case('P4') bg-gray-100 text-gray-600 @break
                                            @endswitch">
                                            <span class="material-symbols-outlined text-sm">flag</span>
                                            {{ $order->ticket->priority }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if($order->status == 'pending')
                                        <x-ui.badge variant="warning">Pendiente</x-ui.badge>
                                    @elseif($order->status == 'in_progress')
                                        <x-ui.badge variant="info">En progreso</x-ui.badge>
                                    @elseif($order->status == 'paused')
                                        <x-ui.badge variant="secondary">Pausada</x-ui.badge>
                                    @elseif($order->status == 'completed')
                                        <x-ui.badge variant="success">Completada</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Cancelada</x-ui.badge>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-gray-500 text-sm">person</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            {{ $order->client->name ?? 'Cliente no especificado' }}
                                        </p>
                                        @if($order->client->phone)
                                            <p class="text-xs text-gray-500">{{ $order->client->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 ml-10">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        {{ $order->client->address ?? 'Sin dirección' }}
                                        @if($order->client->installation_address)
                                            <br>
                                            <span class="text-gray-400">Instalación:</span> {{ $order->client->installation_address }}
                                        @endif
                                    </p>
                                </div>
                                @if($order->notes)
                                    <div class="flex items-start gap-2 ml-10">
                                        <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">sticky_note_2</span>
                                        <p class="text-xs text-gray-500 italic leading-relaxed line-clamp-2">
                                            "{{ $order->notes }}"
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                                <div class="flex items-center gap-2">
                                    @if($order->requisitions()->where('status', 'open')->exists())
                                        <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                                            Material asignado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                                            Sin material
                                        </span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs text-blue-600 font-medium group-hover:text-blue-700 transition">
                                    Ver detalle
                                    <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-16 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-6xl mb-4">inventory_2</span>
                        <p class="text-gray-500 font-medium">No tienes órdenes de trabajo asignadas</p>
                        <p class="text-sm text-gray-400 mt-1">Las órdenes que te asignen aparecerán aquí</p>
                    </div>
                @endforelse
            </div>

            @if($orders->hasPages())
                <div class="mt-6">{{ $orders->links() }}</div>
            @endif

            @if(session('message'))
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
            @endif
        </div>
    </x-ui.card>

    @if($showCreateModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card>
                    <x-slot:headerActions>
                        <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </x-slot:headerActions>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">Registra un trabajo de campo que no tiene ticket previo.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cliente *</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="newClientSearch"
                                    placeholder="Buscar por nombre o teléfono..."
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                @if(count($newClientResults) > 0)
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-40 overflow-auto divide-y divide-gray-100">
                                        @foreach($newClientResults as $client)
                                            <li wire:click="selectNewClient({{ $client->id }}, '{{ $client->name }}')"
                                                class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm">
                                                <span class="font-medium text-gray-800">{{ $client->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ $client->phone ?? 'Sin teléfono' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @if($newClientName)
                                <p class="text-sm text-green-700 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Seleccionado: {{ $newClientName }}
                                </p>
                            @endif
                            @error('newClientId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción del trabajo *</label>
                            <textarea wire:model="newDescription" rows="3"
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                                placeholder="Describe el problema o trabajo a realizar..."></textarea>
                            @error('newDescription') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <x-slot:footer>
                        <x-ui.button variant="primary" wire:click="createFieldOT">Crear e Iniciar OT</x-ui.button>
                        <x-ui.button variant="secondary" wire:click="closeCreateModal">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    <div x-data="{ toasts: [] }"
        x-on:show-toast.window="toasts.push({ id: Date.now() + Math.random(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => toasts.shift(), 3500)"
        class="fixed bottom-5 right-5 z-50 flex flex-col-reverse gap-2 items-end"
        style="max-height: 80vh; overflow-y: auto;">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100">
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