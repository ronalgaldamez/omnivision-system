<div x-data="{ open: false }" class="relative">
    {{-- Icono de campana con badge --}}
    <button @click="open = !open" class="relative inline-flex items-center focus:outline-none">
        <span class="material-symbols-outlined text-gray-500 text-2xl hover:text-gray-700 transition">notifications</span>
        @if($pendingNocTickets > 0)
            <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $pendingNocTickets }}
            </span>
        @endif
    </button>

    {{-- Dropdown de notificaciones --}}
    <div x-show="open" @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200/80 overflow-hidden z-50"
        style="display: none;">

        <div class="p-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Notificaciones NOC</h3>
        </div>

        <div class="max-h-64 overflow-y-auto">
            @forelse($notifications as $ticket)
                <a href="{{ route('noc.panel', ['ticket_id' => $ticket->id]) }}"
                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                    
                    {{-- Avatar con inicial del cliente --}}
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-xs font-semibold text-blue-600">
                            {{ strtoupper(substr($ticket->client->name ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                    
                    {{-- Contenido del ticket --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-xs font-mono text-blue-600">{{ $ticket->ticket_code ?? '—' }}</span>
                            <span class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-800 font-medium truncate">
                            {{ $ticket->client->name ?? 'Cliente desconocido' }}
                        </p>
                        <p class="text-xs text-gray-500 truncate mt-0.5">
                            {{ Str::limit($ticket->description, 60) }}
                        </p>
                    </div>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-gray-400 text-center">No hay notificaciones pendientes</p>
            @endforelse
        </div>

        @if($pendingNocTickets > 5)
            <div class="p-2 border-t border-gray-100 text-center">
                <a href="{{ route('noc.panel') }}" class="text-xs text-blue-600 hover:underline">
                    Ver todos ({{ $pendingNocTickets }})
                </a>
            </div>
        @endif
    </div>

    {{-- Polling cada 30s --}}
    <div wire:poll.{{ $pollingInterval }}s="updateCount" class="hidden"></div>
</div>