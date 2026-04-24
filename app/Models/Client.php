<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'service'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function workOrders()
    {
        return $this->hasManyThrough(WorkOrder::class, Ticket::class);
    }
}