<?php

namespace Database\Seeders;

use App\Models\DeviceStatus;
use Illuminate\Database\Seeder;

class DeviceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'in_stock',  'name' => 'En stock',  'color_class' => 'bg-gray-100 text-gray-700'],
            ['code' => 'assigned',  'name' => 'Asignado',  'color_class' => 'bg-orange-50 text-orange-700'],
            ['code' => 'installed', 'name' => 'Instalado', 'color_class' => 'bg-green-50 text-green-700'],
            ['code' => 'damaged',   'name' => 'Dañado',    'color_class' => 'bg-red-50 text-red-700'],
        ];

        foreach ($statuses as $s) {
            DeviceStatus::firstOrCreate(['code' => $s['code']], $s);
        }
    }
}
