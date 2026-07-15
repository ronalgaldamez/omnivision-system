<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePlanPrice extends Model
{
    use HasFactory;
    protected $fillable = ['zone_id', 'plan_id', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
