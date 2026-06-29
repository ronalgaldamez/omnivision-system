<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackagingType;

class PackagingTypesSeeder extends Seeder
{
    public function run()
    {
        $types = ['Caja', 'Bolsa', 'Rollo', 'Paquete', 'Pallet', 'Unidad', 'Saco', 'Bobina', 'Blister', 'Sobre'];

        foreach ($types as $type) {
            PackagingType::firstOrCreate(['name' => $type]);
        }

        $this->command->info('Tipos de empaque creados: ' . implode(', ', $types));
    }
}
