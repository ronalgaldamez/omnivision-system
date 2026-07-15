<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'encargado_id', 'vehicle_id', 'zone_id', 'is_active', 'assigned_at', 'ended_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'date',
        'ended_at' => 'date',
    ];

    public function encargado()
    {
        return $this->belongsTo(User::class, 'encargado_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehiculo::class, 'vehicle_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
