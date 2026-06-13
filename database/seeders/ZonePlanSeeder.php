<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Zone;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class ZonePlanSeeder extends Seeder
{
    public function run()
    {
        // ========== PLANES DE EJEMPLO ==========
        $plans = [
            ['name' => 'Internet 5 Mbps', 'service_type' => 'internet', 'base_price' => 15.00, 'speed' => '5 Mbps'],
            ['name' => 'Internet 10 Mbps', 'service_type' => 'internet', 'base_price' => 20.00, 'speed' => '10 Mbps'],
            ['name' => 'Internet 25 Mbps', 'service_type' => 'internet', 'base_price' => 30.00, 'speed' => '25 Mbps'],
            ['name' => 'Internet 50 Mbps', 'service_type' => 'internet', 'base_price' => 45.00, 'speed' => '50 Mbps'],
            ['name' => 'Cable Básico', 'service_type' => 'cable', 'base_price' => 12.00, 'channels' => 30],
            ['name' => 'Cable Premium', 'service_type' => 'cable', 'base_price' => 20.00, 'channels' => 60],
            ['name' => 'Internet 10 + Cable Básico', 'service_type' => 'internet_cable', 'base_price' => 28.00, 'speed' => '10 Mbps', 'channels' => 30],
            ['name' => 'Internet 25 + Cable Premium', 'service_type' => 'internet_cable', 'base_price' => 40.00, 'speed' => '25 Mbps', 'channels' => 60],
        ];

        foreach ($plans as $data) {
            Plan::firstOrCreate(['name' => $data['name']], $data);
        }

        // ========== ZONAS BASE POR SUCURSAL ==========
        $branches = Branch::all()->keyBy('code');

        // Ejemplo: Casa Matriz Chalatenango
        $matriz = $branches->get('MATRIZ');
        if ($matriz) {
            $chalaDepto = Zone::create([
                'branch_id' => $matriz->id,
                'name' => 'Chalatenango',
                'level' => 'departamento',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            $chalaCentro = Zone::create([
                'branch_id' => $matriz->id,
                'parent_id' => $chalaDepto->id,
                'name' => 'Chalatenango Centro',
                'level' => 'municipio',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            Zone::create([
                'branch_id' => $matriz->id,
                'parent_id' => $chalaCentro->id,
                'name' => 'Tejutla',
                'level' => 'localidad',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            $chalaSur = Zone::create([
                'branch_id' => $matriz->id,
                'parent_id' => $chalaDepto->id,
                'name' => 'Chalatenango Sur',
                'level' => 'municipio',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            Zone::create([
                'branch_id' => $matriz->id,
                'parent_id' => $chalaSur->id,
                'name' => 'Nueva Trinidad',
                'level' => 'localidad',
                'has_internet' => true,
                'has_cable' => false,
            ]);
        }

        // Ejemplo: Sucursal La Palma (Chalatenango Norte)
        $palma = $branches->get('PALMA');
        if ($palma) {
            $chalaNorte = Zone::create([
                'branch_id' => $palma->id,
                'name' => 'Chalatenango Norte',
                'level' => 'departamento',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            $laPalma = Zone::create([
                'branch_id' => $palma->id,
                'parent_id' => $chalaNorte->id,
                'name' => 'La Palma',
                'level' => 'municipio',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            Zone::create([
                'branch_id' => $palma->id,
                'parent_id' => $laPalma->id,
                'name' => 'La Palma (Casco Urbano)',
                'level' => 'localidad',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            Zone::create([
                'branch_id' => $palma->id,
                'parent_id' => $laPalma->id,
                'name' => 'La Palma - Cantón El Junco',
                'level' => 'localidad',
                'has_internet' => false,
                'has_cable' => true,
            ]);
            Zone::create([
                'branch_id' => $palma->id,
                'parent_id' => $laPalma->id,
                'name' => 'La Palma - Cantón Las Flores',
                'level' => 'localidad',
                'has_internet' => true,
                'has_cable' => false,
            ]);
            Zone::create([
                'branch_id' => $palma->id,
                'parent_id' => $chalaNorte->id,
                'name' => 'San Ignacio',
                'level' => 'municipio',
                'has_internet' => true,
                'has_cable' => true,
            ]);
            Zone::create([
                'branch_id' => $palma->id,
                'parent_id' => $chalaNorte->id,
                'name' => 'Citalá',
                'level' => 'municipio',
                'has_internet' => true,
                'has_cable' => false,
            ]);
        }
    }
}
