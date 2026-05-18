<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'client_id',
        'ticket_id',
        'latitude',
        'longitude',
        'status',
        'scheduled_date',
        'completed_date',
        'notes',
        'service_type',
        'description',
        'code',
        'started_at',       // ← añadido
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'datetime',
        'started_at' => 'datetime',   // ← añadido
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function requisitions()
    {
        return $this->belongsToMany(Requisition::class, 'requisition_work_order');
    }
}