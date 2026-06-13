<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'branch_id', 'parent_id', 'name', 'level',
        'has_internet', 'has_cable', 'is_active',
    ];

    protected $casts = [
        'has_internet' => 'boolean',
        'has_cable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function prices()
    {
        return $this->hasMany(ZonePlanPrice::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getEffectivePriceForPlan(Plan $plan): ?float
    {
        $price = $this->prices()->where('plan_id', $plan->id)->first();
        if ($price && $price->price !== null) {
            return (float) $price->price;
        }
        if ($this->parent) {
            return $this->parent->getEffectivePriceForPlan($plan);
        }
        return (float) $plan->base_price;
    }
}
