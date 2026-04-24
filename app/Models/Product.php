<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'current_stock',
        'stock_min',
        'stock_max',
        'unit_of_measure',
        'measure_value',
        'brand_id',
        'model_id',
        'category_id',
        'average_cost',
        'total_value',
        'is_obsolete',
        'is_floating'
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = $product->generateUniqueSku();
            }
        });
    }

    public function recalculateAverage($newQuantity, $newCost)
    {
        $currentQuantity = $this->current_stock;
        $currentValue = $this->total_value ?? 0;
        $newValue = $newQuantity * $newCost;
        $totalQuantity = $currentQuantity + $newQuantity;
        if ($totalQuantity == 0) {
            $this->average_cost = 0;
            $this->total_value = 0;
        } else {
            $this->average_cost = round(($currentValue + $newValue) / $totalQuantity, 4);
            $this->total_value = round($totalQuantity * $this->average_cost, 2);
        }
        $this->current_stock = $totalQuantity;
        $this->save();
    }

    public function generateUniqueSku()
    {
        $prefix = 'PROD';
        $lastProduct = self::orderBy('id', 'desc')->first();
        if ($lastProduct && preg_match('/\d+$/', $lastProduct->sku, $matches)) {
            $lastNumber = (int) $matches[0];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        return $prefix . '-' . $formattedNumber;
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    // ========== CORRECCIÓN APLICADA AQUÍ ==========
    public function updateStock(int $quantity, string $type): void
    {
        if (in_array($type, ['entry', 'technician_return'])) {
            $this->increment('current_stock', $quantity);
        } elseif (in_array($type, ['exit', 'technician_out', 'damage', 'return_to_supplier'])) {
            $this->decrement('current_stock', $quantity);
        }
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}