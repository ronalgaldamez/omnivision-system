<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'instalacion', 'requires_noc' => false],
            ['name' => 'traslado', 'requires_noc' => false],
            ['name' => 'revision', 'requires_noc' => true],
            ['name' => 'cobro_pendiente', 'requires_noc' => false],
            ['name' => 'reconexion', 'requires_noc' => false],
            ['name' => 'desconexion', 'requires_noc' => false],
            ['name' => 'habilitacion', 'requires_noc' => true],
            ['name' => 'deshabilitacion', 'requires_noc' => true],
            ['name' => 'verificacion_tecnica', 'requires_noc' => true],
            ['name' => 'conexionado', 'requires_noc' => false],
            ['name' => 'conexion', 'requires_noc' => false],
        ];

        foreach ($types as $type) {
            ServiceType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}