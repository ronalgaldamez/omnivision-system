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

    /**
     * Obtiene la tarifa de instalación por zona (cargo base + recargo por exceso)
     * según el tipo de servicio a instalar. Flexibilidad: el técnico puede ajustar el valor final.
     *
     * @return array{covered_meters: int, fee: float, excess_per_50m: float}
     */
    public function installFeeFor(Ticket $ticket): array
    {
        // Determinar el tipo de servicio del contrato (instalacion física)
        $serviceType = $this->resolveInstallServiceType($ticket);

        $zoneId = $ticket->contract?->zone_id ?? $ticket->zone_id;

        $rule = \App\Models\InstallFeeRule::effectiveFor($zoneId, $serviceType);

        if (!$rule) {
            return ['covered_meters' => 150, 'fee' => 0, 'excess_per_50m' => 0];
        }

        return [
            'covered_meters' => (int) $rule->covered_meters,
            'fee' => (float) $rule->fee,
            'excess_per_50m' => (float) $rule->excess_per_50m,
        ];
    }

    /**
     * Costo sugerido de instalación según la distancia y la tarifa de zona.
     * = cargo base + (exceso / 50m) * recargo, si supera los metros cubiertos.
     */
    public function suggestedInstallCost(float $dropDistance, array $fee): float
    {
        $meters = (float) $dropDistance;
        $covered = (int) ($fee['covered_meters'] ?? 150);
        $base = (float) ($fee['fee'] ?? 0);
        $excess = (float) ($fee['excess_per_50m'] ?? 0);

        if ($meters <= $covered) {
            return $base;
        }

        $extraBlocks = ceil(($meters - $covered) / 50);
        return $base + ($extraBlocks * $excess);
    }

    /**
     * Costo de instalación considerando campañas vigentes (instalación gratis).
     * Si hay campaña free_installation activa, el plan/zona es elegible (festive_eligible)
     * y la distancia NO supera los metros cubiertos -> $0. Si supera, aplica recargo.
     */
    public function suggestedInstallCostFor(Ticket $ticket, float $dropDistance): float
    {
        $fee = $this->installFeeFor($ticket);
        $covered = (int) ($fee['covered_meters'] ?? 150);

        if ($this->freeInstallationApplies($ticket, $dropDistance)) {
            // Dentro de los metros cubiertos con campaña activa -> gratis
            return (float) $dropDistance <= $covered ? 0.0 : $this->suggestedInstallCost($dropDistance, $fee);
        }

        return $this->suggestedInstallCost($dropDistance, $fee);
    }

    /**
     * Verifica si aplica instalación gratis por campaña vigente.
     * Aplica si hay campaña activa para la zona; la regla festive_eligible es una
     * capa OPCIONAL: si no está definida, la campaña aplica por defecto.
     */
    public function freeInstallationApplies(Ticket $ticket, float $dropDistance): bool
    {
        $zoneId = $ticket->contract?->zone_id ?? $ticket->zone_id;

        // La instalación gratis es una PROMOCIÓN temporal (mes festivo), no regla de contrato.
        $campaign = \App\Models\Campaign::where('type', 'free_installation')
            ->where('category', 'promotion')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) use ($zoneId) {
                $q->whereNull('zone_id');
                if ($zoneId) {
                    $q->orWhere('zone_id', $zoneId);
                }
            })
            ->first();

        if (!$campaign) {
            return false;
        }

        // Elegibilidad opcional: si hay regla festive_eligible y está desactivada, no aplica.
        $planId = $ticket->contract?->plan_id ?? $ticket->plan_id;
        if ($planId) {
            $eligible = \App\Models\PlanRule::getEffectiveRule($planId, $zoneId, 12, 'festive_eligible');
            if ($eligible !== null) {
                return is_array($eligible)
                    ? (bool) ($eligible['enabled'] ?? $eligible['value'] ?? false)
                    : (bool) $eligible;
            }
        }

        return true;
    }

    /**
     * Resuelve el tipo de servicio de instalación (internet/cable/combo).
     * Prioriza el service_contracted del contrato, luego el plan, luego el ticket.
     */
    protected function resolveInstallServiceType(Ticket $ticket): string
    {
        $contract = $ticket->contract;

        if ($contract && $contract->service_contracted) {
            $sc = $contract->service_contracted;
            if (in_array($sc, ['internet', 'cable', 'internet_cable'])) {
                return $sc;
            }
        }

        if ($contract?->plan?->service_type) {
            return $contract->plan->service_type;
        }

        return 'internet';
    }
}
