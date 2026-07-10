<div class="max-w-2xl mx-auto">
    <x-ui.card title="Recibir envío" icon="qr_code_scanner" subtitle="Ingresá el código del envío para confirmar su recepción">
        <div class="space-y-6">
            @if(!$found)
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" wire:model="code" placeholder="Ej: ENV-00001" class="w-full pl-10 pr-3 py-3 text-lg font-mono text-center tracking-widest rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition"
                        x-data x-init="$watch('$wire.code', val => { if(val.length >= 9) $wire.search(); })">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">qr_code</span>
                </div>
                <x-ui.button variant="primary" wire:click="search">Buscar</x-ui.button>
            </div>
            @endif

            @if($found && $shipment)
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
                    <span class="material-symbols-outlined text-green-600 text-5xl">check_circle</span>
                    <h2 class="text-lg font-semibold text-gray-900 mt-2">{{ $shipment->code }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $shipment->branch?->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">Creado por {{ $shipment->creator?->name }} · {{ $shipment->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead><tr class="bg-gray-50"><th class="px-4 py-2.5 text-left text-gray-600 font-medium">Producto</th><th class="px-4 py-2.5 text-center text-gray-600 font-medium">Cantidad</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($shipment->items as $item)
                            <tr class="hover:bg-gray-50"><td class="px-4 py-2.5 text-gray-800">{{ $item->product_name }}</td><td class="px-4 py-2.5 text-center font-mono font-bold">{{ (int) $item->quantity }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($shipment->status === 'delivered')
                <div class="flex justify-center pt-2">
                    <x-ui.button variant="primary" icon="check_circle" wire:click="confirm" class="!bg-green-600 hover:!bg-green-700 !text-lg !px-8 !py-3">
                        Confirmar recepción
                    </x-ui.button>
                </div>
                @elseif($shipment->status === 'confirmed')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center text-sm text-blue-700">
                    Este envío ya fue confirmado el {{ $shipment->confirmed_at?->format('d/m/Y H:i') }} por {{ $shipment->confirmer?->name ?? '—' }}
                </div>
                @else
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center text-sm text-amber-700">
                    Este envío está en estado "{{ $shipment->status }}". Esperá a que llegue a la sucursal para confirmarlo.
                </div>
                @endif

                <div class="flex justify-center">
                    <button type="button" wire:click="$set('found', false); $set('code', ''); $set('shipment', null)" class="text-sm text-blue-600 hover:text-blue-800 transition">
                        Buscar otro envío
                    </button>
                </div>
            </div>
            @endif
        </div>
    </x-ui.card>
</div>
