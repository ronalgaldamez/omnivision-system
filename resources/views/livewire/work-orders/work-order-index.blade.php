<div class="max-w-7xl mx-auto" wire:poll.15s="$refresh">
    <x-ui.card icon="engineering" title="Órdenes de Trabajo" subtitle="Gestioná y asigná técnicos a las OT del día">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('work-orders.create') }}">Nueva
                Orden</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live="search"
                        placeholder="Buscar por cliente, técnico o código de OT..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">filter_alt</span>
                    <select wire:model.live="statusFilter"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En progreso</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">tag</span>
                                    Código OT
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-base">confirmation_number</span>
                                    Ticket
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                                    Cliente
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">location_on</span>
                                    Zona
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                                    Técnico
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Auxiliar</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">flag</span>
                                    Estado
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-base">calendar_month</span>
                                    Fecha
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                    Acciones
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr
                                class="hover:bg-gray-50/80 transition {{ in_array($order->id, $selectedOrders) ? 'bg-blue-50/60' : '' }}">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" wire:model.live="selectedOrders" value="{{ $order->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-blue-700 font-medium">
                                    {{ $order->code ?? '—' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                    {{ $order->ticket?->ticket_code ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ $order->client->name ?? 'No especificado' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $order->zone->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($order->technician)
                                        <span class="text-xs font-medium text-gray-700">{{ $order->technician->name }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No asignado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($order->auxiliarTechnician)
                                        <span class="text-xs text-gray-600">{{ $order->auxiliarTechnician->name }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($order->status == 'pending')
                                        <x-ui.badge variant="warning">Pendiente</x-ui.badge>
                                    @elseif($order->status == 'in_progress')
                                        <x-ui.badge variant="info">En progreso</x-ui.badge>
                                    @elseif($order->status == 'completed')
                                        <x-ui.badge variant="success">Completada</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Cancelada</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700 text-xs">
                                    {{ $order->scheduled_date ? $order->scheduled_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('work-orders.show', $order->id) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition"
                                            title="Ver">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="{{ route('work-orders.edit', $order->id) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <button wire:click="promptDelete({{ $order->id }})"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                            title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                        @if($order->ticket_id)
                                            <a href="{{ route('sla.ticket-timeline', $order->ticket_id) }}"
                                                class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                                title="Ver Timeline SLA">
                                                <span class="material-symbols-outlined text-lg">account_tree</span>
                                            </a>
                                        @else
                                            <a href="{{ route('sla.work-order-timeline', $order->id) }}"
                                                class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                                title="Ver Timeline SLA">
                                                <span class="material-symbols-outlined text-lg">account_tree</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay órdenes de trabajo registradas</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nueva Orden" para crear una</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($orders, 'hasPages') && $orders->hasPages())
                <div class="mt-5">{{ $orders->links() }}</div>
            @endif
        </div>
    </x-ui.card>

    @if(count($selectedOrders) > 0)
        <div class="fixed bottom-6 right-6 z-50 flex items-center gap-3 animate-slide-up">
            <span class="text-sm text-gray-500 bg-white px-3 py-1.5 rounded-lg shadow-md border border-gray-200">
                {{ count($selectedOrders) }} seleccionada(s)
            </span>
            <button wire:click="$set('showAssignModal', true)"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-lg hover:bg-blue-700 transition">
                Asignar
            </button>
            <button wire:click="$set('selectedOrders', [])"
                class="text-gray-400 hover:text-gray-600 bg-white rounded-full p-1.5 shadow-md border border-gray-200 transition">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        <style>
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-slide-up {
                animation: slideUp 0.2s ease-out;
            }
        </style>
    @endif

    @if($showAssignModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <x-ui.card>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Asignación masiva</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ count($selectedOrders) }} OT(s) seleccionadas</p>
                            </div>
                            <button wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
                            <button wire:click="setQuickMode"
                                class="flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition {{ $assignMode === 'quick' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                                Rápida
                            </button>
                            <button wire:click="setStepMode"
                                class="flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition {{ $assignMode === 'step' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                                Paso a paso
                            </button>
                        </div>

                        @if($assignMode === 'quick')
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Técnico</label>
                                        <select wire:model="assignTechnicianId"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                            <option value="">Seleccionar...</option>
                                            @foreach($encargados as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Auxiliar</label>
                                        <select wire:model="assignAuxiliarId"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                            <option value="">Seleccionar...</option>
                                            @foreach($tecnicos as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha programada</label>
                                    <input type="date" wire:model="scheduledDate"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Notas <span
                                            class="text-gray-400">(opcional)</span></label>
                                    <textarea wire:model="notes" rows="2"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                        placeholder="Notas para todas las OT seleccionadas..."></textarea>
                                </div>
                            </div>
                        @else
                            @php $stepOtId = $selectedOrders[$currentStepIndex] ?? null; @endphp
                            @if($stepOtId)
                                @php $stepOt = \App\Models\WorkOrder::with(['client', 'technician'])->find($stepOtId); @endphp
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                                        <button wire:click="goToStep({{ $currentStepIndex - 1 }})"
                                            class="text-gray-500 hover:text-gray-700 {{ $currentStepIndex === 0 ? 'opacity-30 pointer-events-none' : '' }}">
                                            <span class="material-symbols-outlined text-lg">chevron_left</span>
                                        </button>
                                        <span class="font-medium text-gray-700">{{ $currentStepIndex + 1 }} de
                                            {{ count($selectedOrders) }}</span>
                                        <button wire:click="goToStep({{ $currentStepIndex + 1 }})"
                                            class="text-gray-500 hover:text-gray-700 {{ $currentStepIndex >= count($selectedOrders) - 1 ? 'opacity-30 pointer-events-none' : '' }}">
                                            <span class="material-symbols-outlined text-lg">chevron_right</span>
                                        </button>
                                    </div>

                                    <div class="bg-blue-50 rounded-lg px-3 py-2 space-y-0.5">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="font-mono font-bold text-xs text-blue-700">{{ $stepOt->code ?? '—' }}</span>
                                            <span
                                                class="text-xs text-blue-500">{{ $stepOt->scheduled_date ? $stepOt->scheduled_date->format('d/m/Y') : '' }}</span>
                                        </div>
                                        <div class="text-sm text-gray-800 font-medium">{{ $stepOt->client->name ?? '—' }}</div>
                                        @if($stepOt->technician)
                                            <div class="text-xs text-gray-500">Técnico actual: {{ $stepOt->technician->name }}</div>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Técnico</label>
                                            <select wire:model="assignTechnicianId"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                                <option value="">Seleccionar...</option>
                                                @foreach($encargados as $t)
                                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Auxiliar</label>
                                            <select wire:model="assignAuxiliarId"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                                <option value="">Seleccionar...</option>
                                                @foreach($tecnicos as $t)
                                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha programada</label>
                                        <input type="date" wire:model="scheduledDate"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas <span
                                                class="text-gray-400">(opcional)</span></label>
                                        <textarea wire:model="notes" rows="1"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                            placeholder="Notas..."></textarea>
                                    </div>

                                    <div class="flex gap-2 pt-1">
                                        <button wire:click="applyStep"
                                            class="flex-1 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                            {{ $currentStepIndex < count($selectedOrders) - 1 ? 'Aplicar y siguiente' : 'Aplicar' }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    @if($assignMode === 'quick')
                        <x-slot:footer>
                            <x-ui.button variant="secondary" wire:click="$set('showAssignModal', false)">Cancelar</x-ui.button>
                            <x-ui.button variant="primary" wire:click="assignSelected">Asignar a {{ count($selectedOrders) }}
                                OT</x-ui.button>
                        </x-slot:footer>
                    @endif
                </x-ui.card>
            </div>
        </div>
    @endif

    @if($confirmingAction)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-md">
                <x-ui.card>
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                            <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                        <p class="text-sm text-gray-600 mt-2">¿Estás seguro de que deseas eliminar la orden
                            #{{ $confirmingOrderId }}?</p>
                    </div>
                    <x-slot:footer>
                        <x-ui.button variant="danger" wire:click="executeConfirmedAction">Sí, eliminar</x-ui.button>
                        <x-ui.button variant="secondary" @click="open = false"
                            wire:click="cancelConfirmation">Cancelar</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>
            </div>
        </div>
    @endif

    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 3500)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
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
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>