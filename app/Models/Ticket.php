<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'description', 'service_type', 'requires_noc',
        'status', 'created_by', 'resolved_by', 'resolved_at',
        'ticket_code',  // ← añadido
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