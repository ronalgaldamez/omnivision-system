<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Devolución a Proveedor</h1>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Producto *</label>
            <input type="text" wire:model.live="productSearch" placeholder="Buscar producto..."
                class="mt-1 w-full rounded-md border-gray-300 text-sm">
            @if($productSearch && $products->count())
                <ul class="border rounded-md mt-1 bg-white max-h-40 overflow-auto">
                    @foreach($products as $product)
                        <li wire:click="setProduct({{ $product->id }})" class="p-2 hover:bg-gray-100 cursor-pointer text-sm">
                            {{ $product->name }} (Stock: {{ $product->current_stock }})</li>
                    @endforeach
                </ul>
            @endif
            <select wire:model="product_id" class="mt-2 w-full rounded-md border-gray-300">
                <option value="">Seleccione producto</option>
                @foreach(\App\Models\Product::orderBy('name')->get() as $product)
                    <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->current_stock }})</option>
                @endforeach
            </select>
            @error('product_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Cantidad a devolver *</label>
            <input type="number" wire:model="quantity" class="mt-1 w-full rounded-md border-gray-300">
            @error('quantity') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Motivo</label>
            <textarea wire:model="reason" rows="2" class="mt-1 w-full rounded-md border-gray-300"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Compra asociada (opcional)</label>
            <select wire:model="purchase_id" class="mt-1 w-full rounded-md border-gray-300">
                <option value="">Ninguna</option>
                @foreach($purchases as $purchase)
                    <option value="{{ $purchase->id }}">{{ $purchase->invoice_number }} - {{ $purchase->supplier->name }}
                        ({{ $purchase->purchase_date }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('returns.index') }}"
                class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Cancelar</a>
            <button type="submit"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Registrar
                Devolución</button>
        </div>
    </form>
    @if(session('message'))
    <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div> @endif
    @if(session('error'))
    <div class="mt-2 text-sm text-red-600">{{ session('error') }}</div> @endif
</div>