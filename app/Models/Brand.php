<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function productModels()
    {
        return $this->hasMany(ProductModel::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}