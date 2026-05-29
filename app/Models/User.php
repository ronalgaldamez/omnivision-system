<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación con órdenes de trabajo
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'technician_id');
    }

    // Relación con requisiciones
    public function requisitions()
    {
        return $this->hasMany(Requisition::class, 'technician_id');
    }
   
    public function getRolePrefixAttribute(): string
    {
        return $this->roles()->first()?->prefix ?? 'OT';
    }
}