<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Branch;
use App\Models\User;
use App\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterRole = '';
    public $filterBranch = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterRole' => ['except' => ''],
        'filterBranch' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterRole()
    {
        $this->resetPage();
    }

    public function updatedFilterBranch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $users = User::with(['roles', 'branch'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', function ($q) {
                $q->where('is_active', (bool) $this->filterStatus);
            })
            ->when($this->filterRole, function ($q) {
                $q->role($this->filterRole);
            })
            ->when($this->filterBranch, function ($q) {
                $q->where('branch_id', $this->filterBranch);
            })
            ->when($this->sortField === 'role', function ($q) {
                $q->orderBy(User::select('roles.name')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->orderBy('roles.name')
                    ->limit(1), $this->sortDirection);
            })
            ->when($this->sortField === 'branch', function ($q) {
                $q->orderBy(Branch::select('name')->whereColumn('id', 'users.branch_id'), $this->sortDirection);
            })
            ->when(!in_array($this->sortField, ['role', 'branch']), function ($q) {
                $q->orderBy($this->sortField, $this->sortDirection);
            })
            ->paginate(10);

        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.users.user-index', compact('users', 'roles', 'branches'))
            ->layout('components.layouts.app');
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
