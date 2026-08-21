<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campaña promocional temporal (ej. Mes del Padre/Madre: instalación gratis).
 * type: free_installation, free_tv_month, double_speed, etc.
 * Se evalúa según vigencia (starts_at/ends_at) y opcionalmente por zona.
 */
class Campaign extends Model
{
    protected $fillable = [
        'name',
        'type',
        'category',
        'service',
        'zone_id',
        'config',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Indica si la campaña está vigente en este momento.
     */
    public function isCurrentlyActive(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        if (!$this->is_active) {
            return false;
        }
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Busca una campaña activa de un tipo, aplicable a la zona del cliente (o global).
     */
    public static function activeOfType(string $type, ?int $zoneId = null, ?Carbon $now = null): ?self
    {
        $now = $now ?? now();

        $query = static::where('type', $type)->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        if ($zoneId) {
            // Priorizar campaña específica de la zona, caer a global
            return $query->where(function ($q) use ($zoneId) {
                    $q->where('zone_id', $zoneId)->orWhereNull('zone_id');
                })
                ->orderByRaw('zone_id IS NOT NULL DESC')
                ->first();
        }

        return $query->first();
    }
}
