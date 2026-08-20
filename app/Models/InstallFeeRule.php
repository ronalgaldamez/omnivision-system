<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tarifa de instalación por zona.
 * Cada zona define su propio cargo base y recargo por exceso, independiente del plan.
 * - covered_meters: metros que cubre el cargo base (ej. 150).
 * - fee: costo base (ej. 25).
 * - excess_per_50m: recargo por cada 50m adicionales que exceda covered_meters.
 */
class InstallFeeRule extends Model
{
    protected $fillable = [
        'zone_id',
        'service_type',
        'covered_meters',
        'fee',
        'excess_per_50m',
        'is_active',
    ];

    protected $casts = [
        'covered_meters' => 'integer',
        'fee' => 'decimal:2',
        'excess_per_50m' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Obtiene la tarifa de instalación de una zona (con herencia de la zona padre si no hay).
     */
    public static function effectiveFor(?int $zoneId, string $serviceType, ?int $fallbackZoneId = null): ?self
    {
        $rule = static::where('service_type', $serviceType)->where('is_active', true)
            ->orderByRaw('zone_id IS NOT NULL DESC') // priorizar regla específica de zona
            ->first();

        if ($zoneId) {
            $zone = Zone::find($zoneId);
            $current = $zone;

            while ($current) {
                $found = static::where('zone_id', $current->id)
                    ->where('service_type', $serviceType)
                    ->where('is_active', true)
                    ->first();
                if ($found) {
                    return $found;
                }
                $current = $current->parent;
            }
        }

        // Fallback: regla de la zona por defecto del cliente o global
        if ($fallbackZoneId) {
            $fallback = static::where('zone_id', $fallbackZoneId)
                ->where('service_type', $serviceType)
                ->where('is_active', true)
                ->first();
            if ($fallback) {
                return $fallback;
            }
        }

        return static::whereNull('zone_id')
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->first();
    }
}
