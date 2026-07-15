<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id', 'code', 'label', 'description', 'type', 'warehouse', 'is_active', 'is_full',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_full' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function children()
    {
        return $this->hasMany(Shelf::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_shelf')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
