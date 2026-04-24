<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Supplier;

class SupplierForm extends Component
{
    public $supplierId;
    public $name;
    public $contact_name;
    public $phone;
    public $email;
    public $address;
    public $nrc;
    public $nit;
    public $bankAccounts = [];

    public $showConfirmModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'contact_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'nullable|string',
        'nrc' => 'nullable|string|max:20',
        'nit' => 'nullable|string|max:20',
        'bankAccounts' => 'nullable|array',
        'bankAccounts.*.bank_name' => 'nullable|string|max:100',
        'bankAccounts.*.account_number' => 'nullable|string|max:50',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->supplierId = $supplier->id;
            $this->name = $supplier->name;
            $this->contact_name = $supplier->contact_name;
            $this->phone = $supplier->phone;
            $this->email = $supplier->email;
            $this->address = $supplier->address;
            $this->nrc = $supplier->nrc;
            $this->nit = $supplier->nit;
            $this->bankAccounts = $supplier->bank_accounts ?? [];
        }
    }

    public function addBankAccount()
    {
        $this->bankAccounts[] = ['bank_name' => '', 'account_number' => ''];
    }

    public function removeBankAccount($index)
    {
        unset($this->bankAccounts[$index]);
        $this->bankAccounts = array_values($this->bankAccounts);
    }

    public function confirmSave()
    {
        $this->validate();
        $this->showConfirmModal = true;
    }

    public function save()
    {
        Supplier::updateOrCreate(['id' => $this->supplierId], [
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'nrc' => $this->nrc,
            'nit' => $this->nit,
            'bank_accounts' => $this->bankAccounts,
        ]);

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Proveedor guardado correctamente.']);
        $this->redirectRoute('suppliers.index');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-form')->layout('components.layouts.app');
    }
}