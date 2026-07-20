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
                'name' => 'Internet Mas Cable 500',
                'description' => '',
                'service_type' => 'internet_cable',
                'base_price' => 45.00,
                'speed' => '500 Mbps',
                'channels' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Solo Internet',
                'description' => '',
                'service_type' => 'internet',
                'base_price' => 29.99,
                'speed' => '150 Mbps',
                'channels' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Solo Cable',
                'description' => '',
                'service_type' => 'cable',
                'base_price' => 9.99,
                'speed' => null,
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
