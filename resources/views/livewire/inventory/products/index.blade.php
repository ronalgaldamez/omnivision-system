<div class="max-w-7xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">inventory_2</span>
                    Productos
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestión del catálogo de productos</p>
            </div>
            <a href="{{ route('products.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nuevo producto
            </a>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Filtro de búsqueda (ahora ancho completo, como en movements) -->
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" wire:model.live="search" placeholder="Buscar por nombre o SKU..."
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
            </div>

            <!-- Tabla de productos -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">qr_code</span>
                                    SKU
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                                    Nombre
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">straighten</span>
                                    Medida
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory</span>
                                    Stock
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">arrow_downward</span>
                                    Mínimo
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">arrow_upward</span>
                                    Máximo
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
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $product->sku }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $product->name }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $medida = '';
                                        if($product->unit_of_measure != 'unidad' && $product->measure_value) {
                                            $medida = $product->measure_value . ' ' . $product->unit_of_measure;
                                        } elseif($product->unit_of_measure != 'unidad') {
                                            $medida = $product->unit_of_measure;
                                        } else {
                                            $medida = 'unidad';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($product->unit_of_measure)
                                            @case('kg') bg-blue-50 text-blue-700 @break
                                            @case('g') bg-purple-50 text-purple-700 @break
                                            @case('m') bg-indigo-50 text-indigo-700 @break
                                            @case('cm') bg-teal-50 text-teal-700 @break
                                            @case('lb') bg-orange-50 text-orange-700 @break
                                            @case('l') bg-cyan-50 text-cyan-700 @break
                                            @case('ml') bg-sky-50 text-sky-700 @break
                                            @case('caja') bg-yellow-50 text-yellow-700 @break
                                            @case('paquete') bg-pink-50 text-pink-700 @break
                                            @default bg-gray-50 text-gray-700
                                        @endswitch
                                    ">
                                        <span class="material-symbols-outlined text-xs">straighten</span>
                                        {{ $medida }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium 
                                    {{ $product->current_stock <= $product->stock_min && $product->stock_min > 0 ? 'text-red-600' : 'text-gray-800' }}">
                                    @if($product->current_stock <= $product->stock_min && $product->stock_min > 0)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">warning</span>
                                            {{ $product->current_stock }}
                                        </span>
                                    @else
                                        {{ $product->current_stock }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $product->stock_min }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $product->stock_max ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <a href="{{ route('products.show', $product->id) }}"
                                            class="p-1 text-green-600 hover:text-green-800 rounded transition" title="Ver detalle">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="p-1 text-blue-600 hover:text-blue-800 rounded transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <button wire:click="confirmDelete({{ $product->id }})"
                                            class="p-1 text-red-500 hover:text-red-700 rounded transition" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                    <p class="text-gray-500">No hay productos registrados</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nuevo producto" para agregar uno</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($products->hasPages())
                <div class="mt-5">
                    {{ $products->links() }}
                </div>
            @endif

            <!-- Mensajes de sesión (exactamente igual que en movements) -->
            @if(session('message'))
                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">delete_forever</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        ¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="deleteProduct"
                        class="w-full sm:w-auto px-5 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-red-700 transition">
                        Sí, eliminar
                    </button>
                    <button @click="show = false" wire:click="closeDeleteModal"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
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
        <div x-show="toastType === 'info'"
            class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">info</span>
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>