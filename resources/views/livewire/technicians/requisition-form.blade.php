<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                Nueva Requisición de Material
            </h1>
            <p class="text-sm text-gray-500 mt-1">Solicitud de material para órdenes de trabajo asignadas</p>
        </div>

        <div class="p-6">
            <form wire:submit.prevent="promptSave" class="space-y-6">

                <!-- Selección de Órdenes de Trabajo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                        Órdenes de Trabajo *
                    </label>

                    @php
                        $allWorkOrderIds = $workOrders->pluck('id')->toArray();
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($workOrders as $wo)
                            <div class="flex items-center gap-2 p-2 bg-gray-50/80 rounded-lg border border-gray-200">
                                <input type="checkbox" value="{{ $wo->id }}" wire:model="selectedWorkOrders"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                <span class="text-sm text-gray-700 flex-1">
                                    {{ $wo->code ?? 'OT-'.$wo->id }} - {{ $wo->client->name ?? 'N/A' }}
                                </span>
                                <button type="button"
                                    wire:click="openPreviewWorkOrder({{ $wo->id }}, {{ json_encode($allWorkOrderIds) }})"
                                    class="p-1 text-blue-600 hover:bg-blue-50 rounded-lg transition flex-shrink-0"
                                    title="Ver detalle de la OT">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @error('selectedWorkOrders') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Inventario actual del técnico -->
                @if(count($technicianStock))
                <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                    <h3 class="text-sm font-semibold text-blue-800 flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-blue-500 text-base">inventory</span>
                        Tu inventario actual
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($technicianStock as $productId => $data)
                            <div class="text-sm text-blue-700">
                                <span class="font-medium">{{ $data['name'] }}</span>:
                                <span class="font-mono">{{ $data['quantity'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Agregar productos -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-gray-500">add_shopping_cart</span>
                        Material solicitado
                    </h2>

                    <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Producto</label>
                                <input type="text" wire:model.live.debounce.300ms="currentProductSearch"
                                    placeholder="Buscar por nombre o SKU..."
                                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-7 text-gray-400 text-lg">search</span>
                                @if(count($productResults))
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach($productResults as $product)
                                            <li wire:click="selectProduct({{ $product->id }})"
                                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad</label>
                                <input type="number" step="any" wire:model="currentQuantity"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="addItem"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                Agregar producto
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de productos agregados -->
                    @if(count($items))
                    <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 text-gray-800">{{ $item['product_name'] }} ({{ $item['product_sku'] }})</td>
                                    <td class="px-4 py-3 text-center">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                        Notas
                    </label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                        placeholder="Notas opcionales..."></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('technician.requisitions.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Guardar Requisición
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ★ MODAL DE VISTA PREVIA (COMPLETO) ★ --}}
    @if($previewWorkOrder)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">

                    {{-- Cabecera --}}
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">work</span>
                                {{ $previewWorkOrder->code ?? 'OT-'.$previewWorkOrder->id }}
                            </h3>
                            @if($previewWorkOrder->ticket?->priority)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                    @switch($previewWorkOrder->ticket->priority)
                                        @case('P1') bg-red-100 text-red-700 @break
                                        @case('P2') bg-orange-100 text-orange-700 @break
                                        @case('P3') bg-blue-100 text-blue-700 @break
                                        @case('P4') bg-gray-100 text-gray-600 @break
                                    @endswitch">
                                    <span class="material-symbols-outlined text-sm">flag</span>
                                    {{ $previewWorkOrder->ticket->priority }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($previewWorkOrder->status == 'pending')
                                <span class="px-2.5 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">Pendiente</span>
                            @elseif($previewWorkOrder->status == 'in_progress')
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">En progreso</span>
                            @else
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">{{ $previewWorkOrder->status }}</span>
                            @endif
                            <button type="button" wire:click="closePreviewWorkOrder" class="text-gray-400 hover:text-gray-600 transition ml-2">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- 1. Datos del Cliente --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                Datos del Cliente
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">person</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Nombre</p>
                                        <p class="text-sm text-gray-800 font-medium">{{ $previewWorkOrder->client->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">call</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Teléfono</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->client->phone ?? 'No especificado' }}</p>
                                    </div>
                                </div>
                                @if($previewWorkOrder->client->document_type && $previewWorkOrder->client->document_number)
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">fingerprint</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Documento</p>
                                        <p class="text-sm text-gray-800">{{ strtoupper($previewWorkOrder->client->document_type) }}: {{ $previewWorkOrder->client->document_number }}</p>
                                    </div>
                                </div>
                                @endif
                                @if($previewWorkOrder->client->email)
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">mail</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Correo</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->client->email }}</p>
                                    </div>
                                </div>
                                @endif
                                @if($previewWorkOrder->client->service)
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">tv</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Servicio</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->client->service }}</p>
                                    </div>
                                </div>
                                @endif
                                @if($previewWorkOrder->client->nro_luz)
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">bolt</span>
                                    <div>
                                        <p class="text-xs text-gray-500">N.° de luz</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->client->nro_luz }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg sm:col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Dirección</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->client->address ?? 'No especificada' }}</p>
                                    </div>
                                </div>
                                @if($previewWorkOrder->client->installation_address)
                                <div class="flex items-start gap-2 p-2 bg-gray-50/80 rounded-lg sm:col-span-2">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">home_pin</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Instalación</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->client->installation_address }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- 2. Ticket --}}
                        @if($previewWorkOrder->ticket)
                        <div class="border-t border-gray-100 pt-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">confirmation_number</span>
                                Ticket #{{ $previewWorkOrder->ticket->id }}
                                @if($previewWorkOrder->ticket->ticket_code)
                                    <span class="text-xs text-gray-500 font-mono">({{ $previewWorkOrder->ticket->ticket_code }})</span>
                                @endif
                            </h4>
                            <div class="bg-blue-50/50 rounded-lg border border-blue-100 p-4">
                                <p class="text-xs text-blue-600 font-medium mb-2">Problema reportado por el cliente:</p>
                                <p class="text-sm text-gray-800 whitespace-pre-line">
                                    {{ $previewWorkOrder->ticket->description ?? 'Sin descripción' }}
                                </p>
                                <div class="flex flex-wrap gap-3 mt-3 text-xs text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">source</span>
                                        Origen: {{ $previewWorkOrder->ticket->origin ?? 'N/A' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                                        Creado: {{ $previewWorkOrder->ticket->created_at?->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- 3. Notas --}}
                        @if($previewWorkOrder->notes)
                        <div class="border-t border-gray-100 pt-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                                Notas de la orden
                            </h4>
                            <div class="bg-yellow-50/50 rounded-lg border border-yellow-100 p-4">
                                <p class="text-sm text-gray-700">{{ $previewWorkOrder->notes }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- 4. Info OT --}}
                        <div class="border-t border-gray-100 pt-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">info</span>
                                Información de la OT
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div class="flex items-center gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">calendar_month</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Programada</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">handyman</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Tipo</p>
                                        <p class="text-sm text-gray-800">{{ $previewWorkOrder->service_type ?? 'No especificado' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 p-2 bg-gray-50/80 rounded-lg">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Material</p>
                                        <p class="text-sm text-gray-800">
                                            @if($previewWorkOrder->requisitions()->where('status', 'open')->exists())
                                                <span class="text-green-600">Asignado</span>
                                            @else
                                                <span class="text-red-500">Sin asignar</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($previewWorkOrder->latitude && $previewWorkOrder->longitude)
                                <div class="flex items-center gap-2 p-2 bg-gray-50/80 rounded-lg sm:col-span-3">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">explore</span>
                                    <div>
                                        <p class="text-xs text-gray-500">Coordenadas</p>
                                        <p class="text-sm text-gray-800">
                                            {{ number_format($previewWorkOrder->latitude, 6) }}, {{ number_format($previewWorkOrder->longitude, 6) }}
                                            <a href="https://www.google.com/maps?q={{ $previewWorkOrder->latitude }},{{ $previewWorkOrder->longitude }}" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2 text-xs">
                                                Ver en mapa ↗
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- 5. Productos sugeridos --}}
                        @if($previewWorkOrder->products?->count())
                        <div class="border-t border-gray-100 pt-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">package_2</span>
                                Productos sugeridos
                            </h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-3 py-2 text-left text-gray-600 font-medium">Producto</th>
                                            <th class="px-3 py-2 text-center text-gray-600 font-medium">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($previewWorkOrder->products as $item)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-800">{{ $item->product->name }}</td>
                                                <td class="px-3 py-2 text-center font-mono text-gray-800">{{ $item->quantity }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Slider --}}
                    @if($previewTotal > 1)
                    <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <button type="button" wire:click="previewPrev"
                            @class([
                                'flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition',
                                'text-gray-400 cursor-not-allowed' => $previewIndex === 0,
                                'text-blue-600 hover:bg-blue-50' => $previewIndex > 0,
                            ])
                            @disabled($previewIndex === 0)>
                            <span class="material-symbols-outlined text-base">arrow_back</span>
                            Anterior
                        </button>
                        <span class="text-xs text-gray-500 font-medium">
                            OT {{ $previewIndex + 1 }} de {{ $previewTotal }}
                        </span>
                        <button type="button" wire:click="previewNext"
                            @class([
                                'flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition',
                                'text-gray-400 cursor-not-allowed' => $previewIndex === $previewTotal - 1,
                                'text-blue-600 hover:bg-blue-50' => $previewIndex < $previewTotal - 1,
                            ])
                            @disabled($previewIndex === $previewTotal - 1)>
                            Siguiente
                            <span class="material-symbols-outlined text-base">arrow_forward</span>
                        </button>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    @endif

    <!-- Modal de confirmación -->
    @if($confirmingSave)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar requisición</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            ¿Estás seguro de crear esta requisición con {{ count($items) }} producto(s)?
                        </p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="executeSave"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Sí, continuar
                        </button>
                        <button @click="open = false" wire:click="cancelSave"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
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