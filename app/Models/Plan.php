<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'description', 'service_type',
        'base_price', 'speed', 'channels', 'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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
}
