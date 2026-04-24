<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Nueva Solicitud de Materiales</h1>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Notas (opcional)</label>
            <textarea wire:model="notes" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-2">
                <h2 class="font-medium">Productos solicitados</h2>
                <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                    <span class="material-symbols-outlined text-base">add</span> Agregar
                </button>
            </div>
            @foreach($items as $index => $item)
                <div class="border rounded p-3 mb-3 bg-gray-50">
                    <div class="flex justify-end">
                        <button type="button" wire:click="removeItem({{ $index }})"
                            class="text-red-500 hover:text-red-700"><span
                                class="material-symbols-outlined text-base">delete</span></button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs">Producto</label>
                            <select wire:model="items.{{ $index }}.product_id"
                                class="w-full rounded border-gray-300 text-sm">
                                <option value="">Seleccione</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} (Stock:
                                        {{ $product->current_stock }})</option>
                                @endforeach
                            </select>
                            @error("items.$index.product_id") <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs">Cantidad</label>
                            <input type="number" wire:model="items.{{ $index }}.quantity_requested"
                                class="w-full rounded border-gray-300 text-sm">
                            @error("items.$index.quantity_requested") <span
                            class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            @endforeach
            @error('items') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('technician-requests.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Enviar
                Solicitud</button>
        </div>
    </form>
    @if(session('message'))
    <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div> @endif
</div>