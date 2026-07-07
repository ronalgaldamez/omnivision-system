<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-amber-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-600 text-2xl">inventory</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Panel de Bodega</h1>
                    <p class="text-sm text-gray-500">Aprobación de requisiciones pendientes</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($selectedRequisition)
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <button type="button" wire:click="back" class="text-sm text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">arrow_back</span>
                            Volver al listado
                        </button>
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
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium w-28">Solicitado</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium w-28">Entregar</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Sucursal de origen</th>
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Stock en sucursal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($selectedRequisition->items as $item)
                                @php
                                    $selectedBranchId = $branchAssignments[$item->id]['source_branch_id'] ?? '';
                                    $branchInv = $selectedBranchId ? \App\Models\BranchInventory::where('branch_id', $selectedBranchId)->where('product_id', $item->product_id)->first() : null;
                                    $stockInBranch = $branchInv ? (int) $branchInv->allocated_quantity : 0;
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3 text-gray-800">{{ $item->product?->name }}</td>
                                    <td class="px-4 py-3 text-center font-mono font-medium text-gray-600">{{ $item->quantity_requested }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" wire:model="branchAssignments.{{ $item->id }}.quantity"
                                            min="0" max="{{ $item->quantity_requested }}"
                                            class="w-full px-3 py-2 text-center rounded-lg border border-gray-300 bg-white text-sm font-mono">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select wire:model.live="branchAssignments.{{ $item->id }}.source_branch_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm">
                                            <option value="">Stock general (sin sucursal)</option>
                                            @foreach($allBranches as $b)
                                                @php $bi = \App\Models\BranchInventory::where('branch_id', $b->id)->where('product_id', $item->product_id)->first(); $biQty = $bi ? (int) $bi->allocated_quantity : 0; @endphp
                                                <option value="{{ $b->id }}" {{ $biQty <= 0 ? 'disabled' : '' }}>
                                                    {{ $b->name }} @if($bi)({{ $biQty }} disp.)@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($selectedBranchId)
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-mono font-medium {{ $stockInBranch > 0 ? 'text-green-700' : 'text-red-600' }}">{{ $stockInBranch }}</span>
                                                <span class="text-xs text-gray-500">disponibles</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($selectedRequisition->notes)
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-xs text-gray-500 uppercase">Notas del técnico</p>
                        <p class="text-sm text-gray-700 mt-1">{{ $selectedRequisition->notes }}</p>
                    </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="confirmReject" class="px-5 py-2.5 border border-red-200 text-red-600 text-sm font-medium rounded-lg bg-white hover:bg-red-50 transition">
                            Rechazar
                        </button>
                        <button type="button" wire:click="confirmApprove" class="px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-green-700 transition">
                            Aprobar y entregar
                        </button>
                    </div>
                </div>
            @else
                @if($requisitions->isEmpty())
                    <div class="text-center py-12">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inventory</span>
                        <p class="text-gray-500 font-medium">No hay requisiciones pendientes</p>
                        <p class="text-sm text-gray-400 mt-1">Las solicitudes de los técnicos aparecerán aquí</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($requisitions as $r)
                        <div class="border border-gray-200 rounded-xl p-4 hover:border-amber-300 hover:shadow-sm transition cursor-pointer" wire:click="selectRequisition({{ $r->id }})">
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
                @endif
            @endif
        </div>
    </div>

    {{-- Modal aprobar --}}
    <div x-data="{ show: @entangle('showApproveModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Aprobar requisición</h3>
                    <p class="text-sm text-gray-600 mt-2">¿Estás seguro de aprobar esta requisición? Se descontará el stock de las sucursales seleccionadas y se asignarán los dispositivos al técnico.</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3 sm:flex-row-reverse">
                    <button wire:click="approve" class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">Sí, aprobar</button>
                    <button @click="show = false" wire:click="$set('showApproveModal', false)" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal rechazar --}}
    <div x-data="{ show: @entangle('showRejectModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">cancel</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center">Rechazar requisición</h3>
                    <p class="text-sm text-gray-500 mt-2 text-center">Indicá el motivo del rechazo</p>
                    <textarea wire:model="rejectionReason" rows="3" class="mt-4 w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm" placeholder="Motivo..."></textarea>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3 sm:flex-row-reverse">
                    <button wire:click="reject" class="w-full sm:w-auto px-5 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-red-700 transition">Rechazar</button>
                    <button @click="show = false" wire:click="$set('showRejectModal', false)" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
