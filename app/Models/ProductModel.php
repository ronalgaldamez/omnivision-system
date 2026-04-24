<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'product_models';
    protected $fillable = ['brand_id', 'name', 'description', 'category_id'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        // Especificamos la clave foránea 'model_id' en la tabla products
        return $this->hasMany(Product::class, 'model_id');
    }
}