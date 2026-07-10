<div class="max-w-4xl mx-auto">
    <x-ui.card :title="($supplierId ? 'Editar' : 'Nuevo') . ' Proveedor'"
        :icon="$supplierId ? 'edit' : 'add_business'"
        :subtitle="$supplierId ? 'Modifica los datos del proveedor' : 'Registra un nuevo proveedor'">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('suppliers.index') }}">Volver al listado</x-ui.button>
        </x-slot:headerActions>

        @if($draftRestored)
            <x-ui.alert variant="warning" dismissible>
                <div class="flex items-center gap-2">
                    Se restauraron los datos de una sesión anterior.
                    <button type="button" wire:click="$set('draftRestored', false)" class="ml-auto text-amber-600 hover:text-amber-800">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </x-ui.alert>
        @endif

        <form wire:submit.prevent="confirmSave" class="space-y-6">
            <x-ui.input type="text" icon="badge" wire:model="name" label="Nombre" placeholder="Nombre del proveedor" required />
            <x-ui.input type="text" icon="person" wire:model="contact_name" label="Persona de contacto" placeholder="Nombre completo" required />

            {{-- Teléfonos múltiples --}}
            <div>
                <x-forms.label icon="call">Teléfonos</x-forms.label>
                <div class="space-y-2">
                    @foreach($phones as $index => $phone)
                        <div wire:key="phone-{{ $index }}" class="bg-gray-50/80 rounded-xl border border-gray-200 p-3 flex items-center gap-3">
                            <span class="material-symbols-outlined text-gray-400 text-xl flex-shrink-0">call</span>
                            <div class="flex-1">
                                <input type="text"
                                    x-data="{ val: '{{ $phone }}' }"
                                    x-model="val"
                                    x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                    @input="let v = val.replace(/[^0-9]/g, '').slice(0, 8); if (v.length > 4) v = v.slice(0, 4) + '-' + v.slice(4); val = v"
                                    x-on:change="$wire.set('phones.{{ $index }}', val)"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm font-mono"
                                    placeholder="0000-0000" inputmode="numeric" maxlength="9">
                            </div>
                            <button type="button" wire:click="removePhone({{ $index }})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition flex-shrink-0">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addPhone" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:text-blue-600 hover:border-blue-300 bg-gray-50/50 transition w-full justify-center">
                    <span class="material-symbols-outlined text-base">add</span>Agregar teléfono
                </button>
                @error('phones') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                @error('phones.*') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <x-ui.input type="email" icon="mail" wire:model="email" label="Email" placeholder="correo@ejemplo.com" />
            <x-ui.textarea icon="location_on" wire:model="address" label="Dirección" placeholder="Dirección física" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.label icon="description" required>NRC</x-forms.label>
                    <input type="text" wire:model="nrc"
                        x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="00000000" inputmode="numeric" maxlength="8">
                    @error('nrc') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-forms.label icon="numbers" required>NIT</x-forms.label>
                    <input type="text" wire:model="nit"
                        x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="000000000" inputmode="numeric" maxlength="9">
                    @error('nit') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Cuentas bancarias --}}
            <div>
                <x-forms.label icon="account_balance">Cuentas bancarias</x-forms.label>
                <div class="space-y-2">
                    @foreach($bankAccounts as $index => $account)
                        <div wire:key="bank-{{ $index }}" class="bg-gray-50/80 rounded-xl border border-gray-200 p-3 flex items-center gap-3">
                            <span class="material-symbols-outlined text-gray-400 text-xl flex-shrink-0">account_balance</span>
                            <div class="grid grid-cols-2 gap-3 flex-1">
                                <input type="text" wire:model="bankAccounts.{{ $index }}.bank_name" class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm" placeholder="Nombre del banco">
                                <input type="text" wire:model="bankAccounts.{{ $index }}.account_number"
                                    x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm font-mono"
                                    placeholder="Número de cuenta" inputmode="numeric">
                            </div>
                            <button type="button" wire:click="removeBankAccount({{ $index }})" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition flex-shrink-0">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addBankAccount" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:text-blue-600 hover:border-blue-300 bg-gray-50/50 transition w-full justify-center">
                    <span class="material-symbols-outlined text-base">add</span>Agregar cuenta bancaria
                </button>
                @error('bankAccounts') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" href="{{ route('suppliers.index') }}">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="save" type="submit">{{ $supplierId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    {{-- Modal confirmación --}}
    @if($showConfirmModal)
    <div x-data="{ show: true }" x-show="show" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6 max-w-md w-full mx-4 text-center">
            <span class="material-symbols-outlined text-green-600 text-4xl mb-3">check_circle</span>
            <h3 class="text-lg font-semibold">Confirmar guardado</h3>
            <p class="text-sm text-gray-500 mt-2">¿Estás seguro de {{ $supplierId ? 'actualizar' : 'guardar' }} este proveedor?</p>
            <div class="flex justify-center gap-3 mt-5">
                <button @click="show = false; $wire.set('showConfirmModal', false)" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">Cancelar</button>
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="px-5 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-green-700 transition disabled:opacity-50">
                    Sí, {{ $supplierId ? 'actualizar' : 'guardar' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
