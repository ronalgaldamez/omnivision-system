<div class="space-y-5">
    <x-ui.input icon="edit_note" wire:model="name" label="Nombre *" required placeholder="Nombre del producto" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.input icon="tag" wire:model="sku" label="SKU" placeholder="Se generará automáticamente" disabled readonly />
        <x-ui.input icon="straighten" wire:model="unit_measure" label="Unidad de medida" placeholder="Ej: Unidad, Metro, Kg" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.input type="number" step="any" icon="numbers" wire:model="current_stock" label="Stock inicial" />
        <x-ui.input type="number" step="any" icon="warning" wire:model="stock_min" label="Stock mínimo" />
    </div>

    <div class="flex justify-end gap-3 pt-2">
        <x-ui.button variant="secondary" wire:click="$parent.closeProductModal">Cancelar</x-ui.button>
        <x-ui.button variant="primary" icon="save" wire:click="save">Guardar producto</x-ui.button>
    </div>
</div>
