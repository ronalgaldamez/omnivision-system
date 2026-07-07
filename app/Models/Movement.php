<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'unit_cost',
        'description',
        'user_id',
        'reference_type',
        'reference_id',
        'total_value',
        'branch_id',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function movementType()
    {
        return $this->belongsTo(MovementType::class, 'type', 'code');
    }

    public function getTypeDisplayAttribute(): array
    {
        $mt = $this->movementType;
        if ($mt) {
            return [
                'label' => $mt->label,
                'icon' => $mt->icon,
                'class' => $mt->color_class,
            ];
        }
        return ['label' => ucfirst($this->type), 'icon' => 'circle', 'class' => 'bg-gray-50 text-gray-700'];
    }
}