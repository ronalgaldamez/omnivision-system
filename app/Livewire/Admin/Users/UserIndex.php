<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $users = User::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.admin.users.user-index', compact('users'));
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