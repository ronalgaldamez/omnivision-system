<div class="max-w-4xl mx-auto">
    <x-ui.card title="Detalle del Dispositivo" icon="settings_ethernet" subtitle="{{ $device->mac_address }}">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('devices.index') }}">Volver</x-ui.button>
        </x-slot:headerActions>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">MAC Address</p>
                    <p class="font-mono font-semibold text-gray-800 mt-1">{{ $device->mac_address }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">PON SN</p>
                    <p class="font-mono text-gray-700 mt-1">{{ $device->pon_sn ?? '—' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Producto</p>
                    <p class="text-gray-800 mt-1">{{ $device->product?->name }} <span class="text-gray-500 font-mono">({{ $device->product?->sku }})</span></p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Estado</p>
                    @php $ds = $device->deviceStatus; @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 mt-1 rounded-full text-xs font-medium {{ $ds?->color_class ?? 'bg-gray-100 text-gray-700' }}">{{ $ds?->name ?? $device->status }}</span>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Compra asociada</p>
                    <p class="text-gray-700 mt-1">{{ $device->purchase?->invoice_number ?? '—' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Técnico asignado</p>
                    <p class="text-gray-700 mt-1">{{ $device->technician?->name ?? '—' }}</p>
                </div>
            </div>

            @if($device->default_ip || $device->default_username)
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500">settings</span>
                    Datos predeterminados
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">IP</p><p class="font-mono text-gray-800 mt-0.5">{{ $device->default_ip ?? '—' }}</p></div>
                    <div class="p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">Username</p><p class="font-mono text-gray-800 mt-0.5">{{ $device->default_username ?? '—' }}</p></div>
                    <div class="p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">Password</p><p class="font-mono text-gray-800 mt-0.5">{{ $device->default_password ? '••••••' : '—' }}</p></div>
                    <div class="p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">SSID1</p><p class="text-gray-800 mt-0.5">{{ $device->default_ssid1 ?? '—' }}</p></div>
                    <div class="p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">LAN Key</p><p class="font-mono text-gray-800 mt-0.5">{{ $device->default_lan_key ?? '—' }}</p></div>
                </div>
            </div>
            @endif

            @if($device->workOrder)
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500">assignment</span>
                    Orden de trabajo
                </h2>
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm"><a href="{{ route('work-orders.show', $device->workOrder->id) }}" class="text-blue-700 hover:text-blue-900 font-medium">{{ $device->workOrder->code ?? 'OT #' . $device->workOrder->id }}</a></p>
                    <p class="text-xs text-blue-600 mt-1">Instalado el {{ $device->installed_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
            </div>
            @endif

            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-gray-500">history</span>
                    Línea de tiempo
                </h2>
                <div class="space-y-3">
                    @if($device->installed_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-green-600 text-sm">check_circle</span></div>
                        <div><p class="text-sm font-medium text-gray-800">Instalado</p><p class="text-xs text-gray-500">{{ $device->installed_at->format('d/m/Y H:i') }}</p></div>
                    </div>
                    @endif
                    @if($device->assigned_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-orange-600 text-sm">assignment_ind</span></div>
                        <div><p class="text-sm font-medium text-gray-800">Asignado a {{ $device->technician?->name ?? '—' }}</p><p class="text-xs text-gray-500">{{ $device->assigned_at->format('d/m/Y H:i') }}</p></div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-gray-600 text-sm">inventory</span></div>
                        <div><p class="text-sm font-medium text-gray-800">Registrado en stock</p><p class="text-xs text-gray-500">{{ $device->created_at->format('d/m/Y H:i') }}</p></div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>
</div>
