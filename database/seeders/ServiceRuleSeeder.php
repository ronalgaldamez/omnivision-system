<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;
use App\Models\ServiceRule;

class ServiceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $verificacion = ServiceType::where('name', 'verificacion_instalacion')->first();
        if (!$verificacion) return;

        $rules = [
            [
                'rule_key' => 'free_distance',
                'rule_value' => ['meters' => 150],
            ],
            [
                'rule_key' => 'price_per_meter',
                'rule_value' => ['amount' => 5],
            ],
            [
                'rule_key' => 'required_fields',
                'rule_value' => ['fields' => ['phone', 'departamento', 'municipio', 'distrito', 'address']],
            ],
            [
                'rule_key' => 'auto_create_ot',
                'rule_value' => ['enabled' => true],
            ],
        ];

        foreach ($rules as $rule) {
            ServiceRule::firstOrCreate(
                ['service_type_id' => $verificacion->id, 'rule_key' => $rule['rule_key']],
                ['rule_value' => $rule['rule_value']]
            );
        }
    }
}
