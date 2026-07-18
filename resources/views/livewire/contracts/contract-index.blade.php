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
                                <x-ui.badge :variant="match($contract->status) { 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'danger', default => 'neutral' }">
                                    {{ ucfirst($contract->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $contract->contract_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="createWorkOrder({{ $contract->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                                    <span class="material-symbols-outlined text-sm">engineering</span>
                                    Crear OT
                                </button>
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
