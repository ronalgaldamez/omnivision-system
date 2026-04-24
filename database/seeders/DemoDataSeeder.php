<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\ProductModel;
use App\Models\Category;
use App\Models\Product;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // ========== CATEGORÍAS ==========
        $categorias = [
            ['name' => 'Electrónica', 'description' => 'Componentes y dispositivos electrónicos'],
            ['name' => 'Herramientas', 'description' => 'Herramientas manuales y eléctricas'],
            ['name' => 'Seguridad', 'description' => 'Equipos de seguridad y protección'],
            ['name' => 'Redes', 'description' => 'Equipos de red y conectividad'],
            ['name' => 'Cables y Accesorios', 'description' => 'Cables, conectores y accesorios'],
        ];

        foreach ($categorias as $cat) {
            Category::create($cat);
        }

        // ========== MARCAS ==========
        $marcas = [
            ['name' => 'MikroTik', 'description' => 'Equipos de redes y telecomunicaciones'],
            ['name' => 'TP-Link', 'description' => 'Equipos de red y dispositivos inteligentes'],
            ['name' => 'Stanley', 'description' => 'Herramientas manuales y eléctricas'],
            ['name' => '3M', 'description' => 'Productos de seguridad y adhesivos'],
            ['name' => 'Hikvision', 'description' => 'Cámaras y sistemas de seguridad'],
        ];

        foreach ($marcas as $marca) {
            Brand::create($marca);
        }

        // ========== MODELOS (con relaciones a marca y categoría) ==========
        $mikrotik = Brand::where('name', 'MikroTik')->first();
        $tplink = Brand::where('name', 'TP-Link')->first();
        $stanley = Brand::where('name', 'Stanley')->first();
        $hikvision = Brand::where('name', 'Hikvision')->first();

        $redes = Category::where('name', 'Redes')->first();
        $herramientas = Category::where('name', 'Herramientas')->first();
        $seguridad = Category::where('name', 'Seguridad')->first();
        $cables = Category::where('name', 'Cables y Accesorios')->first();

        $modelos = [
            ['brand_id' => $mikrotik->id, 'name' => 'hAP ac2', 'category_id' => $redes->id],
            ['brand_id' => $mikrotik->id, 'name' => 'RB750Gr3', 'category_id' => $redes->id],
            ['brand_id' => $tplink->id, 'name' => 'Archer C6', 'category_id' => $redes->id],
            ['brand_id' => $tplink->id, 'name' => 'TL-SG108', 'category_id' => $redes->id],
            ['brand_id' => $stanley->id, 'name' => 'Martillo 16oz', 'category_id' => $herramientas->id],
            ['brand_id' => $stanley->id, 'name' => 'Destornillador 6pzas', 'category_id' => $herramientas->id],
            ['brand_id' => $hikvision->id, 'name' => 'DS-2CD1321', 'category_id' => $seguridad->id],
            ['brand_id' => $hikvision->id, 'name' => 'DS-7604NI', 'category_id' => $seguridad->id],
            ['brand_id' => $mikrotik->id, 'name' => 'SXTsq 5 ac', 'category_id' => $redes->id],
            ['brand_id' => $tplink->id, 'name' => 'Deco M4', 'category_id' => $redes->id],
        ];

        foreach ($modelos as $modelo) {
            ProductModel::create($modelo);
        }

        // ========== PRODUCTOS (sin SKU manual, el sistema lo genera automáticamente) ==========
        $hap = ProductModel::where('name', 'hAP ac2')->first();
        $martillo = ProductModel::where('name', 'Martillo 16oz')->first();
        $camara = ProductModel::where('name', 'DS-2CD1321')->first();
        $switch = ProductModel::where('name', 'TL-SG108')->first();

        $productos = [
            [
                'name' => 'Router MikroTik hAP ac2',
                'description' => 'Router inalámbrico dual band, 5 puertos Gigabit',
                'current_stock' => 0,
                'stock_min' => 5,
                'stock_max' => 50,
                'unit_of_measure' => 'unidad',
                'measure_value' => 1,
                'brand_id' => $mikrotik->id,
                'model_id' => $hap->id,
                'category_id' => $redes->id,
            ],
            [
                'name' => 'Martillo Stanley 16oz',
                'description' => 'Martillo de carpintero con mango de fibra de vidrio',
                'current_stock' => 0,
                'stock_min' => 10,
                'stock_max' => 100,
                'unit_of_measure' => 'unidad',
                'measure_value' => 1,
                'brand_id' => $stanley->id,
                'model_id' => $martillo->id,
                'category_id' => $herramientas->id,
            ],
            [
                'name' => 'Cámara Hikvision DS-2CD1321',
                'description' => 'Cámara IP 2MP, visión nocturna',
                'current_stock' => 0,
                'stock_min' => 3,
                'stock_max' => 20,
                'unit_of_measure' => 'unidad',
                'measure_value' => 1,
                'brand_id' => $hikvision->id,
                'model_id' => $camara->id,
                'category_id' => $seguridad->id,
            ],
            [
                'name' => 'Switch TP-Link TL-SG108',
                'description' => 'Switch Gigabit 8 puertos no gestionado',
                'current_stock' => 0,
                'stock_min' => 8,
                'stock_max' => 60,
                'unit_of_measure' => 'unidad',
                'measure_value' => 1,
                'brand_id' => $tplink->id,
                'model_id' => $switch->id,
                'category_id' => $redes->id,
            ],
            [
                'name' => 'Cable UTP Cat6',
                'description' => 'Cable de red categoría 6, rollo 305m',
                'current_stock' => 0,
                'stock_min' => 100,
                'stock_max' => 2000,
                'unit_of_measure' => 'm',
                'measure_value' => null,
                'brand_id' => null,
                'model_id' => null,
                'category_id' => $cables->id,
            ],
        ];

        foreach ($productos as $prod) {
            Product::create($prod);  // El SKU se genera automáticamente en el modelo
        }

        $this->command->info('✅ Datos de prueba creados: categorías, marcas, modelos y productos (SKU automático).');
    }
}