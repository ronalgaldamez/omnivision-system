<div class="max-w-4xl mx-auto">
    <x-ui.card title="Notificaciones" icon="notifications" subtitle="Historial de notificaciones del sistema">
        <x-slot:headerActions>
            @if($unreadCount > 0)
                <x-ui.button variant="secondary" size="sm" icon="done_all" wire:click="markAllRead">Marcar todas como leídas</x-ui.button>
            @endif
        </x-slot:headerActions>

        <div class="flex items-center gap-2 mb-4">
            <button wire:click="$set('filter', '')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition
                {{ $filter === '' ? 'bg-blue-50 border-blue-300 text-blue-700' : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50' }}">
                Todas
            </button>
            <button wire:click="$set('filter', 'unread')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition
                {{ $filter === 'unread' ? 'bg-blue-50 border-blue-300 text-blue-700' : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50' }}">
                No leídas
            </button>
            <button wire:click="$set('filter', 'read')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition
                {{ $filter === 'read' ? 'bg-blue-50 border-blue-300 text-blue-700' : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50' }}">
                Leídas
            </button>
        </div>

        <div class="space-y-2">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    [$icon, $iconColor, $iconBg] = notification_visual($data);
                    $link = notification_url($data);
                @endphp
                <div class="flex items-start gap-3 p-4 border border-gray-200 rounded-xl
                    {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/40 border-blue-100' }}">
                    <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center {{ $iconBg }}">
                        <span class="material-symbols-outlined {{ $iconColor }}">{{ $icon }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-gray-800 font-medium">{{ $data['message'] ?? 'Notificación' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @if($link)
                                <a href="{{ $link }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 whitespace-nowrap flex-shrink-0">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    Ver detalle
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        @if($notification->read_at)
                            <span class="text-[10px] text-gray-400">Leída</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">Nueva</span>
                            <button wire:click="markAsRead('{{ $notification->id }}')" class="text-[10px] text-blue-500 hover:underline">Marcar leída</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">notifications_off</span>
                    <p class="text-gray-500 font-medium">Sin notificaciones</p>
                    <p class="text-sm text-gray-400 mt-1">Las notificaciones aparecerán aquí</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </x-ui.card>
</div>
