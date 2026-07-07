<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-visible">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">settings_ethernet</span>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Registro de Dispositivos</h1>
                    <p class="text-sm text-gray-500">Cargá dispositivos manualmente o desde un archivo JSON</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Producto <span class="text-red-500">*</span></label>
                    @if($product_id && $selProduct = \App\Models\Product::find($product_id))
                    <div class="flex items-start gap-3 p-3.5 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-blue-600 text-xl">check_circle</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $selProduct->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $selProduct->sku }}</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" wire:click="openProductModal"
                                class="px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition">Cambiar</button>
                            <button type="button" wire:click="clearProduct"
                                class="p-1.5 text-blue-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar producto">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Buscar producto..."
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            @if(count($productResults) > 0)
                                <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100">
                                    @foreach($productResults as $p)
                                        <li wire:click="selectProduct({{ $p->id }})" class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm">{{ $p->name }} <span class="text-gray-500 font-mono">({{ $p->sku }})</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <button type="button" wire:click="openProductModal"
                            class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                            title="Ver todos los productos">
                            <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                            <span class="hidden sm:inline">Ver todos</span>
                        </button>
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Compra asociada <span class="text-gray-400 text-xs">(opcional)</span></label>
                    @if($purchase_id && $selPurchase = \App\Models\Purchase::with('supplier')->find($purchase_id))
                    <div class="flex items-start gap-3 p-3.5 bg-green-50 border border-green-200 rounded-lg">
                        <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $selPurchase->invoice_number }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $selPurchase->supplier?->name ?? '—' }} · {{ $selPurchase->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" wire:click="openPurchaseModal"
                                class="px-2.5 py-1.5 text-xs font-medium text-green-700 hover:text-green-800 hover:bg-green-100 rounded-lg transition">Cambiar</button>
                            <button type="button" wire:click="clearPurchase"
                                class="p-1.5 text-green-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar compra">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>
                    @if($purchaseDeviceWarning)
                        <p class="mt-1.5 text-xs text-yellow-700 flex items-center gap-1">
                            <span class="material-symbols-outlined text-yellow-600 text-sm">info</span>
                            {{ $purchaseDeviceWarning }}
                        </p>
                    @endif
                    @else
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" wire:model.live.debounce.300ms="purchaseSearch" placeholder="Buscar por factura o proveedor..."
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">receipt</span>
                            @if(count($purchaseResults) > 0)
                                <ul class="absolute z-30 mt-1 w-full bg-white rounded-lg border border-gray-200 shadow-xl max-h-60 overflow-auto divide-y divide-gray-100">
                                    @foreach($purchaseResults as $pr)
                                        <li wire:click="selectPurchase({{ $pr->id }})" class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm">{{ $pr->invoice_number }} - {{ $pr->supplier?->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <button type="button" wire:click="openPurchaseModal"
                            class="inline-flex items-center gap-1 px-3 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm whitespace-nowrap"
                            title="Ver últimas compras">
                            <span class="material-symbols-outlined text-lg">format_list_bulleted</span>
                            <span class="hidden sm:inline">Ver últimas</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            @if($product_id)
            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('importMode')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg bg-white hover:bg-gray-50 transition">
                        <span class="material-symbols-outlined text-lg">{{ $importMode ? 'edit_note' : 'upload_file' }}</span>
                        {{ $importMode ? 'Carga manual' : 'Importar JSON' }}
                    </button>
                    @if(!$importMode)
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Cantidad:</label>
                        <input type="number" wire:model.live="quantity" min="1" class="w-20 px-3 py-2 rounded-lg border border-gray-300 text-sm">
                    </div>
                    @endif
                </div>
                <span class="text-sm text-gray-500">{{ count($rows) }} dispositivo(s)</span>
            </div>

            @if($importMode)
            <div class="p-6 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 text-center">
                <span class="material-symbols-outlined text-gray-400 text-4xl mb-2">cloud_upload</span>
                <p class="text-gray-600 mb-3">Subí un archivo JSON con los dispositivos</p>
                <input type="file" wire:model="jsonFile" accept=".json,.txt" class="block mx-auto text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-2">JSON array con objetos: {"mac_address": "...", "pon_sn": "...", ...}</p>
            </div>
            @endif

            @if(count($rows) > 0 && !$importMode)
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">#</th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">MAC Address <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">PON SN</th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">IP</th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">Username</th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">Password</th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">SSID1</th>
                            <th class="px-3 py-2.5 text-left text-gray-600 font-medium">LAN Key</th>
                            <th class="px-3 py-2.5 text-center text-gray-600 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rows as $index => $row)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-3 py-2 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.mac_address" placeholder="XX:XX:XX:XX:XX:XX" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm font-mono"></td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.pon_sn" placeholder="PON SN" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm font-mono"></td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.default_ip" placeholder="192.168.1.1" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm font-mono"></td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.default_username" placeholder="admin" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"></td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.default_password" placeholder="password" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"></td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.default_ssid1" placeholder="SSID" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"></td>
                            <td class="px-3 py-2"><input type="text" wire:model="rows.{{ $index }}.default_lan_key" placeholder="WPA Key" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"></td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" wire:click="removeRow({{ $index }})" class="p-1 text-red-500 hover:bg-red-50 rounded transition">
                                    <span class="material-symbols-outlined text-lg">close</span>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="flex justify-between gap-3 pt-4 border-t border-gray-200">
                <div>
                    @if(count($rows) > 0 || $product_id)
                    <button type="button" wire:click="resetForm" wire:loading.attr="disabled" wire:target="confirmSave,requestSave"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-red-200 rounded-lg text-sm font-medium text-red-600 bg-white hover:bg-red-50 transition">
                        <span class="material-symbols-outlined text-base">delete_sweep</span>
                        Limpiar todo
                    </button>
                    @endif
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('devices.index') }}" class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    @if(count($rows) > 0)
                    <button type="button" wire:click="requestSave" wire:loading.attr="disabled" wire:target="confirmSave,requestSave"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="confirmSave,requestSave" class="material-symbols-outlined text-base">save</span>
                        <span wire:loading wire:target="confirmSave,requestSave" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                        <span wire:loading.remove wire:target="confirmSave,requestSave">Guardar {{ count($rows) }} dispositivo(s)</span>
                        <span wire:loading wire:target="confirmSave,requestSave">Guardando...</span>
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal de productos --}}
    <div x-data="{ show: @entangle('showProductModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar producto</h3>
                            <p class="text-xs text-gray-500">Elegí un producto de la lista</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeProductModal"
                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="productListSearch"
                            placeholder="Filtrar por nombre o SKU..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($productList as $p)
                        <button type="button" wire:click="selectProductFromList({{ $p->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-blue-600">inventory_2</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ $p->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $p->sku }}</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-300 group-hover:text-blue-500 text-lg flex-shrink-0">chevron_right</span>
                        </button>
                    @empty
                        <div class="py-12 text-center">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">search_off</span>
                            <p class="text-gray-500 text-sm">No se encontraron productos</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeProductModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de compras --}}
    <div x-data="{ show: @entangle('showPurchaseModal') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-2xl">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600">receipt_long</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Seleccionar compra</h3>
                            <p class="text-xs text-gray-500">Últimas compras registradas</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closePurchaseModal"
                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.300ms="purchaseListSearch"
                            placeholder="Filtrar por factura o proveedor..."
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    </div>
                </div>
                <div class="p-2 max-h-96 overflow-y-auto">
                    @forelse($purchaseList as $pr)
                        <button type="button" wire:click="selectPurchase({{ $pr->id }})"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 rounded-xl transition flex items-center justify-between group border-b border-gray-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-green-100 transition">
                                    <span class="material-symbols-outlined text-gray-500 text-lg group-hover:text-green-600">receipt</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 group-hover:text-green-700 truncate">{{ $pr->invoice_number }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $pr->supplier?->name ?? '—' }} · {{ $pr->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-gray-300 group-hover:text-green-500 text-lg flex-shrink-0">chevron_right</span>
                        </button>
                    @empty
                        <div class="py-12 text-center">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">search_off</span>
                            <p class="text-gray-500 text-sm">No se encontraron compras</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closePurchaseModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación --}}
    <div x-data="{ show: @entangle('showConfirmSave') }" x-show="show" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div x-show="show" x-transition:enter="ease-out duration-200 delay-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-2xl">help</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar guardado</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $confirmMessage }}</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="confirmSave" wire:loading.attr="disabled" wire:target="confirmSave"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                        <span wire:loading.remove wire:target="confirmSave">Sí, guardar</span>
                        <span wire:loading wire:target="confirmSave" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                        <span wire:loading wire:target="confirmSave">Guardando...</span>
                    </button>
                    <button wire:click="cancelSave" wire:loading.attr="disabled" wire:target="confirmSave"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
