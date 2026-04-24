<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">{{ $supplierId ? 'Editar' : 'Nuevo' }} Proveedor</h1>

    <form wire:submit.prevent="confirmSave" class="space-y-4">
        <div class="relative">
            <label class="block text-sm font-medium">Nombre *</label>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 text-base">business</span>
                <input type="text" wire:model="name" class="pl-8 w-full rounded-md border-gray-300">
            </div>
            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="relative">
            <label class="block text-sm font-medium">Persona de contacto</label>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 text-base">contact_page</span>
                <input type="text" wire:model="contact_name" class="pl-8 w-full rounded-md border-gray-300">
            </div>
        </div>

        <div class="relative">
            <label class="block text-sm font-medium">Teléfono</label>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 text-base">phone</span>
                <input type="text" wire:model="phone" class="pl-8 w-full rounded-md border-gray-300">
            </div>
        </div>

        <div class="relative">
            <label class="block text-sm font-medium">Email</label>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 text-base">mail</span>
                <input type="email" wire:model="email" class="pl-8 w-full rounded-md border-gray-300">
            </div>
            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="relative">
            <label class="block text-sm font-medium">Dirección</label>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 text-base">location_on</span>
                <textarea wire:model="address" rows="2" class="pl-8 w-full rounded-md border-gray-300"></textarea>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">NRC</label>
            <input type="text" wire:model="nrc" class="w-full rounded-md border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium">NIT</label>
            <input type="text" wire:model="nit" class="w-full rounded-md border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium">Cuentas bancarias</label>
            @foreach($bankAccounts as $index => $account)
                <div wire:key="bank-{{ $index }}" class="flex gap-2 mt-1">
                    <input type="text" wire:model="bankAccounts.{{ $index }}.bank_name"
                        class="flex-1 rounded-md border-gray-300" placeholder="Nombre del banco">
                    <input type="text" wire:model="bankAccounts.{{ $index }}.account_number"
                        class="flex-1 rounded-md border-gray-300" placeholder="Número de cuenta">
                    <button type="button" wire:click="removeBankAccount({{ $index }})"
                        class="text-red-500">Eliminar</button>
                </div>
            @endforeach
            <button type="button" wire:click="addBankAccount" class="mt-2 text-blue-600 text-sm">+ Agregar
                cuenta</button>
        </div>

        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('suppliers.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">{{ $supplierId ? 'Actualizar' : 'Guardar' }}</button>
        </div>
    </form>

    <!-- Modal de confirmación -->
    <div x-data="{ show: @entangle('showConfirmModal') }" x-show="show" x-cloak
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar guardado</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">¿Estás seguro de {{ $supplierId ? 'actualizar' : 'guardar' }} este
                        proveedor?</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="save"
                        class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700">Sí,
                        {{ $supplierId ? 'actualizar' : 'guardar' }}</button>
                    <button @click="show = false"
                        class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full hover:bg-gray-400">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-data="{ toast: null, toastType: null, toastMessage: '' }"
        x-on:show-toast.window="toast = true; toastType = $event.detail.type; toastMessage = $event.detail.message; setTimeout(() => toast = false, 5000)"
        x-show="toast" x-cloak class="fixed bottom-5 right-5 z-50 transition-all duration-300" style="display: none;">
        <div x-show="toastType === 'success'"
            class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span> <span x-text="toastMessage"></span>
        </div>
        <div x-show="toastType === 'error'"
            class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <span class="material-symbols-outlined">error</span> <span x-text="toastMessage"></span>
        </div>
    </div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>