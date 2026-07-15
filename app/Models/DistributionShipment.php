<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionShipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'branch_id', 'status',
        'created_by', 'confirmed_by',
        'in_transit_at', 'delivered_at', 'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'in_transit_at' => 'datetime',
        'delivered_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items()
    {
        return $this->hasMany(DistributionShipmentItem::class, 'shipment_id');
    }

    public static function generateCode(): string
    {
        $last = self::orderBy('id', 'desc')->first();
        $next = $last ? ((int) substr($last->code, 4)) + 1 : 1;
        return 'ENV-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
