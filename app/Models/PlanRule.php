<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanRule extends Model
{
    protected $fillable = ['plan_id', 'zone_id', 'term_months', 'rule_key', 'rule_value', 'condition', 'is_active'];

    protected $casts = [
        'rule_value' => 'array',
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Busca regla subiendo por el arbol de zonas (herencia)
     */
    public static function getEffectiveRule(int $planId, ?int $zoneId, int $termMonths, string $ruleKey, mixed $default = null): mixed
    {
        // Buscar regla global primero (sin zona)
        $globalRule = static::where('plan_id', $planId)
            ->whereNull('zone_id')
            ->where('term_months', $termMonths)
            ->where('rule_key', $ruleKey)
            ->where('is_active', true)
            ->first();

        if ($globalRule) return $globalRule->rule_value;

        if (!$zoneId) return $default;

        $zone = Zone::find($zoneId);
        if (!$zone) return $default;

        // Buscar en la zona actual
        $rule = static::where('plan_id', $planId)
            ->where('zone_id', $zone->id)
            ->where('term_months', $termMonths)
            ->where('rule_key', $ruleKey)
            ->where('is_active', true)
            ->first();

        if ($rule) return $rule->rule_value;

        // Subir al padre
        if ($zone->parent_id) {
            return static::getEffectiveRule($planId, $zone->parent_id, $termMonths, $ruleKey, $default);
        }

        return $default;
    }

    /**
     * Busca todas las reglas activas para un plan en una zona dada
     */
    public static function getEffectiveRules(int $planId, ?int $zoneId, int $termMonths): array
    {
        $rules = [];

        // Siempre incluir reglas globales (sin zona específica)
        $globalRules = static::where('plan_id', $planId)
            ->whereNull('zone_id')
            ->where('term_months', $termMonths)
            ->where('is_active', true)
            ->get();

        foreach ($globalRules as $r) {
            $rules[$r->rule_key] = $r->rule_value;
        }

        // Reglas específicas de la zona y sus ancestros
        $zone = Zone::find($zoneId);
        while ($zone) {
            $zoneRules = static::where('plan_id', $planId)
                ->where('zone_id', $zone->id)
                ->where('term_months', $termMonths)
                ->where('is_active', true)
                ->get();

            foreach ($zoneRules as $r) {
                if (!isset($rules[$r->rule_key])) {
                    $rules[$r->rule_key] = $r->rule_value;
                }
            }
            $zone = $zone->parent;
        }

        return $rules;
    }
}
