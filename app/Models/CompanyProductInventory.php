<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProductInventory extends Model
{
    use HasFactory;

    protected $table = 'company_product_inventories';

    protected $fillable = [
        'company_id', 'product_id', 'quantity', 'average_cost', 'total_value',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'average_cost' => 'decimal:4',
        'total_value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Costo promedio ponderado del producto para una empresa.
     * Si no existe registro, devuelve null (sin costo previo).
     */
    public static function averageCostFor(int $companyId, int $productId): ?float
    {
        $record = static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->first();

        return $record ? (float) $record->average_cost : null;
    }
}
