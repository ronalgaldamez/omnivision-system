<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
            Nombre *
        </label>
        <div class="relative">
            <input type="text" wire:model="name"
                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                placeholder="Nombre del producto">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
        </div>
        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-gray-400 text-base">qr_code</span>
                SKU
            </label>
            <div class="relative">
                <input type="text" wire:model="sku" readonly
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 shadow-sm focus:ring-0 focus:border-gray-200 transition text-sm cursor-not-allowed"
                    placeholder="Se generará automáticamente">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">tag</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">El código SKU se asigna automáticamente si no se proporciona.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-gray-400 text-base">straighten</span>
                Unidad de medida
            </label>
            <div class="relative">
                <input type="text" wire:model="unit_measure"
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                    placeholder="Ej: Unidad, Metro, Kg">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">straighten</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-gray-400 text-base">inventory</span>
                Stock inicial
            </label>
            <div class="relative">
                <input type="number" step="any" wire:model="current_stock"
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">numbers</span>
            </div>
            @error('current_stock') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-gray-400 text-base">warning</span>
                Stock mínimo
            </label>
            <div class="relative">
                <input type="number" step="any" wire:model="stock_min"
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">warning</span>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex justify-end gap-3 pt-2">
        <button type="button" wire:click="$parent.closeProductModal"
            class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
            Cancelar
        </button>
        <button type="button" wire:click="save"
            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
            <span class="material-symbols-outlined text-base">save</span>
            Guardar producto
        </button>
    </div>
</div>