<div class="max-w-7xl mx-auto">
    <x-ui.card title="Panel de Bodega" icon="inventory" subtitle="Aprobación de requisiciones y seguimiento">

        {{-- Tabs --}}
        <div class="border-b border-gray-200 -mx-6 px-6 mb-5">
            <nav class="flex gap-0 -mb-px">
                <button wire:click="$set('activeTab', 'pending')"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 transition
                    {{ $activeTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="material-symbols-outlined text-base">inbox</span>
                    Pendientes
                </button>
                <button wire:click="$set('activeTab', 'history')"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 transition
                    {{ $activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="material-symbols-outlined text-base">history</span>
                    Historial
                </button>
            </nav>
        </div>

        @if($activeTab === 'history')
            {{-- ============ HISTORIAL ============ --}}
            @if($viewingHistory)
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <x-ui.button variant="ghost" icon="arrow_back" wire:click="backToHistory">Volver al historial</x-ui.button>
                        <span class="text-sm text-gray-500">Requisición #{{ $viewingHistory->id }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-xs text-gray-500 uppercase">Técnico</p>
                            <p class="text-sm font-semibold text-gray-800 mt-1">{{ $viewingHistory->technician?->name }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-xs text-gray-500 uppercase">Fecha</p>
                            <p class="text-sm text-gray-800 mt-1">{{ $viewingHistory->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-xs text-gray-500 uppercase">Estado</p>
                            <p class="text-sm font-semibold mt-1">
                                @if($viewingHistory->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Aprobada</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">Rechazada</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($viewingHistory->status === 'rejected' && $viewingHistory->rejection_reason)
                        <x-ui.alert variant="danger" icon="cancel" title="Motivo del rechazo">
                            {{ $viewingHistory->rejection_reason }}
                        </x-ui.alert>
                    @endif

                    {{-- Timeline de acciones --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-gray-500 text-lg">account_tree</span>
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Historial de acciones</h3>
                        </div>
                        @forelse($viewingHistory->logs as $log)
                            <div class="relative flex gap-4 pb-5 last:pb-0">
                                <div class="flex flex-col items-center">
                                    <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 mt-1
                                        {{ $log->action === 'approved' ? 'bg-green-500' : ($log->action === 'rejected' ? 'bg-red-500' : 'bg-blue-500') }}"></span>
                                    @if(!$loop->last)
                                        <span class="w-px flex-1 bg-gray-200"></span>
                                    @endif
                                </div>
                                <div class="pb-1">
                                    <p class="text-sm text-gray-800">{{ $log->description }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Sin acciones registradas.</p>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <x-ui.input type="text" wire:model.live.debounce.300ms="historySearch" icon="search" placeholder="Buscar por técnico..." class="flex-1" />
                    <x-ui.select wire:model.live="historyStatus" class="sm:w-48">
                        <option value="">Todos los estados</option>
                        <option value="approved">Aprobadas</option>
                        <option value="rejected">Rechazadas</option>
                    </x-ui.select>
                </div>
                <div class="space-y-3">
                    @forelse($history as $r)
                        <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition cursor-pointer"
                            wire:click="selectHistory({{ $r->id }})">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $r->status === 'approved' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                        <span class="material-symbols-outlined {{ $r->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">{{ $r->status === 'approved' ? 'check_circle' : 'cancel' }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">#{{ $r->id }} — {{ $r->technician?->name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $r->updated_at->format('d/m/Y H:i') }} · {{ $r->items_count }} producto(s)</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $r->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                        {{ $r->status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                                    </span>
                                    <span class="material-symbols-outlined text-gray-400">chevron_right</span>
                                </div>
                            </div>
                            @if($r->status === 'rejected' && $r->rejection_reason)
                                <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                    Motivo: {{ $r->rejection_reason }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">history</span>
                            <p class="text-gray-500 font-medium">Sin historial aún</p>
                            <p class="text-sm text-gray-400 mt-1">Las requisiciones aprobadas o rechazadas aparecerán aquí</p>
                        </div>
                    @endforelse
                </div>
                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            @endif
        @else
            @if($selectedRequisition)
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <x-ui.button variant="ghost" icon="arrow_back" wire:click="back">Volver al listado</x-ui.button>
                    <span class="text-sm text-gray-500">Requisición #{{ $selectedRequisition->id }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase">Técnico</p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">{{ $selectedRequisition->technician?->name }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase">Fecha</p>
                        <p class="text-sm text-gray-800 mt-1">{{ $selectedRequisition->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase">OTs vinculadas</p>
                        <p class="text-sm text-gray-800 mt-1">{{ $selectedRequisition->workOrders->pluck('code')->implode(', ') ?: '—' }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium w-20">Stock global</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium w-24">Inv. técnico</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium w-24">Solicitado</th>
                                <th class="px-4 py-3 text-center text-gray-600 font-medium w-24">Entregar</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Sucursal de origen</th>
                                <th class="px-4 py-3 text-left text-gray-600 font-medium">Stock en sucursal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($selectedRequisition->items as $item)
                                @php
                                    $selectedBranchId = $branchAssignments[$item->id]['source_branch_id'] ?? '';
                                    $branchInv = $selectedBranchId ? \App\Models\BranchInventory::where('branch_id', $selectedBranchId)->where('product_id', $item->product_id)->first() : null;
                                    $stockInBranch = $branchInv ? (float) $branchInv->allocated_quantity : 0;
                                    $product = $item->product;
                                    $globalStock = $product ? (float) $product->current_stock : 0;
                                    $isRemoved = in_array($item->id, $removedItems);
                                    $unitLabel = $product ? $product->unitLabel() : '';
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition {{ $isRemoved ? 'bg-red-50 opacity-60' : '' }}">
                                    <td class="px-4 py-3 text-gray-800">
                                        {{ $item->product?->name }}
                                        @if($unitLabel && $unitLabel !== 'unidad')
                                            <span class="text-xs text-gray-400 font-normal">· {{ $unitLabel }}</span>
                                        @endif
                                        @if($item->is_inherited)
                                            <span class="inline-flex items-center gap-0.5 text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium ml-1">
                                                <span class="material-symbols-outlined text-xs">archive</span> Heredado
                                            </span>
                                        @endif
                                        @if($isRemoved)<span class="text-xs text-red-600 ml-1 font-medium">(quitado)</span>@endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-semibold {{ $globalStock > 0 ? 'text-green-700' : 'text-gray-400' }}">
                                        {{ $globalStock > 0 ? $globalStock : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-medium">
                                        @php $techInv = $technicianInventory?->firstWhere('product_id', $item->product_id); @endphp
                                        @if($techInv)
                                            <span class="{{ (int) $techInv->quantity_in_hand > 0 ? 'text-blue-700' : 'text-gray-400' }}">{{ (int) $techInv->quantity_in_hand }}</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-medium text-gray-600">{{ $item->quantity_requested }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->is_inherited)
                                            <span class="text-xs text-blue-700 font-medium">Ya en inventario del técnico</span>
                                        @elseif($isRemoved)
                                            <span class="text-xs text-red-600 font-medium">Eliminado</span>
                                        @else
                                            <input type="number" wire:model="branchAssignments.{{ $item->id }}.quantity" min="0"
                                                max="{{ $item->quantity_requested }}"
                                                class="w-full px-3 py-2 text-center rounded-lg border border-gray-300 bg-white text-sm font-mono">
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!$isRemoved && !$item->is_inherited)
                                            <div class="w-full">
                                                <x-forms.label class="sr-only">Sucursal de origen</x-forms.label>
                                                <select wire:model.live="branchAssignments.{{ $item->id }}.source_branch_id"
                                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm appearance-none">
                                                    <option value="">Stock general ({{ $globalStock }} disp.)</option>
                                                    @foreach($allBranches as $b)
                                                        @php $bi = \App\Models\BranchInventory::where('branch_id', $b->id)->where('product_id', $item->product_id)->first();
                                                        $biQty = $bi ? (float) $bi->allocated_quantity : 0; @endphp
                                                        <option value="{{ $b->id }}" {{ $biQty <= 0 ? 'disabled' : '' }}>
                                                            {{ $b->name }} @if($bi)({{ $biQty }} disp.)@endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif($item->is_inherited)
                                            <span class="text-xs text-blue-700 font-medium">No aplica</span>
                                        @else
                                            <span class="text-xs text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-between gap-1">
                                            <div>
                                            @if($selectedBranchId && !$isRemoved && !$item->is_inherited)
                                                <span class="text-sm font-mono font-medium {{ $stockInBranch > 0 ? 'text-green-700' : 'text-red-600' }}">{{ $stockInBranch }}</span>
                                                <span class="text-xs text-gray-500">disp.</span>
                                            @elseif(!$isRemoved && !$item->is_inherited)
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                            </div>
                                            @if(!$isRemoved && !$item->is_inherited)
                                                <button type="button" wire:click="openSubstituteModal({{ $item->id }})" class="p-1 text-blue-500 hover:bg-blue-50 rounded transition" title="Cambiar producto">
                                                    <span class="material-symbols-outlined text-lg">swap_horiz</span>
                                                </button>
                                                <button type="button" wire:click="removeItem({{ $item->id }})" class="p-1 text-red-500 hover:bg-red-50 rounded transition" title="Quitar producto">
                                                    <span class="material-symbols-outlined text-lg">remove_circle</span>
                                                </button>
                                            @elseif(!$item->is_inherited)
                                                <button type="button" wire:click="restoreItem({{ $item->id }})" class="p-1 text-blue-500 hover:bg-blue-50 rounded transition" title="Restaurar producto">
                                                    <span class="material-symbols-outlined text-lg">undo</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($selectedRequisition->notes)
                    <x-ui.alert variant="info" icon="sticky_note_2" title="Notas del técnico">
                        {{ $selectedRequisition->notes }}
                    </x-ui.alert>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <x-ui.button variant="danger" wire:click="confirmReject">Rechazar</x-ui.button>
                    <x-ui.button variant="primary" icon="check_circle" wire:click="confirmApprove" class="!bg-green-600 hover:!bg-green-700">Aprobar y entregar</x-ui.button>
                </div>
            </div>
        @else
            <div class="mb-4">
                <x-ui.input type="text" wire:model.live.debounce.300ms="pendingSearch" icon="search" placeholder="Buscar por técnico..." />
            </div>
            @if($requisitions->isEmpty())
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inventory</span>
                    <p class="text-gray-500 font-medium">No hay requisiciones pendientes</p>
                    <p class="text-sm text-gray-400 mt-1">Las solicitudes de los técnicos aparecerán aquí</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($requisitions as $r)
                        <div class="border border-gray-200 rounded-xl p-4 hover:border-amber-300 hover:shadow-sm transition cursor-pointer"
                            wire:click="selectRequisition({{ $r->id }})">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-amber-600">assignment</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">#{{ $r->id }} — {{ $r->technician?->name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $r->created_at->format('d/m/Y H:i') }} · {{ $r->items->count() }} producto(s)</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Pendiente</span>
                                    <span class="material-symbols-outlined text-gray-400">chevron_right</span>
                                </div>
                            </div>
                            @if($r->items->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($r->items as $item)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-700">
                                            {{ $item->product?->name ?? '—' }}
                                            <span class="font-mono font-medium text-gray-900">×{{ $item->quantity_requested }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $requisitions->links() }}
                </div>
            @endif
        @endif
        @endif
    </x-ui.card>

    {{-- Modal aprobar --}}
    <div x-data="{ show: @entangle('showApproveModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center">Aprobar requisición</h3>
                    <p class="text-sm text-gray-600 mt-2 text-center">Revisá el resumen antes de confirmar. Se descontará stock y se asignarán los dispositivos al técnico.</p>

                    <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Producto</th>
                                    <th class="px-3 py-2 text-center text-gray-600 font-medium">Solicitado</th>
                                    <th class="px-3 py-2 text-center text-gray-600 font-medium">Entregar</th>
                                    <th class="px-3 py-2 text-left text-gray-600 font-medium">Origen</th>
                                    <th class="px-3 py-2 text-center text-gray-600 font-medium">Stock disp.</th>
                                    <th class="px-3 py-2 text-center text-gray-600 font-medium">Disp. a asignar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($approvalSummary as $s)
                                    <tr class="{{ $s['removed'] ? 'bg-red-50 opacity-60' : '' }}">
                                        <td class="px-3 py-2.5 text-gray-800">
                                            {{ $s['product_name'] }}
                                            @if(!empty($s['unit']) && $s['unit'] !== 'unidad')
                                                <span class="text-xs text-gray-400 font-normal">· {{ $s['unit'] }}</span>
                                            @endif
                                            @if($s['inherited'])
                                                <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium ml-1">Heredado</span>
                                            @endif
                                            @if($s['removed'])
                                                <span class="text-xs text-red-600 font-medium ml-1">(quitado)</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-center font-mono text-gray-600">{{ $s['requested_qty'] }}</td>
                                        <td class="px-3 py-2.5 text-center font-mono font-semibold {{ $s['removed'] ? 'text-red-500' : 'text-green-700' }}">
                                            {{ $s['removed'] ? '—' : $s['qty'] }}
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-700">{{ $s['source_branch_name'] ?? 'Stock general' }}</td>
                                        <td class="px-3 py-2.5 text-center font-mono {{ $s['stock_available'] > 0 ? 'text-gray-700' : 'text-red-600' }}">{{ $s['stock_available'] }}</td>
                                        <td class="px-3 py-2.5 text-center font-mono text-gray-700">{{ $s['removed'] ? '—' : $s['device_count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="primary" wire:click="approve" class="!bg-green-600 hover:!bg-green-700">Sí, aprobar</x-ui.button>
                    <button @click="show = false" wire:click="$set('showApproveModal', false)"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal rechazar --}}
    <div x-data="{ show: @entangle('showRejectModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">cancel</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center">Rechazar requisición</h3>
                    <p class="text-sm text-gray-500 mt-2 text-center">Indicá el motivo del rechazo (obligatorio)</p>
                    <textarea wire:model="rejectionReason" rows="3"
                        class="mt-4 w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm {{ $errors->has('rejectionReason') ? 'border-red-300 bg-red-50' : '' }}"
                        placeholder="Motivo..."></textarea>
                    @error('rejectionReason')
                        <x-forms.error>{{ $message }}</x-forms.error>
                    @enderror
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3 sm:flex-row-reverse">
                    <x-ui.button variant="danger" wire:click="reject">Rechazar</x-ui.button>
                    <button @click="show = false" wire:click="$set('showRejectModal', false)"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de cambio de producto --}}
    <div x-data="{ show: @entangle('showSubstituteModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">swap_horiz</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Cambiar producto</h3>
                            <p class="text-xs text-gray-500">Seleccioná un producto sustituto</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSubstituteModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="substituteListSearch"
                            placeholder="Filtrar por nombre o SKU..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($substituteList as $p)
                        <button type="button" wire:click="selectSubstitute({{ $p->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-blue-600">inventory_2</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ $p->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $p->sku }} · Stock: {{ (float) $p->current_stock }}</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-lg flex-shrink-0">chevron_right</span>
                        </button>
                    @empty
                        <div class="py-12 text-center">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">search_off</span>
                            <p class="text-gray-500 text-sm">No se encontraron productos</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <x-ui.button variant="secondary" wire:click="closeSubstituteModal">Cerrar</x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
