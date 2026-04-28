<div class="max-w-4xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">list_alt</span>
                    Mis Solicitudes
                </h1>
                <p class="text-sm text-gray-500 mt-1">Solicitudes de materiales realizadas</p>
            </div>
            <a href="{{ route('mobile.technician.requests.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nueva Solicitud
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por código o ID"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="delivered">Entregada</option>
                        <option value="rejected">Rechazada</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            <!-- Listado de solicitudes -->
            <div class="space-y-4">
                @forelse($requests as $req)
                    <div
                        class="bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md transition overflow-hidden">
                        <div class="p-5">
                            <!-- Encabezado de la tarjeta -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        <span class="material-symbols-outlined text-sm">tag</span>
                                        #{{ $req->id }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div>
                                    @if($req->status == 'pending')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">schedule</span>
                                            Pendiente
                                        </span>
                                    @elseif($req->status == 'delivered')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            Entregada
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                            <span class="material-symbols-outlined text-sm">cancel</span>
                                            Rechazada
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Enlace a la OT asociada -->
                            @if($req->workOrder)
                                <div class="text-xs text-gray-500 mb-3 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">work</span>
                                    <strong>OT:</strong>
                                    <a href="{{ route('mobile.work-orders.show', $req->workOrder->id) }}"
                                        class="text-blue-600 hover:underline font-medium">
                                        #{{ $req->workOrder->id }} - {{ $req->workOrder->client_name }}
                                    </a>
                                    <span
                                        class="ml-1 text-gray-400">({{ $req->workOrder->scheduled_date?->format('d/m/Y') }})</span>
                                </div>
                            @else
                                <div class="text-xs text-gray-400 mb-3 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">work_off</span>
                                    <strong>OT:</strong> No asociada
                                </div>
                            @endif

                            <!-- Código de retiro -->
                            @if($req->status == 'pending' && $req->request_code)
                                <div class="mb-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-mono">
                                        <span class="material-symbols-outlined text-sm">key</span>
                                        Código: {{ $req->request_code }}
                                    </span>
                                </div>
                            @endif

                            <!-- Productos -->
                            <div class="text-sm text-gray-700 mb-3">
                                <strong class="flex items-center gap-1.5 mb-1">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                                    Productos:
                                </strong>
                                <ul class="list-disc list-inside ml-6 space-y-0.5">
                                    @foreach($req->products as $rp)
                                        <li>{{ $rp->product->name }}: <span
                                                class="font-medium">{{ $rp->quantity_requested }}</span></li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Notas -->
                            @if($req->notes)
                                <div class="text-xs text-gray-500 mb-4 flex items-start gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">sticky_note_2</span>
                                    <p>{{ $req->notes }}</p>
                                </div>
                            @endif

                            <!-- Botones de acción -->
                            @if($req->status == 'pending')
                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                    <a href="{{ route('mobile.technician.requests.edit', $req->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-yellow-600 text-xs font-medium rounded-lg hover:bg-yellow-50 transition">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                        Editar
                                    </a>
                                    <button type="button" onclick="showQR('{{ $req->qr_code }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-blue-600 text-xs font-medium rounded-lg hover:bg-blue-50 transition">
                                        <span class="material-symbols-outlined text-base">qr_code</span>
                                        Ver QR
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50/50 rounded-xl border border-dashed border-gray-300 py-12 text-center">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">inbox</span>
                        <p class="text-gray-500">No tienes solicitudes</p>
                        <p class="text-sm text-gray-400 mt-1">¡Crea una nueva!</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if($requests->hasPages())
                <div class="mt-5">
                    {{ $requests->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div
                    class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal QR (incluido dentro del mismo div raíz) -->
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