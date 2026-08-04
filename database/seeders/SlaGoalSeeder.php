<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SlaGoal;
use App\Models\ServiceType;

class SlaGoalSeeder extends Seeder
{
    public function run()
    {
        $goals = [
            'instalacion' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'traslado' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'revision' => ['P1' => 120, 'P2' => 240, 'P3' => 480, 'P4' => 960],
            'cobro_pendiente' => ['P1' => 480, 'P2' => 960, 'P3' => 1440, 'P4' => 2880],
            'reconexion' => ['P1' => 120, 'P2' => 240, 'P3' => 480, 'P4' => 960],
            'desconexion' => ['P1' => 120, 'P2' => 240, 'P3' => 480, 'P4' => 720],
            'habilitacion' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'deshabilitacion' => ['P1' => 120, 'P2' => 240, 'P3' => 480, 'P4' => 720],
            'verificacion_tecnica' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'conexionado' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'conexion' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'adicion_equipo' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'cambio_plan' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
            'soporte_tecnico' => ['P1' => 60, 'P2' => 120, 'P3' => 240, 'P4' => 480],
            'verificacion_instalacion' => ['P1' => 240, 'P2' => 480, 'P3' => 960, 'P4' => 1440],
        ];

        foreach ($goals as $serviceName => $priorities) {
            $serviceType = ServiceType::where('name', $serviceName)->first();

            if (!$serviceType) {
                $this->command?->warn("Tipo de servicio «{$serviceName}» no encontrado. Omitido.");
                continue;
            }

            foreach ($priorities as $priority => $minutes) {
                SlaGoal::firstOrCreate(
                    ['priority' => $priority, 'service_type_id' => $serviceType->id],
                    [
                        'minutes' => $minutes,
                        'is_active' => true,
                        'description' => "Meta SLA {$serviceName} - prioridad {$priority}",
                    ]
                );
            }
        }
    }
}
