<?php

namespace Database\Seeders;

use App\Models\MovementType;
use Illuminate\Database\Seeder;

class MovementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'entry',               'label' => 'Entrada',                 'icon' => 'arrow_upward',       'color_class' => 'bg-green-50 text-green-700'],
            ['code' => 'exit',                'label' => 'Salida',                  'icon' => 'arrow_downward',     'color_class' => 'bg-red-50 text-red-700'],
            ['code' => 'technician_out',      'label' => 'Salida a técnico',        'icon' => 'engineering',        'color_class' => 'bg-orange-50 text-orange-700'],
            ['code' => 'technician_return',   'label' => 'Devolución técnico',      'icon' => 'assignment_return',  'color_class' => 'bg-blue-50 text-blue-700'],
            ['code' => 'damage',              'label' => 'Dañado',                  'icon' => 'broken_image',       'color_class' => 'bg-red-100 text-red-800'],
            ['code' => 'return_to_supplier',  'label' => 'Dev. proveedor',          'icon' => 'local_shipping',     'color_class' => 'bg-purple-50 text-purple-700'],
            ['code' => 'branch_allocation',   'label' => 'Repartición a sucursal',  'icon' => 'store',              'color_class' => 'bg-teal-50 text-teal-700'],
            ['code' => 'requisition_out',     'label' => 'Requisición',             'icon' => 'handyman',           'color_class' => 'bg-amber-50 text-amber-700'],
        ];

        foreach ($types as $type) {
            MovementType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
