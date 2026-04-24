<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;

class SupplierIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $deleteId = null;

    public function render()
    {
        $suppliers = Supplier::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('contact_name', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.suppliers.supplier-index', compact('suppliers'))->layout('components.layouts.app');
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $supplier = Supplier::findOrFail($this->deleteId);
        if ($supplier->purchases()->count() == 0) {
            $supplier->delete();
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Proveedor eliminado.']);
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No se puede eliminar proveedor con compras asociadas.']);
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }
}