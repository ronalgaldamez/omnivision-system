<div>
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Encabezado --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">local_shipping</span>
                    Envío {{ $shipment->code }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">{{ $shipment->branch?->name }} ·
                    {{ $shipment->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('bodega.shipments.receive', ['code' => $shipment->code]) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                    <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                    Recibir
                </a>
                <a href="{{ route('bodega.shipments.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                    Volver
                </a>
            </div>
        </div>

        {{-- Info rápida --}}
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
            <div>
                <span class="text-xs text-gray-500">Estado</span>
                @php $sc = $statusColors[$shipment->status] ?? ['bg-gray-100 text-gray-700', 'bg-gray-500']; @endphp
                <p><span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc[0] }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                </p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Sucursal</span>
                <p class="font-medium">{{ $shipment->branch?->name ?? '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Creado por</span>
                <p class="font-medium">{{ $shipment->creator?->name ?? '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Productos</span>
                <p class="font-medium">{{ $shipment->items->sum('quantity') }} unidades</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Recibido por</span>
                <p class="font-medium">{{ $shipment->confirmer?->name ?? '—' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Timeline --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200/80 p-6">
                <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-gray-400 text-base">timeline</span>
                    Estado del envío
                </h2>
                <div class="relative">
                    @foreach($steps as $index => $step)
                        <div class="flex items-start gap-4 pb-8 relative {{ $loop->last ? 'pb-0' : '' }}">
                            @if(!$loop->last)
                                <div
                                    class="absolute left-[15px] top-8 bottom-0 w-0.5 {{ $step['isCompleted'] ? 'bg-blue-400' : 'bg-gray-200' }}">
                                </div>
                            @endif
                            <div class="relative z-10 flex-shrink-0">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm
                                            {{ $step['isCompleted'] ? 'bg-blue-500 text-white' : ($step['isActive'] ? 'bg-blue-500 text-white animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                                    <span class="material-symbols-outlined text-sm">{{ $step['icon'] }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h3
                                            class="text-sm font-medium {{ $step['isCompleted'] ? 'text-gray-800' : ($step['isActive'] ? 'text-blue-700' : 'text-gray-400') }}">
                                            {{ $step['name'] }}
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $step['description'] }}</p>
                                        @if($step['user'])
                                            <p class="text-xs text-gray-400 mt-0.5">por {{ $step['user'] }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        @if($step['timestamp'])
                                            <p class="text-xs text-gray-500 font-mono">
                                                {{ \Carbon\Carbon::parse($step['timestamp'])->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- QR + Acciones --}}
            <div class="space-y-4" x-data="{ showQr: false, showBarcode: false, copied: false }">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5 text-center">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Código de envío</h2>

                    {{-- Código texto siempre visible con feedback al copiar --}}
                    <div class="relative mb-4">
                        <p
                            class="text-lg font-mono font-bold text-gray-800 tracking-widest select-all bg-gray-50 px-3 py-2.5 rounded-lg border border-gray-200">
                            {{ $shipment->code }}
                        </p>
                        <span x-show="copied" x-transition x-cloak
                            class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full shadow-sm">
                            ¡Copiado!
                        </span>
                    </div>

                    {{-- Botones para ver códigos grandes --}}
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="showQr = true"
                            class="flex flex-col items-center gap-1.5 px-3 py-3.5 rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer group">
                            <span
                                class="material-symbols-outlined text-blue-600 text-2xl group-hover:scale-110 transition-transform">qr_code</span>
                            <span class="text-xs font-medium text-gray-700">Ver QR</span>
                        </button>
                        <button type="button" @click="showBarcode = true"
                            class="flex flex-col items-center gap-1.5 px-3 py-3.5 rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer group">
                            <span
                                class="material-symbols-outlined text-blue-600 text-2xl group-hover:scale-110 transition-transform">barcode</span>
                            <span class="text-xs font-medium text-gray-700">Ver código de barras</span>
                        </button>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <button type="button"
                            @click="navigator.clipboard.writeText('{{ $shipment->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-blue-50 hover:border-blue-300 transition">
                            <span class="material-symbols-outlined text-gray-500 text-lg">content_copy</span>
                            Copiar
                        </button>
                        <a href="{{ route('bodega.shipments.receive', ['code' => $shipment->code]) }}"
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-green-600 text-sm text-white hover:bg-green-700 transition">
                            <span class="material-symbols-outlined text-lg">qr_code_scanner</span>
                            Recibir
                        </a>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-3">Usá el QR, código de barras o copiá el código para
                        confirmar</p>
                </div>

                {{-- Modal QR grande --}}
                <div x-show="showQr" x-cloak x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click.away="showQr = false"
                    @keydown.escape.window="showQr = false"
                    class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                    style="display: none;">
                    <div x-show="showQr" x-transition:enter="ease-out duration-200 delay-100"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-90"
                        class="relative bg-white rounded-2xl shadow-2xl p-8 text-center max-w-sm w-full">
                        <button type="button" @click="showQr = false"
                            class="absolute top-3 right-3 p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                        <h3 class="text-base font-semibold text-gray-800 mb-5">Código QR</h3>
                        <div class="flex justify-center mb-4">
                            @php $qr = new \Milon\Barcode\DNS2D();
                            $qr->setStorPath(storage_path('framework/cache/')); @endphp
                            <div class="p-3 bg-white rounded-xl border-2 border-gray-200 shadow-sm inline-block">
                                {!! $qr->getBarcodeHTML($shipment->code, 'QRCODE', 10, 10) !!}
                            </div>
                        </div>
                        <p class="text-sm font-mono font-bold text-gray-700 tracking-widest">{{ $shipment->code }}</p>
                        <p class="text-xs text-gray-400 mt-2">Escaneá con la cámara de tu celular</p>
                    </div>
                </div>

                {{-- Modal código de barras grande --}}
                <div x-show="showBarcode" x-cloak x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click.away="showBarcode = false"
                    @keydown.escape.window="showBarcode = false"
                    class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                    style="display: none;">
                    <div x-show="showBarcode" x-transition:enter="ease-out duration-200 delay-100"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-90"
                        class="relative bg-white rounded-2xl shadow-2xl p-8 text-center max-w-lg w-full">
                        <button type="button" @click="showBarcode = false"
                            class="absolute top-3 right-3 p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                        <h3 class="text-base font-semibold text-gray-800 mb-5">Código de barras</h3>
                        <div class="flex justify-center mb-4 overflow-x-auto">
                            @php $bc = new \Milon\Barcode\DNS1D();
                            $bc->setStorPath(storage_path('framework/cache/')); @endphp
                            <div
                                class="p-4 bg-white rounded-xl border-2 border-gray-200 shadow-sm inline-block min-w-[300px]">
                                {!! $bc->getBarcodeHTML($shipment->code, 'C128', 3, 60) !!}
                            </div>
                        </div>
                        <p class="text-sm font-mono font-bold text-gray-700 tracking-widest">{{ $shipment->code }}</p>
                        <p class="text-xs text-gray-400 mt-2">Escanéable con lector láser</p>
                    </div>
                </div>

                {{-- Items del envío --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-4">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-400 text-base">inventory_2</span>
                        Productos
                        <span
                            class="ml-auto text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $shipment->items->count() }}</span>
                    </h2>
                <div class="space-y-2">
                    @foreach($shipment->items as $item)
                        <div
                            class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <span class="text-sm text-gray-700">{{ $item->product_name }}</span>
                            <span
                                    class="text-sm font-mono font-bold text-gray-900 bg-white px-3 py-0.5 rounded border border-gray-200">{{ (int) $item->quantity }}</span>
                            </div>
                    @endforeach
                    </div>
                </div>

            {{-- Acciones --}}
            @if($shipment->status === 'pending')
                <button type="button" wire:click="markInTransit" wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove class="material-symbols-outlined text-lg">local_shipping</span>
                    <span wire:loading class="animate-spin">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                    <span wire:loading.remove>Marcar como enviado</span>
                        <span wire:loading>Procesando...</span>
                    </button>
            @endif
            @if($shipment->status === 'in_transit' && auth()->user()->activeBranchId() == $shipment->branch_id)
                <button type="button" wire:click="markDelivered" wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-amber-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove class="material-symbols-outlined text-lg">inventory</span>
                    <span wire:loading class="animate-spin">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                    <span wire:loading.remove>Marcar como recibido</span>
                        <span wire:loading>Procesando...</span>
                    </button>
            @endif
            </div>
        </div>

    {{-- Notas --}}
    @if($shipment->notes)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-2 text-sm text-amber-800">
            <span class="material-symbols-outlined text-amber-600 text-sm flex-shrink-0 mt-0.5">sticky_note_2</span>
            <div>
                <p class="font-medium">Notas del envío</p>
                <p class="text-amber-700 mt-0.5">{{ $shipment->notes }}</p>
                </div>
            </div>
    @endif
    </div>
</div>