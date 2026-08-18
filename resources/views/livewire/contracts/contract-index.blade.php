<div class="max-w-5xl mx-auto">
    <x-ui.card icon="description" title="Contratos" subtitle="Gestión de contratos generados desde tickets">
        <x-slot:headerActions>
            <x-ui.input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por cliente..." icon="search" />
            <a href="{{ route('contracts.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                <span class="material-symbols-outlined text-base">add</span>
                Nuevo Contrato
            </a>
        </x-slot:headerActions>

        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">#</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Servicio</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Plan</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Zona</th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Fecha</th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $contract->id }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $contract->client?->name ?? '—' }}</p>
                                @if($contract->client?->phone)
                                    <p class="text-xs text-gray-500">{{ $contract->client->phone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 capitalize">{{ $contract->serviceTypeName() }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $contract->plan?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $contract->zone?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($contract->status === 'ready_to_send')
                                    <x-ui.badge variant="info" icon="send">Listo para enviar</x-ui.badge>
                                @else
                                    <x-ui.badge :variant="match($contract->status) { 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'danger', default => 'neutral' }">
                                        {{ ucfirst($contract->status) }}
                                    </x-ui.badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $contract->contract_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($contract->hasPdf())
                                        <a href="{{ route('contracts.pdf-preview', $contract->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                                            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                                            Ver PDF
                                        </a>
                                    @endif
                                    <button wire:click="createWorkOrder({{ $contract->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                                        <span class="material-symbols-outlined text-sm">engineering</span>
                                        Crear OT
                                    </button>
                                    @if($contract->status === 'ready_to_send')
                                        <button wire:click="promptSend({{ $contract->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                                            <span class="material-symbols-outlined text-sm">send</span>
                                            Revisar y enviar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">description</span>
                                <p class="text-gray-500">No hay contratos registrados</p>
                                <p class="text-sm text-gray-400 mt-1">Los contratos se crean automáticamente al resolver un ticket con tipo de servicio que requiera contrato.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    {{-- Modal: Revisar y enviar contrato --}}
    @if($confirmingSend)
        @php $sendContract = $contracts->firstWhere('id', $confirmingSend); @endphp
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card overflow="visible">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                            <span class="material-symbols-outlined text-green-600 text-2xl">send</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Revisar y enviar contrato</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            El contrato <strong>{{ $sendContract?->contract_digital_code }}</strong> está listo para enviarse al cliente.
                        </p>
                        @if($sentWhatsAppLink)
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-left space-y-2">
                                <p class="text-xs text-green-700 font-medium">Enlace generado. Enviáselo al cliente por WhatsApp:</p>
                                <a href="{{ $sentWhatsAppLink }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition w-full justify-center">
                                    <span class="material-symbols-outlined text-sm">chat</span>
                                    Abrir WhatsApp
                                </a>
                            </div>
                        @else
                            <div class="mt-3 bg-gray-50 rounded-lg p-3 text-left text-sm space-y-1">
                                <p><span class="text-gray-500">Cliente:</span> <span class="font-medium">{{ $sendContract?->client?->name ?? '—' }}</span></p>
                                <p><span class="text-gray-500">Canales de envío:</span>
                                    @php
                                        $channels = $sendContract?->client?->contact_channels ?? [];
                                        $channelsLabel = $channels ? implode(' + ', array_map('ucfirst', $channels)) : 'Ninguno';
                                    @endphp
                                    <span class="font-medium">{{ $channelsLabel }}</span>
                                </p>
                                <p><span class="text-gray-500">Correo:</span> <span class="font-medium">{{ $sendContract?->client?->email ?? '—' }}</span></p>
                                <p><span class="text-gray-500">WhatsApp:</span> <span class="font-medium">{{ $sendContract?->client?->phone ?? '—' }}</span></p>
                            </div>
                            <p class="text-xs text-gray-400 mt-3">
                                Se enviará por {{ $channels ? implode(' y ', array_map(fn($c) => $c === 'email' ? 'correo electrónico con el PDF adjunto' : 'WhatsApp', $channels)) : 'ninguno (quedará activo sin envío)' }}.
                            </p>
                        @endif
                    </div>
                    <x-slot:footer>
                        @if($sentWhatsAppLink)
                            <x-ui.button variant="primary" wire:click="cancelSend">Listo</x-ui.button>
                        @else
                            <x-ui.button variant="success" icon="send" wire:click="sendContract({{ $confirmingSend }})">
                                Enviar y activar
                            </x-ui.button>
                            <x-ui.button variant="secondary" wire:click="cancelSend">Cancelar</x-ui.button>
                        @endif
                    </x-slot:footer>
                </x-ui.card>
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
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>
