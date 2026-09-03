<div class="max-w-2xl mx-auto">
    <x-ui.card icon="{{ $companyId ? 'edit' : 'add_circle' }}" title="{{ $companyId ? 'Editar Empresa' : 'Nueva Empresa' }}" subtitle="{{ $companyId ? 'Modifica los datos de la empresa' : 'Registra una entidad legal (razón social) en el sistema' }}">
        <x-slot:headerActions>
            <x-ui.button variant="ghost" icon="arrow_back" href="{{ route('admin.companies.index') }}">Volver</x-ui.button>
        </x-slot:headerActions>

        <form wire:submit.prevent="save" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Razón social <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="razonSocial"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Ej: Omnivision S.A. de C.V.">
                    @error('razonSocial')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nombre comercial
                    </label>
                    <input type="text" wire:model="nombreComercial"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Ej: Omnivision">
                    <p class="text-xs text-gray-400 mt-1">Etiqueta visual. Si se deja vacío se usa la razón social.</p>
                    @error('nombreComercial')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="tipo"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <option value="sociedad">Sociedad</option>
                        <option value="persona_natural">Persona Natural</option>
                    </select>
                    @error('tipo')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        NIT
                    </label>
                    <input type="text" wire:model="nit"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm font-mono uppercase"
                        placeholder="Ej: 0614-180595-101-2">
                    <p class="text-xs text-gray-400 mt-1">Opcional por ahora (la contabilidad se integra después).</p>
                    @error('nit')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Estado de la empresa</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $isActive ? 'La empresa está activa' : 'La empresa está inactiva' }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" wire:model.live="isActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-200">
                <x-ui.button variant="ghost" href="{{ route('admin.companies.index') }}">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="save">{{ $companyId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
            </div>
        </form>

        @if(session('message'))
            <div class="mx-6 mb-6">
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            </div>
        @endif
    </x-ui.card>
</div>
