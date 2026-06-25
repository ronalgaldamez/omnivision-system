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
        'started_at',
        'sla_started_at',
        'accumulated_seconds',
        'created_by',
        'wifi_name',
        'wifi_password',
        'profile_name',
        'profile_password',
        'mac',
        'pon',
        'mufa',
        'installation_date',
        'assigned_at',
        'assigned_by',
        'requires_noc',
        'zone_id',
        'plan_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'datetime',
        'started_at' => 'datetime',
        'sla_started_at' => 'datetime',
        'accumulated_seconds' => 'integer',
        'installation_date' => 'date',
        'assigned_at' => 'datetime',
        'requires_noc' => 'boolean',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function pauses()
    {
        return $this->hasMany(WorkOrderPause::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}