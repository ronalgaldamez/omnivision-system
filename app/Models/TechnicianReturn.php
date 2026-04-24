<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_request_id',
        'work_order_id',
        'type',
        'product_id',
        'quantity',
        'notes',
        'user_id'
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function request()
    {
        return $this->belongsTo(TechnicianRequest::class, 'technician_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}