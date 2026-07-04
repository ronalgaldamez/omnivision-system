<?php

namespace App\Livewire\Admin\Branches;

use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;

class BranchIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $branches = Branch::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('code', 'like', '%'.$this->search.'%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.branches.branch-index', compact('branches'));
    }

    public function toggleActive($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['is_active' => ! $branch->is_active]);
        $estado = $branch->is_active ? 'activada' : 'desactivada';
        session()->flash('message', "Sucursal {$branch->name} {$estado} correctamente.");
    }

    public function delete($id)
    {
        $branch = Branch::findOrFail($id);
        $name = $branch->name;
        $branch->delete();
        session()->flash('message', "Sucursal {$name} eliminada correctamente.");
    }
}
