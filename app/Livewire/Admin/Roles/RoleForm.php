<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleForm extends Component
{
    public $roleId;
    public $name;
    public $selectedPermissions = [];

    public function mount($id = null)
    {
        if ($id) {
            $role = Role::findOrFail($id);
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
        ]);

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->name = $this->name;
            $role->save();
        } else {
            $role = Role::create(['name' => $this->name]);
        }

        // Sincronizar permisos
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message', 'Rol guardado correctamente.');
        return redirect()->route('admin.roles.index');
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('livewire.admin.roles.role-form', compact('permissions'));
    }
}