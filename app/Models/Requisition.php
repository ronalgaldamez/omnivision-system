<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    protected $fillable = [
        'technician_id',
        'status',
        'week_start_date',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'week_start_date' => 'date',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'requisition_work_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }
}