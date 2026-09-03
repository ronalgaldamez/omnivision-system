<?php

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $companies = Company::withCount('branches')
            ->where('razon_social', 'like', '%'.$this->search.'%')
            ->orWhere('nombre_comercial', 'like', '%'.$this->search.'%')
            ->orWhere('nit', 'like', '%'.$this->search.'%')
            ->orderBy('razon_social')
            ->paginate(10);

        return view('livewire.admin.companies.company-index', compact('companies'));
    }

    public function toggleActive($id)
    {
        $company = Company::findOrFail($id);
        $company->update(['is_active' => ! $company->is_active]);
        $estado = $company->is_active ? 'activada' : 'desactivada';
        session()->flash('message', "Empresa {$company->razon_social} {$estado} correctamente.");
    }

    public function delete($id)
    {
        $company = Company::findOrFail($id);

        if ($company->branches()->where('is_active', true)->exists()) {
            session()->flash('error', "No se puede eliminar la empresa {$company->razon_social}: tiene sucursales activas asociadas.");
            return;
        }

        $name = $company->razon_social;
        $company->branches()->update(['company_id' => null]);
        $company->delete();
        session()->flash('message', "Empresa {$name} eliminada correctamente.");
    }
}
