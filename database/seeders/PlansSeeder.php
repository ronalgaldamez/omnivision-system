<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Plan Estudiantil / Básico',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 24.99,
                'speed' => '80 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Familiar',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 25.99,
                'speed' => '100 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Teletrabajo / Home Office',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 27.99,
                'speed' => '150 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Streaming / Pro',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 34.99,
                'speed' => '200 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Gamer',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 45.00,
                'speed' => '300 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Plan Pro Total / Avanzado',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 66.00,
                'speed' => '500 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Combo Inicial',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 29.99,
                'speed' => '50 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Combo Entretenimiento',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 34.99,
                'speed' => '150 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Combo Premium',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 39.99,
                'speed' => '200 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Combo Gamer TV',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 65.00,
                'speed' => '300 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Combo Total HD',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 86.00,
                'speed' => '500 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Combo Familiar',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 31.99,
                'speed' => '100 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
