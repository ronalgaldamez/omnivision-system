<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'symbol', 'is_whole', 'is_active',
    ];

    protected $casts = [
        'is_whole' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_of_measure', 'code');
    }
}
