<div class="max-w-6xl mx-auto">
    <x-ui.card title="Venta entre empresas" icon="point_of_sale" subtitle="Transferencia de material entre empresas distintas (compra/venta)" overflow="visible">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.label icon="logout" required>Empresa / sucursal vendedora</x-forms.label>
                    <select wire:model.live="sellerBranchId"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <option value="">Seleccioná...</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} · {{ $b->company?->razon_social ?? 'Sin empresa' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-forms.label icon="login" required>Empresa / sucursal compradora</x-forms.label>
                    <select wire:model.live="buyerBranchId"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <option value="">Seleccioná...</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} · {{ $b->company?->razon_social ?? 'Sin empresa' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($sellerBranchId && $buyerBranchId && \App\Models\Branch::find($sellerBranchId)?->company_id === \App\Models\Branch::find($buyerBranchId)?->company_id)
                <x-ui.alert variant="danger">
                    Ambas sucursales pertenecen a la misma empresa. Para mover material dentro de la misma empresa usá el módulo de Traspasos.
                </x-ui.alert>
            @endif

            <div class="border-t border-gray-200 pt-5">
                <x-forms.label icon="inventory_2">Producto a vender</x-forms.label>
                @if($selectedProductId && $selectedProduct)
                    <div class="flex items-start gap-3 p-3.5 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-blue-600 text-xl">check_circle</span></div>
                        <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-900 truncate">{{ $selectedProduct->name }}</p><p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $selectedProduct->sku }}</p></div>
                        <button type="button" wire:click="openProductModal" class="px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 rounded-lg">Cambiar</button>
                        <button type="button" wire:click="clearProduct" class="p-1.5 text-blue-600 hover:text-red-600 rounded-lg"><span class="material-symbols-outlined text-lg">close</span></button>
                    </div>
                @else
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Buscar producto..." class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm text-sm">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                            @if(count($productResults) > 0)
                            <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-48 overflow-auto">
                                @foreach($productResults as $p)
                                <li wire:click="selectProduct({{ $p->id }})" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{{ $p->name }} <span class="text-gray-500 font-mono">({{ $p->sku }})</span></li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        <button type="button" wire:click="openProductModal" class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 transition whitespace-nowrap"><span class="material-symbols-outlined text-lg">format_list_bulleted</span></button>
                    </div>
                @endif
            </div>

            @if($selectedProductId)
                @if(!$sellerBranchId)
                    <x-ui.alert variant="warning">Seleccioná la sucursal vendedora para ver el stock y el costo.</x-ui.alert>
                @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <p class="text-xs text-blue-600 uppercase">Disponible en vendedora</p>
                        <p class="text-xl font-bold text-blue-900 font-mono">{{ (int) $available }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                        <p class="text-xs text-indigo-600 uppercase">Costo de venta</p>
                        <p class="text-xl font-bold text-indigo-900 font-mono">${{ number_format($unitCost, 2) }}</p>
                    </div>
                </div>

                <x-ui.input type="number" icon="inventory_2" wire:model.live="quantity" min="0" max="{{ (int) $available }}" label="Cantidad a vender" placeholder="0" />

                <div class="flex justify-end">
                    <div class="w-full sm:w-80 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 bg-gray-100/60 border-b border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-500 text-base">receipt</span> Resumen
                            </h4>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-800 font-mono">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">IVA (13%)</span>
                                <span class="font-medium text-gray-800 font-mono">${{ number_format($ivaAmount, 2) }}</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t-2 border-gray-200">
                                <span class="font-bold text-gray-800">Total</span>
                                <span class="font-bold text-blue-700 text-lg font-mono">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <x-ui.button variant="secondary" href="{{ route('bodega.intercompany-sales.index') }}">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" icon="point_of_sale" wire:click="save">Registrar venta</x-ui.button>
                </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Modal productos --}}
    <div x-data="{ show: @entangle('showProductModal') }" x-show="show" x-cloak x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold">Seleccionar producto</h3>
                    <button type="button" wire:click="closeProductModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg"><span class="material-symbols-outlined text-xl">close</span></button>
                </div>
                <div class="p-4 border-b"><input type="text" wire:model.live.debounce.300ms="productListSearch" placeholder="Filtrar..." class="w-full pl-3 pr-3 py-2.5 rounded-lg border border-gray-300 text-sm"></div>
                <div class="p-2 max-h-80 overflow-y-auto">
                    @forelse($productList as $p)
                    <button type="button" wire:click="selectProductFromList({{ $p->id }})" class="w-full text-left px-4 py-2.5 hover:bg-blue-50 rounded-lg text-sm flex items-center gap-3">
                        <span class="material-symbols-outlined text-gray-400">inventory_2</span>
                        <span>{{ $p->name }}</span>
                        <span class="text-gray-500 font-mono text-xs">{{ $p->sku }}</span>
                    </button>
                    @empty
                    <div class="py-8 text-center text-gray-500">Sin resultados</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
