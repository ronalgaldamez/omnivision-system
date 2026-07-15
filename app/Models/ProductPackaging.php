<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPackaging extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id', 'packaging_type_id', 'name', 'quantity_in_base_unit', 'is_default_for_purchase',
    ];

    protected $casts = [
        'quantity_in_base_unit' => 'decimal:4',
        'is_default_for_purchase' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $packaging) {
            if ($packaging->is_default_for_purchase) {
                static::where('product_id', $packaging->product_id)
                    ->where('id', '!=', $packaging->id)
                    ->update(['is_default_for_purchase' => false]);
            }
        });

        static::deleted(function (self $packaging) {
            $remaining = static::where('product_id', $packaging->product_id)->first();
            if ($remaining && ! $remaining->is_default_for_purchase) {
                $remaining->is_default_for_purchase = true;
                $remaining->save();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function packagingType()
    {
        return $this->belongsTo(PackagingType::class);
    }
}
