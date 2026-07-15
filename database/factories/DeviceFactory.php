<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'purchase_id' => Purchase::factory(),
            'mac_address' => fake()->unique()->macAddress(),
            'pon_sn' => fake()->unique()->lexify('PON-??????'),
            'default_ip' => fake()->localIpv4(),
            'default_username' => fake()->userName(),
            'default_password' => fake()->password(),
            'default_ssid1' => fake()->word(),
            'default_lan_key' => fake()->password(),
            'status' => fake()->randomElement(['in_stock', 'assigned', 'installed', 'damaged']),
            'technician_id' => null,
            'assigned_at' => null,
            'work_order_id' => null,
            'installed_at' => null,
            'branch_id' => null,
        ];
    }

    public function inStock(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'in_stock']);
    }
}
