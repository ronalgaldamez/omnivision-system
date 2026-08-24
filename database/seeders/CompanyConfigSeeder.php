<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\InstallFeeRule;
use Illuminate\Database\Seeder;

/**
 * Configuración comercial de la empresa: tarifas de instalación,
 * promociones de instalación gratis y reglas de contrato.
 *
 * Valores base:
 * - Metraje base de instalación: 150 m.
 * - Cargo base: $25.
 * - Recargo: $5 por cada 50 m adicionales.
 * Estas tarifas aplican de forma global (todas las zonas) salvo que se
 * configuren reglas específicas por zona desde el panel de administración.
 */
class CompanyConfigSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInstallFeeRules();
        $this->seedFreeInstallationCampaigns();
        $this->seedDoubleSpeedRules();
        $this->seedDiscountMonthsRules();
        $this->seedTvExtraRule();
    }

    /**
     * Tarifa de instalación global (aplica a todas las zonas) para cada tipo de servicio.
     */
    protected function seedInstallFeeRules(): void
    {
        $services = ['internet', 'cable', 'internet_cable'];

        foreach ($services as $service) {
            InstallFeeRule::updateOrCreate(
                ['zone_id' => null, 'service_type' => $service],
                [
                    'covered_meters' => 150,
                    'fee' => 25,
                    'excess_per_50m' => 5,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Promoción de instalación gratis para el mes vigente (agosto),
     * para cada tipo de servicio.
     */
    protected function seedFreeInstallationCampaigns(): void
    {
        $campaigns = [
            [
                'name' => 'Mes Instalacion Gratis Internet',
                'service' => 'internet',
            ],
            [
                'name' => 'Mes Instalacion Gratis Cable + Internet',
                'service' => 'internet_cable',
            ],
            [
                'name' => 'Mes Instalacion Gratis Cable',
                'service' => 'cable',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::firstOrCreate(
                ['type' => 'free_installation', 'category' => 'promotion', 'service' => $campaign['service']],
                [
                    'name' => $campaign['name'],
                    'zone_id' => null,
                    'starts_at' => now()->startOfMonth(),
                    'ends_at' => now()->startOfMonth()->addMonth()->subSecond(),
                    'is_active' => true,
                    'config' => null,
                ]
            );
        }
    }

    /**
     * Reglas de doble velocidad por plazo (12 y 24 meses), para internet e internet_cable.
     */
    protected function seedDoubleSpeedRules(): void
    {
        $rules = [
            ['months' => 12, 'service' => 'internet'],
            ['months' => 12, 'service' => 'internet_cable'],
            ['months' => 24, 'service' => 'internet'],
            ['months' => 24, 'service' => 'internet_cable'],
        ];

        foreach ($rules as $rule) {
            Campaign::firstOrCreate(
                [
                    'type' => 'double_speed',
                    'category' => 'contract_rule',
                    'service' => $rule['service'],
                    'config' => ['months' => $rule['months'], 'enabled' => true],
                ],
                [
                    'name' => "Regla {$rule['months']} Meses " . ucfirst(str_replace('_', ' ', $rule['service'])),
                    'zone_id' => null,
                    'is_active' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                    'config' => ['months' => $rule['months'], 'enabled' => true],
                ]
            );
        }
    }

    /**
     * Reglas de meses gratis por plazo (12 y 24 meses), aplicable al servicio cable.
     */
    protected function seedDiscountMonthsRules(): void
    {
        $rules = [
            ['months' => 12, 'free' => 2, 'service' => 'cable'],
            ['months' => 24, 'free' => 4, 'service' => 'cable'],
        ];

        foreach ($rules as $rule) {
            Campaign::firstOrCreate(
                [
                    'type' => 'discount_months',
                    'category' => 'contract_rule',
                    'service' => $rule['service'],
                    'config' => ['min_pay' => $rule['months'], 'free' => $rule['free']],
                ],
                [
                    'name' => "Regla {$rule['months']} Meses",
                    'zone_id' => null,
                    'is_active' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                    'config' => ['min_pay' => $rule['months'], 'free' => $rule['free']],
                ]
            );
        }
    }

    /**
     * Regla de TV extra (aplica a todos los servicios): $6 instalación por TV, $1 mensual por TV.
     */
    protected function seedTvExtraRule(): void
    {
        Campaign::firstOrCreate(
            ['type' => 'tv_extra', 'category' => 'contract_rule', 'service' => 'all'],
            [
                'name' => 'TV Extra Global',
                'zone_id' => null,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
                'config' => ['install_fee' => 6, 'monthly_fee' => 1],
            ]
        );
    }
}
