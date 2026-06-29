<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'contact_name', 'phones', 'email',
        'address', 'nrc', 'nit', 'bank_accounts',
    ];

    protected $casts = [
        'phones' => 'array',
        'bank_accounts' => 'array',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function movements()
    {
        return $this->hasManyThrough(
            Movement::class, Purchase::class,
            'supplier_id', 'reference_id', 'id', 'id'
        )->where('movements.reference_type', 'purchase');
    }
}
