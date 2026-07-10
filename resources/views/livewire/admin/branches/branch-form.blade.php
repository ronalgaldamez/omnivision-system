<div class="max-w-2xl mx-auto">
    <x-ui.card icon="{{ $branchId ? 'edit' : 'add_circle' }}" title="{{ $branchId ? 'Editar Sucursal' : 'Nueva Sucursal' }}" subtitle="{{ $branchId ? 'Modifica los datos de la sucursal' : 'Registra una nueva sucursal en el sistema' }}">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('admin.branches.index') }}">Volver</x-ui.button>
        </x-slot:headerActions>

        <form wire:submit.prevent="save" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="name"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Ej: Sucursal Amayo">
                    @error('name')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="code"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm font-mono uppercase"
                        placeholder="Ej: AMAYO">
                    @error('code')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Teléfono
                    </label>
                    <input type="text" wire:model="phone"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Ej: 2300-0000">
                    @error('phone')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Dirección
                    </label>
                    <textarea wire:model="address" rows="2"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm resize-none"
                        placeholder="Dirección de la sucursal"></textarea>
                    @error('address')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Estado de la sucursal</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $isActive ? 'La sucursal está activa y operativa' : 'La sucursal está inactiva' }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" wire:model.live="isActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-200">
                <x-ui.button variant="ghost" href="{{ route('admin.branches.index') }}">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="save">{{ $branchId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
            </div>
        </form>

        @if(session('message'))
            <div class="mx-6 mb-6">
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            </div>
        @endif
    </x-ui.card>
</div>