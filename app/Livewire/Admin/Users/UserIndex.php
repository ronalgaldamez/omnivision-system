<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $users = User::with(['roles', 'branch'])
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.admin.users.user-index', compact('users'));
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        $estado = $user->is_active ? 'activado' : 'desactivado';
        session()->flash('message', "Usuario {$user->name} {$estado} correctamente.");
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        if ($user->hasRole('admin') && User::role('admin')->count() == 1) {
            session()->flash('error', 'No se puede eliminar el único administrador.');
            return;
        }
        $user->delete();
        session()->flash('message', 'Usuario eliminado correctamente.');
    }
}
