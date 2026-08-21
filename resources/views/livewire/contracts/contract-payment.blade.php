<div class="max-w-7xl mx-auto">
    <x-ui.card icon="payments" title="Pagos" subtitle="Registrá abonos (pago proporcional) o cuotas de cada contrato">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Lista de contratos --}}
            <div>
                <x-ui.input type="text" wire:model.live.debounce.300ms="search" icon="search" placeholder="Buscar por cliente..." />
                <div class="mt-3 space-y-2 max-h-[70vh] overflow-y-auto pr-1">
                    @forelse($contracts as $contract)
                        <button wire:click="selectContract({{ $contract->id }})"
                            class="w-full text-left p-3 rounded-lg border transition
                            {{ $contract_id === $contract->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $contract->client?->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $contract->contract_digital_code }} · {{ $contract->plan?->name ?? '—' }}</p>
                                </div>
                                <x-ui.badge variant="{{ $contract->status === 'active' ? 'success' : 'info' }}">
                                    {{ ucfirst($contract->status) }}
                                </x-ui.badge>
                            </div>
                        </button>
                    @empty
                        <p class="text-center text-gray-400 text-sm py-8">No hay contratos activos.</p>
                    @endforelse
                </div>
            </div>

            {{-- Detalle del contrato seleccionado --}}
            <div>
                @if($selectedContract)
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-3">
                        <h3 class="text-sm font-semibold text-gray-800">{{ $selectedContract->client?->name }}</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500">Cuota mensual</p>
                                <p class="font-mono font-bold text-lg text-gray-800">${{ number_format($selectedContract->monthlyTotal(), 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Día de pago</p>
                                <p class="font-medium text-gray-800">{{ $selectedContract->payment_day ?? $selectedContract->payment_date ?? '—' }}</p>
                            </div>
                        </div>

                        <button wire:click="calculateAbono" class="w-full px-3 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 transition">
                            Calcular abono proporcional
                        </button>

                        @if($abono)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm space-y-1">
                                <p class="text-blue-800"><strong>Abono a pagar:</strong> ${{ number_format($abono['charge'], 2) }}</p>
                                <p class="text-blue-700 text-xs">Cuota ${{ number_format($abono['base'], 2) }} ÷ {{ $abono['days_in_month'] }} días del mes × {{ $abono['days'] }} días de servicio</p>
                                <p class="text-blue-600 text-xs">Corresponde hasta la fecha de pago (día {{ $abono['payment_day'] }}).</p>
                            </div>
                            <button wire:click="registerAbono" class="w-full px-3 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                Registrar abono (${{ number_format($abono['charge'], 2) }})
                            </button>
                        @endif

                        <button wire:click="registerCuota" class="w-full px-3 py-2 rounded-lg text-sm font-medium bg-purple-600 text-white hover:bg-purple-700 transition">
                            Registrar cuota completa (${{ number_format($selectedContract->monthlyTotal(), 2) }})
                        </button>
                    </div>

                    {{-- Historial de pagos --}}
                    <div class="mt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Historial de cobros</h4>
                        @if($charges->isEmpty())
                            <p class="text-gray-400 text-sm">Sin pagos registrados.</p>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="text-left px-3 py-2">Tipo</th>
                                            <th class="text-left px-3 py-2">Descripción</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                            <th class="text-left px-3 py-2">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($charges as $charge)
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <x-ui.badge variant="{{ $charge->charge_type === 'abono' ? 'info' : 'success' }}">
                                                        {{ ucfirst($charge->charge_type) }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="px-3 py-2 text-gray-700">{{ $charge->description }}</td>
                                                <td class="px-3 py-2 text-right font-mono font-medium">${{ number_format($charge->amount, 2) }}</td>
                                                <td class="px-3 py-2 text-xs text-gray-500">{{ $charge->applied_at?->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-center text-gray-400 py-16">Seleccioná un contrato para registrar pagos.</p>
                @endif
            </div>
        </div>
    </x-ui.card>
</div>
