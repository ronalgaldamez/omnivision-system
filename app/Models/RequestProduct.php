<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_request_id',
        'product_id',
        'quantity_requested',
        'quantity_delivered',
        'quantity_returned'
    ];

    public function request()
    {
        return $this->belongsTo(TechnicianRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}