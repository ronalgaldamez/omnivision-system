<?php

namespace App\Services;

use App\Models\Campaign;

/**
 * Obtiene los valores configurables del TV extra por zona.
 * Se leen de las reglas de contrato (category=contract_rule, type=tv_extra).
 * Default: $6 instalación por TV, +$1 mensual por TV.
 */
class TvExtraFees
{
    /**
     * @return array{install_fee: float, monthly_fee: float}
     */
    public static function forZone(?int $zoneId = null): array
    {
        $campaign = Campaign::where('type', 'tv_extra')
            ->where('category', 'contract_rule')
            ->where('is_active', true)
            ->where(function ($q) use ($zoneId) {
                $q->whereNull('zone_id');
                if ($zoneId) {
                    $q->orWhere('zone_id', $zoneId);
                }
            })
            ->orderByRaw('zone_id IS NOT NULL DESC')
            ->first();

        if (!$campaign) {
            return ['install_fee' => 6.0, 'monthly_fee' => 1.0];
        }

        $cfg = $campaign->config ?? [];
        return [
            'install_fee' => (float) ($cfg['install_fee'] ?? 6),
            'monthly_fee' => (float) ($cfg['monthly_fee'] ?? 1),
        ];
    }
}
