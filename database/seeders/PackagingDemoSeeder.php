<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductPackaging;

class PackagingDemoSeeder extends Seeder
{
    public function run()
    {
        // Tomar los primeros 5 productos existentes
        $products = Product::whereNull('base_unit')->limit(5)->get();

        foreach ($products as $i => $product) {
            $units = ['metro', 'unidad', 'pieza', 'rollo', 'par'];
            $product->update(['base_unit' => $units[$i % count($units)]]);

            ProductPackaging::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Unidad'],
                ['quantity_in_base_unit' => 1, 'is_default_for_purchase' => true]
            );

            ProductPackaging::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Caja x24'],
                ['quantity_in_base_unit' => 24, 'is_default_for_purchase' => false]
            );

            ProductPackaging::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Caja x100'],
                ['quantity_in_base_unit' => 100, 'is_default_for_purchase' => false]
            );
        }

        $this->command->info(count($products) . ' productos actualizados con empaques de prueba.');
    }
}
