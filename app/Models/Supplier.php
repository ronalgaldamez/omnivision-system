<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_name',
        'phone',
        'email',
        'address',
        'nrc',
        'nit',
        'bank_accounts'
    ];

    protected $casts = [
        'bank_accounts' => 'array',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // Obtener movimientos de productos comprados a este proveedor
    public function movements()
    {
        // Movimientos de tipo 'entry' que tienen referencia a compras de este proveedor
        return Movement::whereHas('purchase', function ($q) {
            $q->where('supplier_id', $this->id);
        });
    }
}