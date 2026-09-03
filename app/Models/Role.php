<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'prefix',
    ];

    /**
     * Nombre legible del rol según el idioma activo.
     * El código interno (name) no cambia; solo cambia la etiqueta mostrada.
     */
    public function label(): string
    {
        $translated = __('roles.role_' . $this->name);

        // Si la traducción no existe, devuelve el nombre original con guiones bajos como espacios.
        if ($translated === 'roles.role_' . $this->name) {
            return ucfirst(str_replace('_', ' ', $this->name));
        }

        return $translated;
    }
}
