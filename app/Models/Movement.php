<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'unit_cost',
        'description',
        'user_id',
        'reference_type',
        'reference_id',
        'unit_cost',
        'total_value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // en app/Models/Movement.php
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'reference_id', 'id')
            ->where('reference_type', 'purchase');
    }
}