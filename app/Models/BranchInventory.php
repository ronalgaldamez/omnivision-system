<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchInventory extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id', 'product_id', 'allocated_quantity',
    ];

    protected $casts = [
        'allocated_quantity' => 'decimal:4',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
