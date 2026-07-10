<div class="max-w-7xl mx-auto">
    <x-ui.card title="Productos" subtitle="Gestión del catálogo de productos" icon="inventory_2">
        <x-slot:headerActions>
            <x-ui.button variant="primary" size="sm" icon="add_circle" href="{{ route('products.create') }}">Nuevo producto</x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-5">
            @if($activeBranch)
                <x-ui.alert variant="info">
                    Viendo inventario de: <strong>{{ $activeBranch->name }}</strong>
                </x-ui.alert>
            @endif

            <x-ui.input icon="search" wire:model.live="search" placeholder="Buscar por nombre o SKU..." />

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">qr_code</span> SKU
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span> Nombre
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">straighten</span> Medida
                                </div>
                            </th>
                            @if($activeBranch)
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">store</span> Stock {{ $activeBranch->name }}
                                </div>
                            </th>
                            @else
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">inventory</span> Stock
                                </div>
                            </th>
                            @endif
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">arrow_downward</span> Mínimo
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">arrow_upward</span> Máximo
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-gray-400 text-base">settings</span> Acciones
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
                                @if($activeBranch)
                                @php $branchAlloc = (int) ($product->branchInventories->first()?->allocated_quantity ?? 0); @endphp
                                <td class="px-4 py-3 text-right font-medium text-blue-700">
                                    {{ rtrim(rtrim(number_format($branchAlloc, 2), '0'), '.') }}
                                    <span class="text-blue-500 text-xs font-normal">{{ $product->unit_of_measure }}</span>
                                </td>
                                @else
                                <td class="px-4 py-3 text-right font-medium {{ $product->current_stock <= $product->stock_min && $product->stock_min > 0 ? 'text-red-600' : 'text-gray-800' }}">
                                    @if($product->current_stock <= $product->stock_min && $product->stock_min > 0)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">warning</span>
                                            {{ intval($product->current_stock) }}
                                            <span class="text-gray-400 text-xs font-normal">{{ $product->unit_of_measure }}</span>
                                        </span>
                                    @else
                                        {{ intval($product->current_stock) }}
                                        <span class="text-gray-400 text-xs font-normal">{{ $product->unit_of_measure }}</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3 text-right text-gray-700">{{ intval($product->stock_min) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $product->stock_max ? intval($product->stock_max) : '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <a href="{{ route('products.show', $product->id) }}" class="p-1 text-green-600 hover:text-green-800 rounded transition" title="Ver detalle">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="p-1 text-blue-600 hover:text-blue-800 rounded transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <span wire:click="confirmDelete({{ $product->id }})" class="p-1 text-red-500 hover:text-red-700 rounded transition cursor-pointer" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </span>
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

            @if($products->hasPages())
                <div class="mt-5">{{ $products->links() }}</div>
            @endif

            @if(session('message'))
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
            @endif
        </div>
    </x-ui.card>

    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative mx-auto p-5 w-full max-w-md"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-2xl">delete_forever</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                    <p class="text-sm text-gray-600 mt-2">¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row-reverse gap-3">
                    <x-ui.button variant="danger" icon="delete" wire:click="deleteProduct">Sí, eliminar</x-ui.button>
                    <span @click="show = false">
                        <x-ui.button variant="secondary" wire:click="closeDeleteModal">Cancelar</x-ui.button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
