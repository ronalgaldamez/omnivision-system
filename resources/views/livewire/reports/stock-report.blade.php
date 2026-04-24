<div>
    <h1 class="text-lg font-semibold mb-4">Productos con stock bajo</h1>
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-right">Stock actual</th>
                    <th class="px-4 py-2 text-right">Stock mínimo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $product->sku }}</td>
                        <td class="px-4 py-2">{{ $product->name }}</td>
                        <td class="px-4 py-2 text-right text-red-600">{{ $product->current_stock }}</td>
                        <td class="px-4 py-2 text-right">{{ $product->stock_min }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-400">No hay productos con stock bajo</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>