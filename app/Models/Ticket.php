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
        'requires_contract',
        'create_ot',
        'status',
        'created_by',
        'resolved_by',
        'resolved_at',
        'cancelled_at',
        'cancellation_reason',
        'ticket_code',
        'zone_id',
        'plan_id',
        // Tiempos L1 — Atención al Cliente
        'started_at',
        'l1_ended_at',
        'escalated_at',
        // Tiempos L2 — NOC
        'l2_started_at',
        'l2_ended_at',
        // Tiempos Contratos
        'contracts_escalated_at',
        'contracts_started_at',
        'contracts_ended_at',
        // SLA
        'sla_goal_id',
        'sla_deadline_at',
        'sla_met',
        'sla_evaluated_at',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'l1_ended_at'   => 'datetime',
        'escalated_at'  => 'datetime',
        'l2_started_at'         => 'datetime',
        'l2_ended_at'           => 'datetime',
        'contracts_escalated_at' => 'datetime',
        'contracts_started_at'  => 'datetime',
        'contracts_ended_at'    => 'datetime',
        'resolved_at'   => 'datetime',
        'cancelled_at'  => 'datetime',
        'requires_noc'          => 'boolean',
        'requires_contract'     => 'boolean',
        'create_ot'             => 'boolean',
        'sla_deadline_at'   => 'datetime',
        'sla_met'           => 'boolean',
        'sla_evaluated_at'  => 'datetime',
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

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function slaGoal()
    {
        return $this->belongsTo(SlaGoal::class);
    }
}