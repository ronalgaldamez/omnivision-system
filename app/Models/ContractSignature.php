<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSignature extends Model
{
    protected $fillable = [
        'contract_id',
        'type',
        'signature_data',
        'ip_address',
        'user_agent',
        'signature_token',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'client'    => 'Firma del Cliente',
            'witness'   => 'Firma del Testigo',
            'sales_rep' => 'Firma del Agente de Ventas',
            default     => ucfirst($this->type),
        };
    }
}
