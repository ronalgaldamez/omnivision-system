<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'razon_social', 'nombre_comercial', 'tipo', 'nit', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function displayName(): string
    {
        return $this->nombre_comercial ?: $this->razon_social;
    }

    public function tipoLabel(): string
    {
        return $this->tipo === 'sociedad' ? 'Sociedad' : 'Persona Natural';
    }
}
