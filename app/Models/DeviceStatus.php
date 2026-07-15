<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceStatus extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'color_class', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
