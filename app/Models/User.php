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

    /**
     * Sucursales asignadas manualmente al usuario (visibilidad ampliada).
     */
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches');
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
        $sessionId = session('active_branch_id');
        if ($sessionId) {
            return (int) $sessionId;
        }

        return $this->branch_id !== null ? (int) $this->branch_id : null;
    }

    /**
     * Sucursales que el usuario puede seleccionar en el switcher.
     * Prioridad:
     * 1. Si tiene sucursales asignadas manualmente (user_branches) → esas.
     * 2. Si tiene acceso global (sin sucursal, access_all_branches, o rol warehouse) → todas las activas.
     * 3. Si tiene sucursal asignada → las de su misma empresa (la suya + las hermanas).
     */
    public function allowedBranchIds(): array
    {
        // Camino 1: lista manual de sucursales asignadas al usuario
        $manual = $this->branches()->pluck('branch_id')->map(fn($id) => (int) $id)->all();
        if (! empty($manual)) {
            return Branch::whereIn('id', $manual)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        $isGlobal = false;

        if ($this->branch_id === null) {
            $isGlobal = true;
        } elseif (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $isGlobal = \App\Models\Permission::where('name', 'access_all_branches')->exists()
                && $this->hasPermissionTo('access_all_branches');
        }

        // Camino 2: el bodeguero (warehouse) administra todas las sucursales
        if (! $isGlobal && $this->hasRole('warehouse')) {
            $isGlobal = true;
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
