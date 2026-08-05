<div x-data="{ open: false }" class="relative">
    {{-- Icono de campana con badge --}}
    <button @click="open = !open" class="relative inline-flex items-center focus:outline-none">
        <span class="material-symbols-outlined text-gray-500 text-2xl hover:text-gray-700 transition">notifications</span>
        @if($count > 0)
            <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $count > 9 ? '9+' : $count }}
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

        <div class="p-3 border-b border-gray-100 flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-gray-700">Notificaciones</h3>
            <div class="flex items-center gap-2">
                <button wire:click="toggleSound" :title="$wire.soundEnabled ? 'Silenciar sonido' : 'Activar sonido'"
                    class="p-1 rounded-lg transition {{ $soundEnabled ? 'text-blue-600 hover:bg-blue-50' : 'text-gray-400 hover:bg-gray-100' }}">
                    <span class="material-symbols-outlined text-lg">{{ $soundEnabled ? 'volume_up' : 'volume_off' }}</span>
                </button>
                @if($count > 0)
                    <button wire:click="markAllRead" class="text-xs text-blue-600 hover:underline">Marcar leídas</button>
                @endif
            </div>
        </div>

        <div class="max-h-64 overflow-y-auto">
            @forelse($notifications as $notification)
                @php $data = $notification->data; @endphp
                <a href="{{ isset($data['requisition_id']) ? route('technician.requisitions.show', $data['requisition_id']) : route('notifications.index') }}"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition {{ $notification->read_at ? 'opacity-60' : 'bg-blue-50/40' }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ ($data['status'] ?? '') === 'approved' ? 'bg-green-100' : 'bg-red-100' }}">
                        <span class="material-symbols-outlined text-base {{ ($data['status'] ?? '') === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($data['status'] ?? '') === 'approved' ? 'check_circle' : 'cancel' }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 font-medium">{{ $data['message'] ?? 'Notificación' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-gray-400 text-center">No hay notificaciones</p>
            @endforelse
        </div>

        <div class="p-2 border-t border-gray-100 text-center">
            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">Ver historial completo</a>
        </div>
    </div>

    {{-- Actualización en tiempo real vía Reverb (sin polling) --}}
    <div x-data @refresh-notifications.window="$wire.updateCount()" class="hidden"></div>
</div>
