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
        'is_active',
        'branch_id',
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
            'is_active' => 'boolean',
        ];
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'technician_id');
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class, 'technician_id');
    }

    public function supervisedZones()
    {
        return $this->belongsToMany(Zone::class, 'supervisor_zone');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function activeBranchId(): ?int
    {
        if ($this->branch_id !== null) {
            return (int) $this->branch_id;
        }

        $sessionId = session('active_branch_id');

        return $sessionId ? (int) $sessionId : null;
    }

    public function getRolePrefixAttribute(): string
    {
        return $this->roles()->first()?->prefix ?? 'OT';
    }

    /**
     * Indica si el usuario tiene permisos personalizados
     * (asignados directamente, no heredados del rol).
     */
    public function hasPersonalizedPermissions(): bool
    {
        return $this->permissions()->count() > 0;
    }

    /**
     * Sistema híbrido de permisos:
     * - Si el usuario tiene permisos directos → solo esos valen (rol ignorado).
     * - Si no tiene permisos directos → hereda los del rol (comportamiento normal).
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasPersonalizedPermissions()) {
            return $this->hasDirectPermission($permission);
        }

        $permission = $this->filterPermission($permission, $guardName);

        return $this->hasDirectPermission($permission)
            || $this->hasPermissionViaRole($permission);
    }
}
