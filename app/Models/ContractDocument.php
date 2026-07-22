<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocument extends Model
{
    protected $fillable = [
        'contract_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'notes',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'dui_front'     => 'DUI (Frente)',
            'dui_back'      => 'DUI (Reverso)',
            'receipt'       => 'Recibo de luz',
            default         => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
