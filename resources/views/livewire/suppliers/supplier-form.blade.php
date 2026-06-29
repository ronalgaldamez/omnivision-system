<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">{{ $supplierId ? 'edit' : 'add_business' }}</span>
                    {{ $supplierId ? 'Editar' : 'Nuevo' }} Proveedor
                </h1>
                <p class="text-sm text-gray-500 mt-1">{{ $supplierId ? 'Modifica los datos del proveedor' : 'Registra un nuevo proveedor' }}</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>Volver al listado
            </a>
        </div>

        <div class="p-6">
            @if($draftRestored)
            <div class="mb-4 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 px-4 py-3 rounded-lg border border-amber-200">
                <span class="material-symbols-outlined text-amber-600">warning</span>
                Se restauraron los datos de una sesión anterior.
                <button type="button" wire:click="$set('draftRestored', false)" class="ml-auto text-amber-600 hover:text-amber-800">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            @endif

            <form wire:submit.prevent="confirmSave" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre *</label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm" placeholder="Nombre del proveedor">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Persona de contacto *</label>
                    <input type="text" wire:model="contact_name" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm" placeholder="Nombre completo">
                    @error('contact_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Teléfonos múltiples --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfonos</label>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" wire:model="email" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm" placeholder="correo@ejemplo.com">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Dirección</label>
                    <textarea wire:model="address" rows="2" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none" placeholder="Dirección física"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">NRC *</label>
                        <input type="text" wire:model="nrc"
                            x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="00000000" inputmode="numeric" maxlength="8">
                        @error('nrc') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">NIT *</label>
                        <input type="text" wire:model="nit"
                            x-on:keydown="if(!/^[0-9]$/.test($event.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes($event.key)) $event.preventDefault()"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="000000000" inputmode="numeric" maxlength="9">
                        @error('nit') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Cuentas bancarias --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Cuentas bancarias</label>
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
                    <a href="{{ route('suppliers.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">Cancelar</a>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2 disabled:opacity-50">
                        <span class="material-symbols-outlined text-base">save</span>{{ $supplierId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

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

    {{-- Toast --}}
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-on:show-toasts.window="$event.detail.errors.forEach(msg => { toast = true; toastType = 'error'; toastMessage = msg; setTimeout(() => toast = false, 5000) })"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition" style="display:none">
        <div x-show="toastType === 'success'" class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span><span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'" class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span><span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>
    <style>[x-cloak] { display: none !important; }</style>
</div>
