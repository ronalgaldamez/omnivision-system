<div class="max-w-7xl mx-auto space-y-6">
    @if($drafts->isNotEmpty())
        <x-ui.card title="Mis borradores" subtitle="Cotizaciones que todavía no enviaste a aprobación" icon="drafts">
            <div class="space-y-3">
                @foreach($drafts as $d)
                    <div class="border border-dashed border-gray-300 rounded-xl p-4 bg-gray-50/40">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-700">{{ $d->code }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-200 text-gray-600">Borrador</span>
                                    @if($d->supplier)
                                        <span class="text-xs text-gray-500">· {{ $d->supplier->name }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $d->items->count() }} producto(s) · actualizado {{ $d->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <x-ui.button variant="secondary" size="sm" icon="edit_note" href="{{ route('bodega.quotations.edit', $d->id) }}">Continuar</x-ui.button>
                                <x-ui.button variant="ghost" size="sm" icon="delete" wire:click="askDeleteDraft({{ $d->id }})">Eliminar</x-ui.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif

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
                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                                {{ $item->product?->name ?? $item->pending_name ?? '?' }}
                                @if($item->isPending())<span class="text-amber-600 font-medium"> · nuevo</span>@endif
                                ×{{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }}
                            </span>
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
                            <x-ui.button variant="ghost" size="sm" icon="visibility" href="{{ route('bodega.quotations.show', $q->id) }}">Ver</x-ui.button>
                            @if($q->status === 'pending' && $canApprove)
                                <x-ui.button variant="success" size="sm" icon="check" wire:click="confirmApprove({{ $q->id }})">Aprobar</x-ui.button>
                                <x-ui.button variant="danger" size="sm" icon="block" wire:click="openReject({{ $q->id }})">Rechazar</x-ui.button>
                            @elseif($q->status === 'approved' && $canPay)
                                <x-ui.button variant="warning" size="sm" icon="payments" wire:click="confirmPay({{ $q->id }})">Marcar pagada</x-ui.button>
                            @elseif($q->status === 'paid')
                                <x-ui.button variant="success" size="sm" icon="inventory_2" wire:click="confirmReceive({{ $q->id }})">Recibir (generar compra)</x-ui.button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <x-ui.empty-state icon="request_quote" title="No hay cotizaciones registradas"
                    description="Las cotizaciones creadas por el bodeguero aparecerán acá para su aprobación.">
                    @can('create quotations')
                        <x-slot:action>
                            <x-ui.button variant="primary" icon="add_circle" href="{{ route('bodega.quotations.create') }}">Nueva cotización</x-ui.button>
                        </x-slot:action>
                    @endcan
                </x-ui.empty-state>
            @endforelse
        </div>
        @if($quotations->hasPages())<div class="mt-4">{{ $quotations->links() }}</div>@endif

        @if(session('message'))
            <x-ui.alert variant="success" class="mt-4">{{ session('message') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger" class="mt-4">{{ session('error') }}</x-ui.alert>
        @endif
    </x-ui.card>

    {{-- Modal rechazo (requiere motivo) --}}
    <div x-data="{ show: @entangle('showRejectModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-red-50 to-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-600">block</span>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold">Rechazar cotización</h3>
                        <p class="text-xs text-gray-500">Indicá el motivo del rechazo</p>
                    </div>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del rechazo</label>
                    <textarea wire:model="rejectionReason" rows="3"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition resize-none"></textarea>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <x-ui.button variant="secondary" wire:click="$set('showRejectModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="danger" wire:click="reject">Rechazar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modales de confirmación (patrón canónico) --}}
    @if($confirmingAction === 'approve')
        <x-ui.confirm-modal variant="primary" icon="check" title="Aprobar cotización"
            message="¿Aprobar la cotización #{{ $confirmingId }}? El subgerente podrá pagarla después."
            confirmLabel="Sí, aprobar" cancelLabel="Cancelar"
            confirmAction="executeConfirmedAction" cancelAction="cancelConfirmation" id="confirm-approve" />
    @elseif($confirmingAction === 'pay')
        <x-ui.confirm-modal variant="warning" icon="payments" title="Marcar cotización como pagada"
            message="¿Confirmás el pago de la cotización #{{ $confirmingId }}? Se esperará la recepción del producto."
            confirmLabel="Sí, marcar pagada" cancelLabel="Cancelar"
            confirmAction="executeConfirmedAction" cancelAction="cancelConfirmation" id="confirm-pay" />
    @elseif($confirmingAction === 'receive')
        <x-ui.confirm-modal variant="success" icon="inventory_2" title="Recibir cotización"
            message="¿Confirmás la recepción de la cotización #{{ $confirmingId }}? Se generará la compra y entrará el stock."
            confirmLabel="Sí, recibir y generar compra" cancelLabel="Cancelar"
            confirmAction="executeConfirmedAction" cancelAction="cancelConfirmation" id="confirm-receive" />
    @elseif($confirmingAction === 'delete_draft')
        <x-ui.confirm-modal variant="danger" icon="delete" title="Eliminar borrador"
            message="¿Eliminar el borrador #{{ $confirmingId }}? Se perderán sus productos."
            confirmLabel="Sí, eliminar" cancelLabel="Cancelar"
            confirmAction="executeConfirmedAction" cancelAction="cancelConfirmation" id="confirm-delete-draft" />
    @endif
</div>
