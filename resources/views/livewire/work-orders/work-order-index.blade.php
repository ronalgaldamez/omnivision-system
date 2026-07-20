<div class="max-w-full mx-auto" wire:poll.15s="$refresh">
    <x-ui.card icon="engineering" title="Órdenes de Trabajo" subtitle="Gestioná y asigná técnicos a las OT del día">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('work-orders.create') }}">Nueva Orden</x-ui.button>
        </x-slot:headerActions>

        <div class="p-6 space-y-5">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-xs">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" wire:model.live="search" placeholder="Buscar OT, cliente o técnico..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                </div>
                <div class="relative">
                    <select wire:model.live="statusFilter"
                        class="pl-3 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En progreso</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                <div class="flex gap-1 bg-gray-100 rounded-lg p-0.5 ml-auto">
                    <button wire:click="$set('viewMode', 'table')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $viewMode === 'table' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-base align-text-bottom">table_rows</span> Tabla
                    </button>
                    <button wire:click="$set('viewMode', 'planner')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $viewMode === 'planner' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="material-symbols-outlined text-base align-text-bottom">calendar_view_week</span> Planificador
                    </button>
                </div>
            </div>

            @if($viewMode === 'table')
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Código OT</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Ticket</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Cliente</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Zona</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Técnico</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Auxiliar</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Estado</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Fecha</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/80 transition {{ in_array($order->id, $selectedOrders) ? 'bg-blue-50/60' : '' }}">
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:model.live="selectedOrders" value="{{ $order->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-blue-700 font-medium">{{ $order->code ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $order->ticket?->ticket_code ?? '—' }}</td>
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
                                @php $b = match($order->status) { 'pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'paused' => 'secondary', default => 'danger' }; $sl = ucfirst(str_replace('_', ' ', $order->status)); @endphp
                                <x-ui.badge variant="{{ $b }}">{{ $sl }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-700">{{ $order->scheduled_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('work-orders.show', $order->id) }}" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    <a href="{{ route('work-orders.edit', $order->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="promptDelete({{ $order->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                    <a href="{{ $order->ticket_id ? route('sla.ticket-timeline', $order->ticket_id) : route('sla.work-order-timeline', $order->id) }}"
                                        class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Ver Timeline SLA">
                                        <span class="material-symbols-outlined text-lg">account_tree</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2 block">inbox</span>
                                <p class="text-gray-500">No hay órdenes de trabajo registradas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($orders, 'hasPages') && $orders->hasPages())
            <div class="mt-5">{{ $orders->links() }}</div>
            @endif

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
                @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
                .animate-slide-up { animation: slideUp 0.2s ease-out; }
            </style>
            @endif
            @endif

            @if($viewMode === 'planner')
            <div id="planner-loading" class="text-center py-16 text-gray-400">
                <span class="material-symbols-outlined text-4xl mb-3 block animate-spin">refresh</span>
                <p class="text-sm">Inicializando planificador...</p>
            </div>
            <div id="planner-columns" class="flex gap-4 overflow-x-auto pb-4" style="min-height: 65vh; display: none;" x-init="$nextTick(() => initPlannerSortable())">
                <div class="flex-shrink-0 w-72 flex flex-col">
                    <div class="flex items-center gap-2 mb-3 px-1">
                        <span class="material-symbols-outlined text-gray-400 text-lg">inbox</span>
                        <span class="font-semibold text-sm text-gray-700">Sin asignar</span>
                        <span class="text-xs font-medium bg-gray-100 text-gray-600 rounded-full px-2 py-0.5 ml-auto">{{ $unassigned->count() }}</span>
                    </div>
                    <div class="flex-1 space-y-2 rounded-xl bg-gray-50/60 border-2 border-dashed border-gray-200 p-2" data-tech-id="">
                        @forelse($unassigned as $ot)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 transition-all hover:shadow-md cursor-grab active:cursor-grabbing" data-ot-id="{{ $ot->id }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-mono font-bold text-xs text-blue-600">{{ $ot->code }}</span>
                                <span class="text-[10px] text-gray-400">{{ $ot->scheduled_date?->format('d/m') }}</span>
                            </div>
                            <div class="text-sm font-medium text-gray-800 truncate">{{ $ot->client->name }}</div>
                            @if($ot->zone)
                            <div class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">location_on</span>
                                {{ $ot->zone->name }}
                            </div>
                            @endif
                            <div class="mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-300 text-sm">drag_indicator</span>
                                @php $sl = ucfirst(str_replace('_', ' ', $ot->status)); @endphp
                                <x-ui.badge variant="warning">{{ $sl }}</x-ui.badge>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-10 text-gray-400 text-xs">
                            <span class="material-symbols-outlined text-gray-300 text-3xl mb-1 block">inbox</span>
                            <p>Sin OT</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                @foreach($technicians as $tech)
                <div class="flex-shrink-0 w-72 flex flex-col">
                    <div class="flex items-center gap-2 mb-3 px-1">
                        <span class="material-symbols-outlined text-gray-400 text-lg">engineering</span>
                        <span class="font-semibold text-sm text-gray-700 truncate">{{ $tech->name }}</span>
                        <span class="text-xs font-medium bg-blue-100 text-blue-700 rounded-full px-2 py-0.5 ml-auto">{{ $byTechnician[$tech->id]->count() }}</span>
                    </div>
                    <div class="flex-1 space-y-2 rounded-xl bg-gray-50/60 border-2 border-dashed border-gray-200 p-2" data-tech-id="{{ $tech->id }}">
                        @forelse($byTechnician[$tech->id] as $ot)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 transition-all hover:shadow-md cursor-grab active:cursor-grabbing" data-ot-id="{{ $ot->id }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-mono font-bold text-xs text-blue-600">{{ $ot->code }}</span>
                                <span class="text-[10px] text-gray-400">{{ $ot->scheduled_date?->format('d/m') }}</span>
                            </div>
                            <div class="text-sm font-medium text-gray-800 truncate">{{ $ot->client->name }}</div>
                            @if($ot->zone)
                            <div class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">location_on</span>
                                {{ $ot->zone->name }}
                            </div>
                            @endif
                            @if($ot->auxiliarTechnician)
                            <div class="text-xs text-gray-500 mt-1">+ {{ $ot->auxiliarTechnician->name }}</div>
                            @endif
                            @php
                                $b = match($ot->status) { 'pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'paused' => 'secondary', default => 'danger' };
                                $sl = ucfirst(str_replace('_', ' ', $ot->status));
                            @endphp
                            <div class="mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-gray-300 text-sm">drag_indicator</span>
                                <x-ui.badge variant="{{ $b }}">{{ $sl }}</x-ui.badge>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-10 text-gray-400 text-xs">
                            <span class="material-symbols-outlined text-gray-300 text-3xl mb-1 block">inbox</span>
                            <p>Sin OT</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>

            <div id="planner-load-bar" @if($technicians->count() > 0) style="display: none" @endif>
            @if($technicians->count() > 0)
            <div class="pt-2 border-t border-gray-100">
                <div class="text-xs font-medium text-gray-500 mb-2">Carga del día</div>
                <div class="space-y-1.5">
                    @foreach($technicians as $tech)
                    @php
                        $count = $byTechnician[$tech->id]->count();
                        $pct = $maxLoad > 0 ? round($count / $maxLoad * 100) : 0;
                        $barColor = $pct > 80 ? 'bg-red-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-blue-400');
                    @endphp
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-24 text-gray-600 text-right truncate">{{ $tech->name }}</span>
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300 {{ $barColor }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-8 text-gray-500 text-center">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            </div>
            @endif
        </div>
    </x-ui.card>

    <script src="{{ asset('js/sortable.min.js') }}"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => { initPlannerSortable(); });
            });
            initPlannerSortable();
        });
        window.addEventListener('planner-activated', () => initPlannerSortable());

        // Observer: atrapa el spinner cuando Livewire lo re-inserte en el DOM (polls, morphs)
        const plannerObserver = new MutationObserver(() => {
            const spinner = document.getElementById('planner-loading');
            if (spinner) {
                initPlannerSortable();
            }
        });
        plannerObserver.observe(document.body, { childList: true, subtree: true });

        function initPlannerSortable() {
            // Always hide spinner and show columns immediately
            document.getElementById('planner-loading')?.remove();
            document.getElementById('planner-columns')?.style?.removeProperty('display');
            const loadBar = document.getElementById('planner-load-bar');
            if (loadBar) loadBar.style.removeProperty('display');

            const columns = document.querySelectorAll('[data-tech-id]');
            if (columns.length === 0) return;
            if (typeof Sortable === 'undefined') { setTimeout(initPlannerSortable, 500); return; }
            columns.forEach(el => {
                if (el.sortableInstance) el.sortableInstance.destroy();
                el.sortableInstance = new Sortable(el, {
                    group: 'planner-ots',
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                    onEnd: function(evt) {
                        const otId = evt.item.dataset.otId;
                        const techId = evt.to.dataset.techId;
                        if (otId) {
                            Livewire.dispatch('assignFromDrag', {
                                otId: parseInt(otId),
                                technicianId: techId !== '' ? parseInt(techId) : null
                            });
                        }
                    }
                });
            });
        }
    </script>

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

                    @if($alreadyAssigned > 0)
                    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <span class="material-symbols-outlined text-amber-500 text-base mt-0.5">warning</span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800">{{ $alreadyAssigned }} de {{ count($selectedOrders) }} OT ya tienen técnico</p>
                            <label class="inline-flex items-center gap-2 mt-2 text-xs text-amber-700 cursor-pointer">
                                <input type="checkbox" wire:model="skipAssigned" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                Saltar OT que ya tienen técnico (solo {{ count($selectedOrders) - $alreadyAssigned }} sin técnico)
                            </label>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Técnico</label>
                            <select wire:model="assignTechnicianId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach($encargados as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Auxiliar</label>
                            <select wire:model="assignAuxiliarId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha programada</label>
                        <input type="date" wire:model="scheduledDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas <span class="text-gray-400">(opcional)</span></label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Notas para todas las OT seleccionadas..."></textarea>
                    </div>
                </div>
                <x-slot:footer>
                    <x-ui.button variant="secondary" wire:click="$set('showAssignModal', false)">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" wire:click="assignSelected">Asignar a {{ count($selectedOrders) }} OT</x-ui.button>
                </x-slot:footer>
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
                    <p class="text-sm text-gray-600 mt-2">¿Estás seguro de que deseas eliminar la orden #{{ $confirmingOrderId }}?</p>
                </div>
                <x-slot:footer>
                    <x-ui.button variant="danger" wire:click="executeConfirmedAction">Sí, eliminar</x-ui.button>
                    <x-ui.button variant="secondary" @click="open = false" wire:click="cancelConfirmation">Cancelar</x-ui.button>
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
        <div x-show="toastType === 'success'" class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'" class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .sortable-ghost { opacity: 0.4; }
        .sortable-drag { border-color: rgb(96 165 250) !important; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
    </style>
</div>
