<?php

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use Livewire\Component;

class CompanyForm extends Component
{
    public $companyId;

    public $razonSocial = '';

    public $nombreComercial = '';

    public $tipo = 'persona_natural';

    public $nit = '';

    public $isActive = true;

    public function mount($id = null)
    {
        if ($id) {
            $company = Company::findOrFail($id);
            $this->companyId = $company->id;
            $this->razonSocial = $company->razon_social;
            $this->nombreComercial = $company->nombre_comercial;
            $this->tipo = $company->tipo;
            $this->nit = $company->nit;
            $this->isActive = $company->is_active;
        }
    }

    protected function rules()
    {
        return [
            'razonSocial' => 'required|string|max:255',
            'nombreComercial' => 'nullable|string|max:255',
            'tipo' => 'required|in:sociedad,persona_natural',
            'nit' => 'nullable|string|max:20',
            'isActive' => 'boolean',
        ];
    }

    public function save()
    {
        $this->validate();

        Company::updateOrCreate(
            ['id' => $this->companyId],
            [
                'razon_social' => $this->razonSocial,
                'nombre_comercial' => $this->nombreComercial ?: null,
                'tipo' => $this->tipo,
                'nit' => $this->nit ?: null,
                'is_active' => $this->isActive,
            ]
        );

        $action = $this->companyId ? 'actualizada' : 'creada';
        session()->flash('message', "Empresa {$action} correctamente.");

        return redirect()->route('admin.companies.index');
    }

    public function render()
    {
        return view('livewire.admin.companies.company-form')->layout('components.layouts.app');
    }
}
