<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use App\Models\Role;
use App\Enums\PermissionEnum;

class RoleForm extends Component
{
    public $roleId;
    public $name;
    public $prefix = '';
    public $selectedPermissions = [];
    public $activeTab = '';

    public function mount($id = null)
    {
        if ($id) {
            $role = Role::findOrFail($id);
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->prefix = $role->prefix ?? '';
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }

        if (empty($this->activeTab)) {
            $this->activeTab = '';
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            'prefix' => 'nullable|string|max:10',
        ]);

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->name = $this->name;
            $role->prefix = $this->prefix;
            $role->save();
        } else {
            $role = Role::create([
                'name' => $this->name,
                'prefix' => $this->prefix,
            ]);
        }

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message', 'Rol guardado correctamente.');
    }

    public function getPermissionsGroupedProperty(): array
    {
        return [
            'Inventario'            => $this->categorize(PermissionEnum::inventory()),
            'Proveedores'           => $this->categorize(PermissionEnum::suppliers()),
            'Órdenes de Trabajo'    => $this->categorize(PermissionEnum::workOrders()),
            'Técnicos'              => $this->categorize(PermissionEnum::technicians()),
            'Reportes'              => $this->categorize(PermissionEnum::reports()),
            'Soporte / Tickets'     => $this->categorize(PermissionEnum::support()),
            'Panel NOC'             => $this->categorize(PermissionEnum::noc()),
            'Clientes'              => $this->categorize(PermissionEnum::clients()),
            'Administración'        => $this->categorize(PermissionEnum::admin()),
            'SLA'                   => $this->categorize(PermissionEnum::sla()),
            'Campo / Móvil'         => $this->categorize(PermissionEnum::field()),
            'Supervisor de Zona'    => $this->categorize(PermissionEnum::supervisor()),
        ];
    }

    private function categorize(array $enumCases): array
    {
        $values = array_map(fn($c) => $c->value, $enumCases);

        return [
            'gates'   => array_values(array_filter($values, fn($p) => str_starts_with($p, 'access_'))),
            'menus'   => array_values(array_filter($values, fn($p) => str_ends_with($p, '_menu'))),
            'actions' => array_values(array_filter($values, fn($p) => !str_starts_with($p, 'access_') && !str_ends_with($p, '_menu'))),
        ];
    }

    public function render()
    {
        $grouped = $this->permissionsGrouped;
        $tabModules = array_keys($grouped);

        return view('livewire.admin.roles.role-form', compact('grouped', 'tabModules'));
    }
}
