<div class="max-w-7xl mx-auto">
    <x-ui.card title="Cotizaciones" icon="request_quote" subtitle="Flujo de aprobación de compras por empresa">
        <x-slot:headerActions>
            @can('create quotations')
                <x-ui.button variant="primary" icon="add_circle" href="{{ route('bodega.quotations.create') }}">
                    Nueva cotización
                </x-ui.button>
            @endcan
        </x-slot:headerActions>

        <x-ui.select wire:model.live="statusFilter" placeholder="Todos" label="">
            <option value="">Todas</option>
            <option value="pending">Pendientes</option>
            <option value="approved">Aprobadas</option>
            <option value="paid">Pagadas</option>
            <option value="received">Recibidas</option>
            <option value="rejected">Rechazadas</option>
        </x-ui.select>

        <div class="space-y-3 mt-4">
            @forelse($quotations as $q)
            @php
                $colors = [
                    'pending' => ['Pendiente', 'bg-gray-100 text-gray-700'],
                    'approved' => ['Aprobada', 'bg-blue-50 text-blue-700'],
                    'paid' => ['Pagada', 'bg-purple-50 text-purple-700'],
                    'received' => ['Recibida', 'bg-green-50 text-green-700'],
                    'rejected' => ['Rechazada', 'bg-red-50 text-red-700'],
                ];
                $st = $colors[$q->status] ?? [$q->status, 'bg-gray-100 text-gray-700'];
                $canApprove = auth()->user()->can('approve quotations');
                $canPay = auth()->user()->can('pay quotations');
            @endphp
            <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-gray-800">{{ $q->code }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $st[1] }}">{{ $st[0] }}</span>
                            @if($q->status === 'rejected')
                                <span class="text-xs text-red-600">· {{ $q->rejection_reason }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="material-symbols-outlined text-xs align-middle">warehouse</span> {{ $q->supplier?->name }}
                            · {{ $q->branch?->name }} · creada por {{ $q->creator?->name }} · {{ $q->created_at->format('d/m/Y') }}
                        </p>
                        @if($q->items->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($q->items as $item)
                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">{{ $item->product?->name ?? '?' }} ×{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-gray-800 font-mono">${{ number_format($q->total, 2) }}</p>
                        <p class="text-xs text-gray-400">IVA: ${{ number_format($q->iva_amount, 2) }}</p>
                        @if($q->approved_by)
                            <p class="text-[10px] text-gray-400 mt-1">Aprobó: {{ $q->approver?->name }}</p>
                        @endif
                        @if($q->paid_by)
                            <p class="text-[10px] text-gray-400">Pagó: {{ $q->payer?->name }}</p>
                        @endif
                        <div class="flex items-center justify-end gap-1.5 mt-2">
                            @if($q->status === 'pending' && $canApprove)
                                <button wire:click="approve({{ $q->id }})" class="px-2.5 py-1 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Aprobar</button>
                                <button wire:click="openReject({{ $q->id }})" class="px-2.5 py-1 text-xs font-medium bg-red-50 text-red-700 rounded-lg hover:bg-red-100">Rechazar</button>
                            @elseif($q->status === 'approved' && $canPay)
                                <button wire:click="markPaid({{ $q->id }})" class="px-2.5 py-1 text-xs font-medium bg-purple-600 text-white rounded-lg hover:bg-purple-700">Marcar pagada</button>
                            @elseif($q->status === 'paid')
                                <button wire:click="receive({{ $q->id }})" class="px-2.5 py-1 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700">Recibir (generar compra)</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500">No hay cotizaciones registradas</div>
            @endforelse
        </div>
        @if($quotations->hasPages())<div class="mt-4">{{ $quotations->links() }}</div>@endif
    </x-ui.card>

    {{-- Modal rechazo --}}
    <div x-data="{ show: @entangle('showRejectModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold">Rechazar cotización</h3>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del rechazo</label>
                    <textarea wire:model="rejectionReason" rows="3" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm"></textarea>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <x-ui.button variant="secondary" wire:click="$set('showRejectModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="danger" wire:click="reject">Rechazar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
