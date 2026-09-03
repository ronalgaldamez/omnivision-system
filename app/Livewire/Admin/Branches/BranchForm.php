<?php

namespace App\Livewire\Admin\Branches;

use App\Models\Branch;
use App\Models\Company;
use Livewire\Component;

class BranchForm extends Component
{
    public $branchId;

    public $companyId = '';

    public $name = '';

    public $code = '';

    public $address = '';

    public $phone = '';

    public $isActive = true;

    public $companies = [];

    public function mount($id = null)
    {
        $this->companies = Company::where('is_active', true)->orderBy('razon_social')->get();

        if ($id) {
            $branch = Branch::findOrFail($id);
            $this->branchId = $branch->id;
            $this->companyId = $branch->company_id;
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
            'companyId' => 'nullable|exists:companies,id',
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
                'company_id' => $this->companyId ?: null,
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
        return view('livewire.admin.branches.branch-form', ['companies' => $this->companies])->layout('components.layouts.app');
    }
}
