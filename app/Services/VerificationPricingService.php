<?php

namespace App\Services;

use App\Models\PlanRule;
use App\Models\ServiceRule;
use App\Models\ServiceType;
use App\Models\Ticket;

/**
 * Centraliza el cálculo de precio de verificación de instalación.
 * Único punto de verdad para free_distance / price_per_meter.
 */
class VerificationPricingService
{
    /**
     * Reglas de factibilidad para un ticket: free_distance y price_per_meter.
     * Prioriza plan_rules (plan + zona) y cae a service_rules (global).
     *
     * @return array{free_distance: int, price_per_meter: float}
     */
    public function rulesFor(Ticket $ticket): array
    {
        $contract = $ticket->contract;

        if ($contract && $contract->plan_id && $contract->zone_id) {
            $freeDist = PlanRule::getEffectiveRule($contract->plan_id, $contract->zone_id, $contract->term_months ?? 12, 'free_distance');
            $pricePer = PlanRule::getEffectiveRule($contract->plan_id, $contract->zone_id, $contract->term_months ?? 12, 'price_per_meter');

            if ($freeDist || $pricePer) {
                return [
                    'free_distance' => (int) ($freeDist['meters'] ?? 150),
                    'price_per_meter' => (float) ($pricePer['amount'] ?? 5),
                ];
            }
        }

        $st = ServiceType::where('name', 'verificacion_instalacion')->first();
        if (!$st) {
            return ['free_distance' => 150, 'price_per_meter' => 5];
        }

        $freeDist = ServiceRule::getRule($st->id, 'free_distance', ['meters' => 150]);
        $pricePer = ServiceRule::getRule($st->id, 'price_per_meter', ['amount' => 5]);

        return [
            'free_distance' => (int) ($freeDist['meters'] ?? 150),
            'price_per_meter' => (float) ($pricePer['amount'] ?? 5),
        ];
    }

    /**
     * Precio sugerido según la distancia capturada y las reglas.
     */
    public function suggestedPrice(float $dropDistance, array $rules): float
    {
        $meters = (float) $dropDistance;
        $free = (int) ($rules['free_distance'] ?? 150);
        $price = (float) ($rules['price_per_meter'] ?? 5);

        if ($meters <= $free) {
            return 0.0;
        }

        return ($meters - $free) * $price;
    }
}
