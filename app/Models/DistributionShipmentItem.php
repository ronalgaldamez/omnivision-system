<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionShipmentItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'shipment_id', 'product_id', 'product_name', 'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function shipment()
    {
        return $this->belongsTo(DistributionShipment::class, 'shipment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
