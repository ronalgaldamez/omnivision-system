<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Supplier;

class SupplierForm extends Component
{
    public $supplierId;
    public $name;
    public $contact_name;
    public $phones = [];
    public $email;
    public $address;
    public $nrc;
    public $nit;
    public $bankAccounts = [];
    public $showConfirmModal = false;
    public $draftRestored = false;
    public $saving = false;

    protected function rules()
    {
        $supplierId = $this->supplierId;
        return [
            'name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', "unique:suppliers,email,{$supplierId}"],
            'address' => 'nullable|string',
            'nrc' => 'required|digits:8',
            'nit' => 'required|digits:9',
            'phones' => 'nullable|array',
            'phones.*' => ['nullable', 'string', 'regex:/^\d{4}-\d{4}$/'],
            'bankAccounts' => 'nullable|array',
            'bankAccounts.*.bank_name' => 'nullable|string|max:100',
            'bankAccounts.*.account_number' => ['nullable', 'string', 'max:50', 'regex:/^\\d+$/'],
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->supplierId = $supplier->id;
            $this->name = $supplier->name;
            $this->contact_name = $supplier->contact_name;
            $this->phones = $supplier->phones ?? [];
            $this->email = $supplier->email;
            $this->address = $supplier->address;
            $this->nrc = $supplier->nrc;
            $this->nit = $supplier->nit;
            $this->bankAccounts = $supplier->bank_accounts ?? [];
        } elseif ($draft = session()->get('supplier_form_draft')) {
            $this->name = $draft['name'] ?? '';
            $this->contact_name = $draft['contact_name'] ?? '';
            $this->phones = $draft['phones'] ?? [];
            $this->email = $draft['email'] ?? '';
            $this->address = $draft['address'] ?? '';
            $this->nrc = $draft['nrc'] ?? '';
            $this->nit = $draft['nit'] ?? '';
            $this->bankAccounts = $draft['bank_accounts'] ?? [];
            $this->draftRestored = true;
        }
    }

    public function updated($property)
    {
        if ($property === 'showConfirmModal') return;
        session()->put('supplier_form_draft', [
            'name' => $this->name, 'contact_name' => $this->contact_name,
            'phones' => $this->phones, 'email' => $this->email,
            'address' => $this->address, 'nrc' => $this->nrc,
            'nit' => $this->nit, 'bank_accounts' => $this->bankAccounts,
        ]);
    }

    public function addPhone() { $this->phones[] = ''; }
    public function removePhone($index) { unset($this->phones[$index]); $this->phones = array_values($this->phones); }
    public function addBankAccount() { $this->bankAccounts[] = ['bank_name' => '', 'account_number' => '']; }
    public function removeBankAccount($index) { unset($this->bankAccounts[$index]); $this->bankAccounts = array_values($this->bankAccounts); }

    public function confirmSave()
    {
        $errors = [];
        foreach (array_filter($this->phones) as $phone) {
            if (Supplier::where('id', '!=', $this->supplierId)->whereJsonContains('phones', $phone)->exists()) {
                $errors[] = "El teléfono {$phone} ya está registrado en otro proveedor.";
                break;
            }
        }
        foreach ($this->bankAccounts as $acc) {
            if (empty($acc['account_number'])) continue;
            if (Supplier::where('id', '!=', $this->supplierId)->whereJsonContains('bank_accounts->account_number', $acc['account_number'])->exists()) {
                $errors[] = "La cuenta {$acc['account_number']} ya existe en otro proveedor.";
                break;
            }
        }
        if (!empty($errors)) {
            $this->dispatch('show-toasts', errors: $errors);
            return;
        }
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toasts', errors: $e->validator->errors()->all());
            throw $e;
        }
        $this->showConfirmModal = true;
    }

    public function save()
    {
        if ($this->saving) return;
        $this->saving = true;

        Supplier::updateOrCreate(['id' => $this->supplierId], [
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'phones' => array_values(array_filter($this->phones)),
            'email' => $this->email,
            'address' => $this->address,
            'nrc' => $this->nrc,
            'nit' => $this->nit,
            'bank_accounts' => array_filter($this->bankAccounts, fn($a) => !empty($a['bank_name']) || !empty($a['account_number'])),
        ]);

        session()->forget('supplier_form_draft');
        session()->flash('message', 'Proveedor guardado correctamente.');
        $this->redirectRoute('suppliers.index');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-form')->layout('components.layouts.app');
    }
}
