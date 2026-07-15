<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'zone_plan_price_id', 'plan_id', 'zone_id',
        'old_price', 'new_price', 'reason', 'user_id',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function zonePlanPrice()
    {
        return $this->belongsTo(ZonePlanPrice::class);
    }
}
