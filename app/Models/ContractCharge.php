<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cobro/factura asociado a un contrato. Base del módulo de facturación.
 * - extra_tv: cargo único de instalación ($6 por TV).
 * - mensualidad extra recurrente (+$1 mensual por TV).
 */
class ContractCharge extends Model
{
    protected $fillable = [
        'contract_id',
        'client_id',
        'type',
        'charge_type',
        'description',
        'amount',
        'base_amount',
        'is_recurring',
        'recurring_period',
        'quantity',
        'days',
        'applied_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'applied_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
