<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRule extends Model
{
    protected $fillable = ['service_type_id', 'rule_key', 'rule_value', 'is_active'];

    protected $casts = [
        'rule_value' => 'array',
        'is_active' => 'boolean',
    ];

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public static function getRule(int $serviceTypeId, string $key, mixed $default = null): mixed
    {
        return static::where('service_type_id', $serviceTypeId)
            ->where('rule_key', $key)
            ->where('is_active', true)
            ->value('rule_value') ?? $default;
    }
}
