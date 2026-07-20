<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'instalacion', 'requires_noc' => false, 'requires_ot' => false, 'requires_contract' => true],
            ['name' => 'traslado', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],
            ['name' => 'revision', 'requires_noc' => true, 'requires_ot' => false, 'requires_contract' => false],
            ['name' => 'cobro_pendiente', 'requires_noc' => false, 'requires_ot' => false, 'requires_contract' => false],
            ['name' => 'reconexion', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],
            ['name' => 'desconexion', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],
            ['name' => 'habilitacion', 'requires_noc' => true, 'requires_ot' => false, 'requires_contract' => false],
            ['name' => 'deshabilitacion', 'requires_noc' => true, 'requires_ot' => false, 'requires_contract' => false],
            ['name' => 'verificacion_tecnica', 'requires_noc' => true, 'requires_ot' => false, 'requires_contract' => false],
            ['name' => 'conexionado', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],
            ['name' => 'conexion', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],

            // Nuevos tipos para cubrir los escenarios que mencionaste
            ['name' => 'adicion_equipo', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],
            ['name' => 'cambio_plan', 'requires_noc' => true, 'requires_ot' => false, 'requires_contract' => false],
            ['name' => 'soporte_tecnico', 'requires_noc' => false, 'requires_ot' => true, 'requires_contract' => false],
        ];

        foreach ($types as $type) {
            ServiceType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}