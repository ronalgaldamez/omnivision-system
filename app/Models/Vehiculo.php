<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'placa', 'marca', 'modelo', 'anio', 'color', 'tipo', 'estado', 'notas',
    ];

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'vehicle_id');
    }

    public function encargadoActual()
    {
        return $this->hasOne(Asignacion::class, 'vehicle_id')->where('is_active', true);
    }
}
