<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                    Mis Requisiciones
                </h1>
                <p class="text-sm text-gray-500 mt-1">Material solicitado para tus órdenes de trabajo</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ $hasPending ? '#' : route('technician.requisitions.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 {{ $hasPending ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-sm font-medium rounded-lg shadow-sm transition">
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    {{ $hasPending ? 'Pendiente de aprobación' : 'Nueva Requisición' }}
                </a>
                <button wire:click="closeWeek"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                    <span class="material-symbols-outlined text-base">event_available</span>
                    Cierre Semanal
                </button>
            </div>
        </div>

        <div class="p-6">
            @if($requisitions->isEmpty())
                <div class="text-center py-12 bg-gray-50/50 rounded-xl border border-dashed border-gray-300">
                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inventory</span>
                    <p class="text-gray-500">No tienes requisiciones registradas</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($requisitions as $req)
                        <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <div>
                                    <span class="font-mono text-sm font-semibold text-gray-700">#{{ $req->id }}</span>
                                    <span class="text-xs text-gray-500 ml-2">{{ $req->created_at->format('d/m/Y') }}</span>
                                    @php
                                        $statusMap = ['closed' => ['Cerrada', 'bg-gray-100 text-gray-600'], 'heredada' => ['Heredada', 'bg-yellow-50 text-yellow-700'], 'open' => ['Abierta', 'bg-green-50 text-green-700'], 'pending' => ['Pendiente', 'bg-amber-50 text-amber-700'], 'approved' => ['Aprobada', 'bg-blue-50 text-blue-700'], 'rejected' => ['Rechazada', 'bg-red-50 text-red-700']];
                                        $s = $statusMap[$req->status] ?? [$req->status, 'bg-gray-50 text-gray-600'];
                                    @endphp
                                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ $s[1] }}">{{ $s[0] }}</span>
                                </div>
                                <a href="{{ route('technician.requisitions.show', $req->id) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Ajustar consumo →
                                </a>
                            </div>
                            @if($req->workOrders->isNotEmpty())
                                <p class="text-xs text-gray-500 mb-2">Órdenes: 
                                    @foreach($req->workOrders as $wo)
                                        #{{ $wo->id }}@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            @endif
                            <div class="text-sm text-gray-700">
                                <span class="font-medium">Productos:</span>
                                @foreach($req->items as $item)
                                    <span class="ml-1">{{ $item->product->name }} ({{ $item->quantity_requested }})</span>@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>
    <style>[x-cloak] { display: none !important; }</style>
</div>