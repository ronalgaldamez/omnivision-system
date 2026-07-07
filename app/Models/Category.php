<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'requires_device_registration'];

    protected $casts = [
        'requires_device_registration' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}