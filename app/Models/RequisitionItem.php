<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'requisition_id',
        'product_id',
        'quantity_requested',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_used' => 'decimal:2',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}