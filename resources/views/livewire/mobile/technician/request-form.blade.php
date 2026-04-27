<div class="max-w-3xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">
                            {{ $requestId ? 'edit' : 'add_task' }}
                        </span>
                        {{ $requestId ? 'Editar' : 'Nueva' }} Solicitud
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $requestId ? 'Modifica los productos solicitados' : 'Solicita materiales para tu trabajo' }}
                    </p>
                </div>
                <a href="{{ route('mobile.technician.requests') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Volver
                </a>
            </div>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <!-- Orden de Trabajo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">work</span>
                        Orden de Trabajo
                    </label>
                    <div class="relative">
                        <select wire:model="workOrderId"
                            class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                            <option value="">Seleccione una OT (opcional)</option>
                            @foreach($workOrders as $wo)
                                <option value="{{ $wo->id }}">#{{ $wo->id }} - {{ $wo->client_name }}
                                    ({{ $wo->scheduled_date?->format('d/m/Y') }})</option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">receipt</span>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                    @error('workOrderId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Notas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">sticky_note_2</span>
                        Notas (opcional)
                    </label>
                    <div class="relative">
                        <textarea wire:model="notes" rows="2"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Indicaciones adicionales..."></textarea>
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                </div>

                <!-- Productos -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                            Productos
                        </h2>
                        <button type="button" wire:click="addItem"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                            <span class="material-symbols-outlined text-base">add_circle</span>
                            Agregar producto
                        </button>
                    </div>

                    @foreach($items as $index => $item)
                        <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 mb-4">
                            <div class="flex justify-end mb-3">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"
                                    title="Eliminar producto">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                                        Producto
                                    </label>
                                    <select wire:model="items.{{ $index }}.product_id"
                                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                                        <option value="">Seleccione</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("items.$index.product_id") <span
                                    class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">numbers</span>
                                        Cantidad
                                    </label>
                                    <div class="relative">
                                        <input type="number" wire:model="items.{{ $index }}.quantity_requested"
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                                        <span
                                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                                    </div>
                                    @error("items.$index.quantity_requested") <span
                                    class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @error('items') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('mobile.technician.requests') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ $requestId ? 'Actualizar' : 'Crear Solicitud' }}
                    </button>
                </div>
            </form>

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div
                    class="mt-4 flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>