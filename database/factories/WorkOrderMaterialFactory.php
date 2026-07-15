<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\RequisitionItem;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderMaterialFactory extends Factory
{
    protected $model = WorkOrderMaterial::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'product_id' => Product::factory(),
            'quantity_used' => fake()->randomFloat(2, 1, 10),
            'requisition_item_id' => RequisitionItem::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
