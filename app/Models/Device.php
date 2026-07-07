<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'product_id', 'purchase_id', 'mac_address', 'pon_sn',
        'default_ip', 'default_username', 'default_password',
        'default_ssid1', 'default_lan_key',
        'status', 'technician_id', 'assigned_at',
        'work_order_id', 'installed_at', 'branch_id',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'installed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function deviceStatus()
    {
        return $this->belongsTo(DeviceStatus::class, 'status', 'code');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
