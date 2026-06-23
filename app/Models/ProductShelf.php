<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductShelf extends Pivot
{
    protected $table = 'product_shelf';

    protected $fillable = [
        'product_id', 'shelf_id', 'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }
}
