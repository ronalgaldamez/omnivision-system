<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">work</span>
                    {{ $workOrder->code ?? 'OT-'.$workOrder->id }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">Detalle de la orden asignada</p>
            </div>
            <a href="{{ route('mobile.work-orders.list') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver
            </a>
        </div>

        <div class="p-6 space-y-5">
            {{-- ========== DATOS DEL CLIENTE ========== --}}
            <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">person</span>
                Datos del Cliente
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">person</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Nombre</p>
                        <p class="text-gray-800 font-medium">{{ $workOrder->client->name ?? 'No especificado' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400">call</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Teléfono</p>
                        <p class="text-gray-700">
                            @if($workOrder->client && $workOrder->client->phones->isNotEmpty())
                                @foreach($workOrder->client->phones as $phone)
                                    {{ $phone->number }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @else
                                {{ $workOrder->client->phone ?? '—' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                    <span class="material-symbols-outlined text-gray-400">location_on</span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Dirección</p>
                        <p class="text-gray-700">{{ $workOrder->client->address ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- ========== INFORMACIÓN DEL TICKET ========== --}}
            @if($workOrder->ticket)
                <div class="border-t border-gray-200 pt-5 space-y-3">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">confirmation_number</span>
                        Ticket #{{ $workOrder->ticket->id }}
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                            <span class="material-symbols-outlined text-gray-400">person</span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Creado por</p>
                                <p class="text-gray-700">{{ $workOrder->ticket->createdBy->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                            <span class="material-symbols-outlined text-gray-400">source</span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Origen</p>
                                <p class="text-gray-700">{{ $this->getTicketOriginLabel() ?? '—' }}</p>
                            </div>
                        </div>
                        @if($workOrder->ticket->priority)
                        <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                            <span class="material-symbols-outlined text-gray-400">flag</span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Prioridad</p>
                                <p class="text-gray-700">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($workOrder->ticket->priority)
                                            @case('P1') bg-red-100 text-red-700 @break
                                            @case('P2') bg-orange-100 text-orange-700 @break
                                            @case('P3') bg-blue-100 text-blue-700 @break
                                            @case('P4') bg-gray-100 text-gray-600 @break
                                        @endswitch">
                                        {{ $workOrder->ticket->priority }} -
                                        @php
                                            $priorityLabels = ['P1' => 'Crítico', 'P2' => 'Alta', 'P3' => 'Media', 'P4' => 'Baja'];
                                        @endphp
                                        {{ $priorityLabels[$workOrder->ticket->priority] ?? $workOrder->ticket->priority }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        @endif
                        @if($workOrder->ticket->description)
                        <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                            <span class="material-symbols-outlined text-gray-400">description</span>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Problema Reportado</p>
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $workOrder->ticket->description }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== INFORMACIÓN DE LA OT ========== --}}
            <div class="border-t border-gray-200 pt-5 space-y-3">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">engineering</span>
                    Información de la OT
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">person</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Creada por</p>
                            <p class="text-gray-700">{{ $workOrder->createdBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">calendar_month</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Fecha programada</p>
                            <p class="text-gray-700">{{ $workOrder->scheduled_date?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400">flag</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Estado</p>
                            @if($workOrder->status == 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-50 text-yellow-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-sm">schedule</span> Pendiente
                                </span>
                            @elseif($workOrder->status == 'in_progress')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-sm">autorenew</span> En progreso
                                </span>
                            @elseif($workOrder->status == 'paused')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-sm">pause_circle</span> Pausada
                                </span>
                            @elseif($workOrder->status == 'completed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-sm">check_circle</span> Completada
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-red-50 text-red-700 rounded-full text-xs font-medium">
                                    <span class="material-symbols-outlined text-sm">cancel</span> Cancelada
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- Tiempo trabajado --}}
                    @if($workOrder->started_at || $workOrder->accumulated_seconds > 0)
                    <div class="flex items-start gap-3 p-3 bg-blue-50/50 rounded-lg border border-blue-100 sm:col-span-2">
                        <span class="material-symbols-outlined text-blue-500">schedule</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Tiempo trabajado</p>
                            @if($workOrder->started_at)
                                <p class="text-sm text-gray-800 font-medium">Inició: {{ $workOrder->started_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($workOrder->accumulated_seconds > 0)
                                @php
                                    $hours = floor($workOrder->accumulated_seconds / 3600);
                                    $minutes = floor(($workOrder->accumulated_seconds % 3600) / 60);
                                @endphp
                                <p class="text-xs text-gray-600">
                                    @if($workOrder->status === 'paused')
                                        Tiempo acumulado: {{ $hours }}h {{ $minutes }}m (pausada)
                                    @else
                                        Tiempo total anterior: {{ $hours }}h {{ $minutes }}m
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    @endif
                    {{-- Notas --}}
                    <div class="flex items-start gap-3 p-3 bg-gray-50/50 rounded-lg border border-gray-100 sm:col-span-2">
                        <span class="material-symbols-outlined text-gray-400">sticky_note_2</span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Notas</p>
                            <p class="text-gray-700">{{ $workOrder->notes ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Productos sugeridos --}}
            @if($workOrder->products->count())
                <div class="border-t border-gray-200 pt-5">
                    <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                        Productos sugeridos
                    </h2>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Producto</th>
                                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($workOrder->products as $item)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3 text-center font-mono text-gray-800">{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Enlace a Google Maps -->
            @if($workOrder->latitude && $workOrder->longitude)
                <a href="https://www.google.com/maps?q={{ $workOrder->latitude }},{{ $workOrder->longitude }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                    <span class="material-symbols-outlined text-base">map</span>
                    Ver en Google Maps
                </a>
            @endif

            <!-- Botones de acción -->
            @if(!in_array($workOrder->status, ['completed', 'cancelled']))
                <div class="border-t border-gray-200 pt-5 space-y-3">
                    {{-- Pendiente: mostrar iniciar (si tiene requisición y no hay otra en progreso) --}}
                    @if($workOrder->status === 'pending' && $hasOpenRequisition && !$hasAnotherInProgress)
                        <button wire:click="promptStartWorkOrder"
                            class="block w-full text-center px-5 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-yellow-600 transition">
                            Iniciar OT
                        </button>
                    @elseif($workOrder->status === 'pending' && !$hasOpenRequisition)
                        @if($technicianHasOpenRequisition)
                            <div class="flex items-start gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <span class="material-symbols-outlined text-yellow-600">warning</span>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">OT no vinculada</p>
                                    <p class="text-xs text-yellow-700">Selecciona OTs para vincular a tu requisición activa.</p>
                                </div>
                            </div>
                            <button wire:click="openWorkOrderSelectionModal"
                                class="block w-full text-center px-5 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-purple-700 transition">
                                Vincular OTs a requisición activa
                            </button>
                        @else
                            <div class="flex items-start gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <span class="material-symbols-outlined text-yellow-600">warning</span>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Requisición pendiente</p>
                                    <p class="text-xs text-yellow-700">Debes crear una requisición de material para esta OT.</p>
                                </div>
                            </div>
                            <a href="{{ route('technician.requisitions.create') }}"
                                class="block w-full text-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                                Crear requisición de material
                            </a>
                        @endif
                    @elseif($workOrder->status === 'pending' && $hasAnotherInProgress)
                        <div class="flex items-start gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <span class="material-symbols-outlined text-yellow-600">warning</span>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Ya tienes otra OT en progreso</p>
                                <p class="text-xs text-yellow-700">Finaliza o pausa esa OT antes de iniciar esta.</p>
                            </div>
                        </div>
                    @endif

                    {{-- En progreso: mostrar Pausar y Completar --}}
                    @if($workOrder->status === 'in_progress')
                        <button wire:click="promptPauseWorkOrder"
                            class="block w-full text-center px-5 py-2.5 bg-gray-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-600 transition">
                            Pausar OT
                        </button>
                        @if(auth()->user()->can('complete work_orders'))
                            <button wire:click="promptCompleteWorkOrder"
                                class="block w-full text-center px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                                Completar trabajo
                            </button>
                        @endif
                    @endif
                    {{-- Pausada: solo Reanudar --}}
                    @if($workOrder->status === 'paused')
                        <button wire:click="promptResumeWorkOrder"
                            class="block w-full text-center px-5 py-2.5 bg-blue-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-600 transition">
                            Reanudar OT
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de confirmación --}}
    @if($confirmingAction)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar acción</h3>
                        <p class="text-sm text-gray-600 mt-2">{{ $confirmingMessage }}</p>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="executeConfirmedAction"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Sí, continuar
                        </button>
                        <button @click="open = false" wire:click="cancelConfirmation"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de consumo de material --}}
    @if($showConsumptionModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Material Utilizado en {{ $workOrder->code ?? 'OT-'.$workOrder->id }}
                        </h3>
                        <button wire:click="closeConsumptionModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        @if(count($availableProducts) > 0)
                            <p class="text-sm text-gray-600">Indica cuánto material usaste de tu requisición para esta OT.</p>
                            <div class="space-y-3">
                                @foreach($availableProducts as $index => $product)
                                    <div class="flex items-center justify-between gap-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-800">{{ $product['product_name'] }}</p>
                                            <p class="text-xs text-gray-500">Disponible: {{ $product['available'] }}</p>
                                        </div>
                                        <input type="number" min="0" max="{{ $product['available'] }}" step="any"
                                            wire:model.defer="consumptionQuantities.{{ $index }}"
                                            class="w-20 text-center rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm py-1.5">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No tienes productos disponibles en tu requisición activa.</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="saveConsumption"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Guardar consumo
                        </button>
                        <button wire:click="closeConsumptionModal"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Omitir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de selección de OTs para vincular --}}
    @if($showWorkOrderSelectionModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">playlist_add_check</span>
                            Selecciona OTs para Vincular
                        </h3>
                        <button wire:click="closeWorkOrderSelectionModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-600">Marca las Órdenes de Trabajo que deseas agregar a tu requisición activa.</p>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @forelse($eligibleWorkOrders as $wo)
                                <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                                    <input type="checkbox" value="{{ $wo['id'] }}" wire:model="selectedWorkOrdersForLink"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                                    <span class="text-sm text-gray-700">{{ $wo['name'] }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No hay OTs pendientes sin vincular.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                        <button wire:click="linkSelectedWorkOrders"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                            Vincular seleccionadas
                        </button>
                        <button wire:click="closeWorkOrderSelectionModal"
                            class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Toast --}}
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

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>