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
    public $deleteName = '';
    public $deleteHasPurchases = false;
    public $deleting = false;

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, function ($q) {
                $q->whereRaw("MATCH(name, contact_name) AGAINST(? IN BOOLEAN MODE)", [$this->search . '*'])
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.suppliers.supplier-index', compact('suppliers'))->layout('components.layouts.app');
    }

    public function confirmDelete($id)
    {
        $supplier = Supplier::withCount('purchases')->findOrFail($id);
        $this->deleteId = $id;
        $this->deleteName = $supplier->name;
        $this->deleteHasPurchases = $supplier->purchases_count > 0;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->deleting) return;
        $this->deleting = true;

        $supplier = Supplier::findOrFail($this->deleteId);

        if ($supplier->purchases()->count() > 0) {
            session()->flash('error', 'No se puede eliminar: tiene compras asociadas.');
            $this->showDeleteModal = false;
            $this->deleting = false;
            return;
        }

        $supplier->delete();
        session()->flash('message', 'Proveedor eliminado correctamente.');
        $this->showDeleteModal = false;
        $this->deleteName = '';
        $this->deleteHasPurchases = false;
        $this->deleting = false;
    }
}
