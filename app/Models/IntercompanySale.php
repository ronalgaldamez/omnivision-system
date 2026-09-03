<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntercompanySale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'seller_branch_id', 'buyer_branch_id',
        'subtotal', 'iva_amount', 'total', 'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sellerBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'seller_branch_id');
    }

    public function buyerBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'buyer_branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(IntercompanySaleItem::class, 'sale_id');
    }

    public static function generateCode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;
        return 'VEN-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
