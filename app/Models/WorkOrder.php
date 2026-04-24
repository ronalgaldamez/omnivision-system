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
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'datetime',
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

    public function technicianRequests()
    {
        return $this->hasMany(TechnicianRequest::class);
    }
}