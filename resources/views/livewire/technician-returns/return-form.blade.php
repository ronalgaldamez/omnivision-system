<div class="max-w-4xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">assignment_return</span>
                        Registrar Devolución de Materiales
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Devuelve sobrantes o registra materiales dañados</p>
                </div>
                <a href="{{ route('technician-returns.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver al listado
                </a>
            </div>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit.prevent="confirmSave" class="space-y-6">
                <!-- Tipo de devolución -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">swap_vert</span>
                        Tipo *
                    </label>
                    <div class="relative">
                        <select wire:model="type"
                            class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                            <option value="surplus">Sobrante (devuelve al inventario - recalcula costo promedio)</option>
                            <option value="damage">Dañado (se descarta del inventario - resta stock)</option>
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">alt_route</span>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                </div>

                <!-- Técnico (solo admin/warehouse) -->
                @if(!auth()->user()->hasRole('technician'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">engineering</span>
                            Técnico *
                        </label>
                        <div class="relative">
                            <select wire:model.live="technician_id"
                                class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Seleccione técnico</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">person</span>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                        @error('technician_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Orden de Trabajo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">work</span>
                        Orden de Trabajo *
                    </label>
                    <div class="relative">
                        <select wire:model.live="work_order_id"
                            class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                            <option value="">Seleccione OT</option>
                            @foreach($workOrders as $wo)
                                <option value="{{ $wo->id }}">#{{ $wo->id }} - {{ $wo->client->name ?? 'Cliente' }}
                                    ({{ $wo->scheduled_date?->format('d/m/Y') }})</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">receipt</span>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('work_order_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Productos disponibles (si hay OT y productos) -->
                @if($work_order_id && count($availableProducts) > 0)
                    <div class="border-t border-gray-200 pt-5 space-y-5">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Producto a devolver
                        </h2>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                Producto *
                            </label>
                            <div class="relative">
                                <input type="text" wire:model.live="productSearch"
                                    placeholder="Buscar producto disponible para devolver..."
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                                @if($productSearch && count($availableProducts) > 0)
                                    <ul class="absolute z-10 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-lg max-h-56 overflow-auto divide-y divide-gray-100">
                                        @foreach($availableProducts as $prod)
                                            @if(stripos($prod['product_name'], $productSearch) !== false || stripos($prod['product_sku'], $productSearch) !== false)
                                                <li wire:click="selectProduct({{ $prod['product_id'] }}, {{ $prod['request_id'] }}, {{ $prod['available'] }})"
                                                    class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition text-sm flex items-center justify-between">
                                                    <span class="font-medium text-gray-800">{{ $prod['product_name'] }}</span>
                                                    <span class="text-xs text-gray-500">Disponible: {{ $prod['available'] }}</span>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <select wire:model="product_id" class="mt-2 w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                <option value="">Seleccione producto a devolver</option>
                                @foreach($availableProducts as $prod)
                                    <option value="{{ $prod['product_id'] }}">{{ $prod['product_name'] }} (Disponible: {{ $prod['available'] }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                                Cantidad a devolver *
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="quantity" step="1" min="1"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                            </div>
                            @error('quantity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @elseif($work_order_id && count($availableProducts) == 0)
                    <div class="flex items-center gap-2 text-sm text-yellow-700 bg-yellow-50 px-4 py-3 rounded-lg border border-yellow-200">
                        <span class="material-symbols-outlined text-yellow-600">warning</span>
                        No hay productos disponibles para devolver de esta OT.
                    </div>
                @endif

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                        Notas / Motivo
                    </label>
                    <div class="relative">
                        <textarea wire:model="notes" rows="2"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Describe el motivo de la devolución..."></textarea>
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('technician-returns.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Registrar Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación (Alpine.js) -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-yellow-100 mb-4">
                        <span class="material-symbols-outlined text-yellow-600 text-2xl">warning</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar devolución</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $confirmMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="save"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
                        Sí, registrar
                    </button>
                    <button @click="show = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>