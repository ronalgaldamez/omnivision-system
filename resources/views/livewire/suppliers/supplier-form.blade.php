<div class="max-w-4xl mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil y botón de regreso -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">
                        {{ $supplierId ? 'edit' : 'add_business' }}
                    </span>
                    {{ $supplierId ? 'Editar' : 'Nuevo' }} Proveedor
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $supplierId ? 'Modifica los datos del proveedor' : 'Registra un nuevo proveedor' }}
                </p>
            </div>
            <a href="{{ route('suppliers.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Volver al listado
            </a>
        </div>

        <!-- Contenido del formulario -->
        <div class="p-6">
            <form wire:submit.prevent="confirmSave" class="space-y-6">
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">business</span>
                        Nombre *
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="name"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Nombre del proveedor">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                    </div>
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Persona de contacto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">contact_page</span>
                        Persona de contacto
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="contact_name"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Nombre completo">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">badge</span>
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">phone</span>
                        Teléfono
                    </label>
                    <div class="relative">
                        <input type="text" wire:model="phone"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Número de teléfono">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">call</span>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">mail</span>
                        Email
                    </label>
                    <div class="relative">
                        <input type="email" wire:model="email"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="correo@ejemplo.com">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">alternate_email</span>
                    </div>
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Dirección -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">location_on</span>
                        Dirección
                    </label>
                    <div class="relative">
                        <textarea wire:model="address" rows="2"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                            placeholder="Dirección física"></textarea>
                        <span
                            class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">edit_note</span>
                    </div>
                </div>

                <!-- NRC y NIT en grid de 2 columnas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">description</span>
                            NRC
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="nrc"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="NRC del proveedor">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">numbers</span>
                            NIT
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="nit"
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="NIT del proveedor">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">badge</span>
                        </div>
                    </div>
                </div>

                <!-- Cuentas bancarias -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-gray-400 text-base">account_balance</span>
                        Cuentas bancarias
                    </label>
                    <div class="space-y-2">
                        @foreach($bankAccounts as $index => $account)
                            <div wire:key="bank-{{ $index }}" class="flex gap-2 items-center">
                                <div class="relative flex-1">
                                    <input type="text" wire:model="bankAccounts.{{ $index }}.bank_name"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                        placeholder="Nombre del banco">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">apartment</span>
                                </div>
                                <div class="relative flex-1">
                                    <input type="text" wire:model="bankAccounts.{{ $index }}.account_number"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                        placeholder="Número de cuenta">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">credit_card</span>
                                </div>
                                <button type="button" wire:click="removeBankAccount({{ $index }})"
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar cuenta">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addBankAccount"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 transition">
                        <span class="material-symbols-outlined text-base">add_circle</span>
                        Agregar cuenta bancaria
                    </button>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('suppliers.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ $supplierId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        style="display: none;">
        <div class="relative mx-auto p-5 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar guardado</h3>
                    <p class="text-sm text-gray-600 mt-2">
                        ¿Estás seguro de {{ $supplierId ? 'actualizar' : 'guardar' }} este proveedor?
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col gap-3 sm:flex-row-reverse">
                    <button wire:click="save"
                        class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 transition">
                        Sí, {{ $supplierId ? 'actualizar' : 'guardar' }}
                    </button>
                    <button @click="show = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300"
        x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
        style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"
                class="text-sm font-medium"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"
                class="text-sm font-medium"></span>
        </div>
    </div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>