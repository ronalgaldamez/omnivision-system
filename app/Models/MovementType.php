<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementType extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'label', 'icon', 'color_class', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
