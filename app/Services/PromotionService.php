<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Plan;

/**
 * Centraliza el cálculo de promociones desde CAMPAÑAS (globales o por zona),
 * sin depender del plan. Evita configurar la promo en cada plan.
 * - Meses gratis: campaña type=discount_months con config{pay,total}.
 * - Doble velocidad: campaña type=double_speed con config{enabled:true}.
 */
class PromotionService
{
    /**
     * Meses gratis según la campaña discount_months vigente para la zona.
     * Respeta el "service" elegido por el usuario al crear la campaña.
     * Totalmente flexible: config {min_pay, free} -> si termMonths>=min_pay, regala `free` meses.
     */
    public function freeMonths(?int $planId, ?int $zoneId, int $termMonths): array
    {
        $noApply = ['applies' => false, 'free' => 0, 'pay' => 0, 'total' => 0];
        $serviceType = $this->planServiceType($planId);

        $campaigns = $this->activeCampaigns('discount_months', $zoneId, $serviceType);
        if ($campaigns->isEmpty()) {
            return $noApply;
        }

        // Elegir la campaña con min_pay == termMonths, o la mayor min_pay que no exceda
        $best = null;
        $bestMinPay = 0;
        foreach ($campaigns as $campaign) {
            $cfg = $campaign->config ?? [];
            $minPay = (int) ($cfg['min_pay'] ?? 0);
            $free = (int) ($cfg['free'] ?? 0);

            if ($free <= 0 || $termMonths < $minPay) {
                continue;
            }

            if ($best === null
                || $minPay === $termMonths
                || ($bestMinPay < $minPay && $minPay !== $bestMinPay)) {
                $best = ['free' => $free, 'min_pay' => $minPay];
                $bestMinPay = $minPay;
            }
        }

        if (!$best) {
            return $noApply;
        }

        return [
            'applies' => true,
            'free' => $best['free'],
            'pay' => $best['min_pay'],
            'total' => $best['min_pay'] + $best['free'],
        ];
    }

    /**
     * Indica si aplica doble velocidad por campaña vigente para la zona,
     * respetando el "service" elegido por el usuario al crear la campaña.
     */
    public function appliesDoubleSpeed(?int $planId, ?int $zoneId, int $termMonths): bool
    {
        $serviceType = $this->planServiceType($planId);
        $campaigns = $this->activeCampaigns('double_speed', $zoneId, $serviceType);
        if ($campaigns->isEmpty()) {
            return false;
        }

        $campaign = $campaigns->first();
        $cfg = $campaign->config ?? [];
        return (bool) ($cfg['enabled'] ?? true);
    }

    /**
     * Velocidad efectiva del plan (duplicada si aplica doble velocidad).
     */
    public function effectiveSpeed(?int $planId, ?int $zoneId, int $termMonths): array
    {
        if (!$planId) {
            return ['base' => 0, 'effective' => 0, 'original_text' => '', 'doubled' => false];
        }

        $plan = Plan::find($planId);
        $baseSpeed = $plan?->speed;
        $baseMbps = $this->parseMbps($baseSpeed);
        $doubled = $this->appliesDoubleSpeed($planId, $zoneId, $termMonths) && $baseMbps > 0;

        $effectiveMbps = $doubled ? $baseMbps * 2 : $baseMbps;

        return [
            'base' => $baseMbps,
            'effective' => $effectiveMbps,
            'original_text' => (string) $baseSpeed,
            'doubled' => $doubled,
        ];
    }

    /**
     * Campañas activas de un tipo aplicables a la zona y al servicio del plan.
     * Filtra por el "service" que eligió el usuario (all = aplica a todo) y por categoría.
     * Las reglas de contrato (meses gratis / doble velocidad) usan category=contract_rule.
     */
    private function activeCampaigns(string $type, ?int $zoneId, ?string $serviceType = null, ?string $category = 'contract_rule')
    {
        $query = Campaign::where('type', $type)->where('is_active', true)
            ->where('category', $category)
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
            });

        // Filtrar por servicio si la campaña especifica uno
        if ($serviceType) {
            $query->where(function ($q) use ($serviceType) {
                $q->where('service', $serviceType)->orWhere('service', 'all');
            });
        }

        return $query->get();
    }

    /**
     * Service_type del plan (internet, cable, internet_cable) o null si no hay plan.
     */
    private function planServiceType(?int $planId): ?string
    {
        if (!$planId) {
            return null;
        }
        return Plan::find($planId)?->service_type;
    }

    /**
     * Extrae el número de Mbps de un texto como "100 Mbps" o "50".
     */
    private function parseMbps($speed): int
    {
        if (!$speed) {
            return 0;
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', (string) $speed, $m)) {
            return (int) $m[1];
        }
        return 0;
    }
}
