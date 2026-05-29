<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianInventory extends Model
{
    protected $table = 'technician_inventory';
    
    protected $fillable = [
        'technician_id',
        'product_id',
        'quantity_in_hand',
    ];

    protected $casts = [
        'quantity_in_hand' => 'decimal:2',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}