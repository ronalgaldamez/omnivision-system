<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPackaging extends Model
{
    protected $fillable = [
        'product_id', 'packaging_type_id', 'name', 'quantity_in_base_unit', 'is_default_for_purchase',
    ];

    protected $casts = [
        'quantity_in_base_unit' => 'decimal:4',
        'is_default_for_purchase' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function packagingType()
    {
        return $this->belongsTo(PackagingType::class);
    }
}
