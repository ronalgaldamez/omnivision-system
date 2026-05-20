<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianReturn extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'type',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}