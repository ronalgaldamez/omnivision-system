<?php

namespace App\Livewire\Admin\Branches;

use App\Models\Branch;
use Livewire\Component;

class BranchForm extends Component
{
    public $branchId;

    public $name = '';

    public $code = '';

    public $address = '';

    public $phone = '';

    public $isActive = true;

    public function mount($id = null)
    {
        if ($id) {
            $branch = Branch::findOrFail($id);
            $this->branchId = $branch->id;
            $this->name = $branch->name;
            $this->code = $branch->code;
            $this->address = $branch->address;
            $this->phone = $branch->phone;
            $this->isActive = $branch->is_active;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:branches,code,'.$this->branchId,
            'address' => 'nullable|string|max:65535',
            'phone' => 'nullable|string|max:20',
            'isActive' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        Branch::updateOrCreate(
            ['id' => $this->branchId],
            [
                'name' => $this->name,
                'code' => $this->code,
                'address' => $this->address,
                'phone' => $this->phone,
                'is_active' => $this->isActive,
            ]
        );

        $action = $this->branchId ? 'actualizada' : 'creada';
        session()->flash('message', "Sucursal {$action} correctamente.");

        return redirect()->route('admin.branches.index');
    }

    public function render()
    {
        return view('livewire.admin.branches.branch-form')->layout('components.layouts.app');
    }
}
