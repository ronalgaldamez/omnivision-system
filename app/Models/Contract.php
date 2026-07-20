<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'client_id',
        'plan_id',
        'zone_id',
        'service_type',
        'price',
        'status',
        'installation_address',
        'latitude',
        'longitude',
        'contract_date',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'contract_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function serviceTypeName(): string
    {
        return str_replace('_', ' ', $this->service_type);
    }
}
