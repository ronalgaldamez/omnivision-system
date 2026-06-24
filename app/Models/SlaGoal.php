<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'priority',
        'service_type_id',
        'minutes',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function scopeForTicket($query, $priority, $serviceTypeId = null)
    {
        return $query->where('priority', $priority)
            ->where(function ($q) use ($serviceTypeId) {
                $q->where('service_type_id', $serviceTypeId)
                  ->orWhereNull('service_type_id');
            })
            ->where('is_active', true)
            ->orderBy('service_type_id', 'desc') // Preferir el más específico
            ->orderBy('minutes', 'asc')
            ->limit(1);
    }
}
