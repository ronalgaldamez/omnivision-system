<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-visible">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">add_circle</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Nuevo envío a sucursal</h1>
                    <p class="text-sm text-gray-500">Creá un envío para repartir material</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Producto</label>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sucursal destino <span class="text-red-500">*</span></label>
                    <select wire:model="targetBranchId" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm">
                        <option value="">Seleccioná...</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($selectedProductId)
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200"><p class="text-xs text-blue-600 uppercase">Stock global</p><p class="text-xl font-bold text-blue-900 font-mono">{{ $requiresDevice ? count($devices) : (int) $globalStock }}</p></div>
                <div class="bg-amber-50 rounded-lg p-4 border border-amber-200"><p class="text-xs text-amber-600 uppercase">Ya repartido</p><p class="text-xl font-bold text-amber-900 font-mono">{{ $requiresDevice ? 0 : (int) $alreadyAllocated }}</p></div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200"><p class="text-xs text-green-600 uppercase">Disponible</p><p class="text-xl font-bold text-green-900 font-mono">{{ $requiresDevice ? count($devices) : (int) $available }}</p></div>
            </div>

            @if($requiresDevice && count($devices) > 0)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Seleccionar dispositivos</h3>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" wire:click="toggleSelectAll" {{ $selectAll ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600"> Seleccionar todos
                    </label>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg bg-white divide-y">
                    @foreach($devices as $d)
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm {{ in_array($d['id'], $selectedDevices) ? 'bg-blue-50/50' : '' }}">
                        <input type="checkbox" wire:click="toggleDevice({{ $d['id'] }})" {{ in_array($d['id'], $selectedDevices) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="font-mono text-xs">{{ $d['mac_address'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @elseif(!$requiresDevice)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Cantidad a enviar</label>
                <input type="number" wire:model="quantity" min="0" max="{{ (int) $available }}" class="w-48 px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-mono" placeholder="0">
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notas <span class="text-gray-400 text-xs">(opcional)</span></label>
                <textarea wire:model="notes" rows="2" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-sm" placeholder="Instrucciones o comentarios..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('bodega.shipments.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cancelar</a>
                <button type="button" wire:click="save" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700 transition">
                    <span class="material-symbols-outlined text-base">local_shipping</span>
                    Crear envío
                </button>
            </div>
            @endif
        </div>
    </div>

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
