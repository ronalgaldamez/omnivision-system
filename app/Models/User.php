<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'branch_id',
        'tech_role',
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

    public function technicianReturns()
    {
        return $this->hasMany(TechnicianReturn::class, 'user_id');
    }

    public function supervisedZones()
    {
        return $this->belongsToMany(Zone::class, 'supervisor_zone');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeEncargados($q)
    {
        return $q->where('tech_role', 'encargado');
    }

    public function scopeAuxiliares($q)
    {
        return $q->where('tech_role', 'auxiliar');
    }

    public function activeBranchId(): ?int
    {
        if ($this->branch_id !== null) {
            return (int) $this->branch_id;
        }

        $sessionId = session('active_branch_id');

        return $sessionId ? (int) $sessionId : null;
    }

    /**
     * Sucursales que el usuario puede seleccionar en el switcher.
     * - Sin sucursal asignada o con access_all_branches → todas las activas.
     * - Con sucursal asignada → las de su misma empresa (la suya + las hermanas).
     */
    public function allowedBranchIds(): array
    {
        $isGlobal = false;

        if ($this->branch_id === null) {
            $isGlobal = true;
        } elseif (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $isGlobal = \App\Models\Permission::where('name', 'access_all_branches')->exists()
                && $this->hasPermissionTo('access_all_branches');
        }

        if ($isGlobal) {
            return Branch::where('is_active', true)->pluck('id')->map(fn($id) => (int) $id)->all();
        }

        $myBranch = Branch::with('company')->find($this->branch_id);

        if (! $myBranch || ! $myBranch->company_id) {
            return $this->branch_id !== null ? [(int) $this->branch_id] : [];
        }

        return Branch::where('is_active', true)
            ->where('company_id', $myBranch->company_id)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
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
            if ($this->hasDirectPermission($permission)) {
                return true;
            }
        }

        $permission = $this->filterPermission($permission, $guardName);

        return $this->hasDirectPermission($permission)
            || $this->hasPermissionViaRole($permission);
    }
}
