<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'service',
        'document_type',
        'document_number',
        'email',
        'latitude',
        'longitude',
        'nro_luz',
        'installation_address',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function workOrders()
    {
        return $this->hasManyThrough(WorkOrder::class, Ticket::class);
    }

    public function phones()
    {
        return $this->hasMany(ClientPhone::class);
    }
}