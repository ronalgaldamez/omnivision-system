<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">{{ $branchId ? 'edit' : 'add_circle' }}</span>
                    {{ $branchId ? 'Editar Sucursal' : 'Nueva Sucursal' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">{{ $branchId ? 'Modifica los datos de la sucursal' : 'Registra una nueva sucursal en el sistema' }}</p>
            </div>
            <a href="{{ route('admin.branches.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-blue-600 transition group">
                <span class="material-symbols-outlined text-base group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
                Volver
            </a>
        </div>

        <form wire:submit.prevent="save" class="p-6 space-y-5">
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
                <a href="{{ route('admin.branches.index') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                    <span class="material-symbols-outlined text-base">save</span>
                    {{ $branchId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>

        @if(session('message'))
            <div class="mx-6 mb-6 flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>
