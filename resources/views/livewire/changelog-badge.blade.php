<div>
    {{-- Ícono de actualizaciones --}}
    <button wire:click="open" class="relative inline-flex items-center focus:outline-none" title="Novedades del sistema">
        <span class="material-symbols-outlined text-gray-500 text-2xl hover:text-gray-700 transition">campaign</span>
        @if($hasUpdates)
            <span class="absolute -top-1 -right-2 bg-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $newCount > 9 ? '9+' : $newCount }}
            </span>
        @endif
    </button>

    {{-- Modal de actualizaciones --}}
    @if($showModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div class="relative w-full max-w-lg">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">new_releases</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Novedades del sistema</h3>
                                <p class="text-xs text-gray-500">Últimas actualizaciones realizadas</p>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto divide-y divide-gray-100">
                        @forelse($updates as $entry)
                            <div class="px-6 py-4">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($entry->version)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 font-mono">{{ $entry->version }}</span>
                                    @endif
                                    <h4 class="text-sm font-semibold text-gray-800">{{ $entry->title }}</h4>
                                </div>
                                @if($entry->description)
                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed whitespace-pre-line">{{ $entry->description }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-2">{{ $entry->published_at?->format('d/m/Y') }}</p>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">new_releases</span>
                                <p class="text-gray-500 text-sm">No hay novedades</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <x-ui.button variant="primary" size="sm" icon="done_all" wire:click="close">Entendido</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
