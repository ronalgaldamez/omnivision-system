<div class="max-w-lg mx-auto px-3 py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-800">Mis Solicitudes</h1>
        <a href="{{ route('mobile.technician.requests.create') }}"
            class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-1 shadow">
            <span class="material-symbols-outlined text-base">add</span> Nueva
        </a>
    </div>

    <div class="mb-4 flex gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por código o ID"
            class="flex-1 rounded-lg border-gray-300 text-sm">
        <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 text-sm w-32">
            <option value="">Todos</option>
            <option value="pending">Pendiente</option>
            <option value="delivered">Entregada</option>
            <option value="rejected">Rechazada</option>
        </select>
    </div>

    <div class="space-y-3">
        @forelse($requests as $req)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-xs text-gray-500">#{{ $req->id }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        @if($req->status == 'pending')
                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Pendiente</span>
                        @elseif($req->status == 'delivered')
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Entregada</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Rechazada</span>
                        @endif
                    </div>
                </div>

                <!-- Enlace a la OT asociada -->
                @if($req->workOrder)
                    <div class="text-xs text-gray-500 mb-2">
                        <strong>OT:</strong>
                        <a href="{{ route('mobile.work-orders.show', $req->workOrder->id) }}"
                            class="text-blue-600 hover:underline">
                            #{{ $req->workOrder->id }} - {{ $req->workOrder->client_name }}
                        </a>
                        <span class="ml-1">({{ $req->workOrder->scheduled_date?->format('d/m/Y') }})</span>
                    </div>
                @else
                    <div class="text-xs text-gray-500 mb-2">
                        <strong>OT:</strong> No asociada
                    </div>
                @endif

                @if($req->status == 'pending' && $req->request_code)
                    <div class="mb-2">
                        <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">Código: {{ $req->request_code }}</span>
                    </div>
                @endif

                <div class="text-sm text-gray-700 mb-2">
                    <strong>Productos:</strong>
                    <ul class="list-disc list-inside ml-2">
                        @foreach($req->products as $rp)
                            <li>{{ $rp->product->name }}: {{ $rp->quantity_requested }}</li>
                        @endforeach
                    </ul>
                </div>

                @if($req->notes)
                    <div class="text-xs text-gray-500 mb-3">
                        <strong>Notas:</strong> {{ $req->notes }}
                    </div>
                @endif

                <div class="flex justify-end gap-2 mt-2">
                    @if($req->status == 'pending')
                        <a href="{{ route('mobile.technician.requests.edit', $req->id) }}"
                            class="text-yellow-600 text-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">edit</span> Editar
                        </a>
                        <button type="button" onclick="showQR('{{ $req->qr_code }}')"
                            class="text-blue-600 text-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">qr_code</span> Ver QR
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-8">No tienes solicitudes. ¡Crea una nueva!</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>

    <!-- Modal QR -->
    <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl p-4 max-w-sm w-full text-center">
            <img id="qrImage" src="" class="w-64 h-64 mx-auto">
            <button onclick="closeQR()" class="mt-4 bg-gray-600 text-white px-4 py-2 rounded-lg w-full">Cerrar</button>
        </div>
    </div>

    <script>
        function showQR(qrDataUrl) {
            document.getElementById('qrImage').src = qrDataUrl;
            document.getElementById('qrModal').classList.remove('hidden');
        }
        function closeQR() {
            document.getElementById('qrModal').classList.add('hidden');
        }
    </script>
</div>