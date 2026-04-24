<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $roles = Role::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.roles.role-index', compact('roles'));
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        // No permitir eliminar rol admin si es el único
        if ($role->name === 'admin' && Role::where('name', 'admin')->count() == 1) {
            session()->flash('error', 'No se puede eliminar el único rol administrador.');
            return;
        }
        // No eliminar si tiene usuarios asignados
        if ($role->users()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
            return;
        }
        $role->delete();
        session()->flash('message', 'Rol eliminado correctamente.');
    }
}