<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold text-gray-800">Solicitudes de Materiales</h1>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <input type="text" wire:model.live="search" placeholder="Buscar por código o técnico..."
            class="flex-1 min-w-[200px] rounded-md border-gray-300 text-sm">
        <select wire:model.live="statusFilter" class="rounded-md border-gray-300 text-sm">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="delivered">Entregada</option>
            <option value="rejected">Rechazada</option>
        </select>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Técnico</th>
                    <th class="px-4 py-2 text-left">OT Asociada</th>
                    <th class="px-4 py-2 text-left">Productos</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2 text-left">Código</th>
                    <th class="px-4 py-2 text-left">Fecha</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">#{{ $req->id }}</td>
                        <td class="px-4 py-2">{{ $req->technician->name }}</td>
                        <td class="px-4 py-2">
                            @if($req->workOrder)
                                #{{ $req->workOrder->id }} - {{ $req->workOrder->client_name }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @foreach($req->products as $rp)
                                {{ $rp->product->name }}: {{ $rp->quantity_requested }}<br>
                            @endforeach
                        </td>
                        <td class="px-4 py-2">
                            @if($req->status == 'pending')
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                            @elseif($req->status == 'delivered')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Entregada</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Rechazada</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $req->request_code ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($req->status == 'pending')
                                <a href="{{ route('technician-requests.approve', $req->id) }}"
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                    Procesar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-400">No hay solicitudes</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>

    @if(session('message'))
        <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mt-2 text-sm text-red-600">{{ session('error') }}</div>
    @endif

    <!-- TOAST DE NOTIFICACIONES (agregado) -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300" style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"></span>
        </div>
        <div x-show="toastType === 'info'"
            class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">info</span> <span x-text="toastMessage"></span>
        </div>
    </div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>