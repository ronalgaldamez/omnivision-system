<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'description',
        'service_type',
        'priority',
        'origin',
        'requires_noc',
        'status',
        'created_by',
        'resolved_by',
        'resolved_at',
        'ticket_code',
        // Tiempos L1 — Atención al Cliente
        'started_at',
        'l1_ended_at',
        'escalated_at',
        // Tiempos L2 — NOC
        'l2_started_at',
        'l2_ended_at',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'l1_ended_at'   => 'datetime',
        'escalated_at'  => 'datetime',
        'l2_started_at' => 'datetime',
        'l2_ended_at'   => 'datetime',
        'resolved_at'   => 'datetime',
        'requires_noc'  => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function workOrder()
    {
        return $this->hasOne(WorkOrder::class);
    }
}