<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id', 'product_id', 'quantity', 'unit_cost',
        'pending_name', 'pending_unit', 'pending_category_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pendingCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'pending_category_id');
    }

    /**
     * Indica si el ítem es un producto propuesto (aún no existe en el catálogo).
     */
    public function isPending(): bool
    {
        return $this->product_id === null && ! empty($this->pending_name);
    }
}
