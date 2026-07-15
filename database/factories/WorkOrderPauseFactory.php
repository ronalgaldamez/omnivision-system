<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use App\Models\WorkOrderPause;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderPauseFactory extends Factory
{
    protected $model = WorkOrderPause::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'paused_at' => now()->subHour(),
            'resumed_at' => now(),
        ];
    }
}
