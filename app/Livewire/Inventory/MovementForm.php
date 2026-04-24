<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\Product;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class MovementForm extends Component
{
    public $movementList = [];

    public $currentProductId = '';
    public $currentProductSearch = '';
    public $currentType = 'entry';
    public $currentQuantity = 1;
    public $currentUnitCost = null;
    public $currentDescription = '';

    public $searchResults = [];

    public $showConfirmModal = false;
    public $modalAction = null;
    public $modalItemIndex = null;
    public $modalMessage = '';

    public $editingIndex = null;

    protected $rules = [
        'currentProductId' => 'required|exists:products,id',
        'currentType' => 'required|in:entry,exit,technician_out',
        'currentQuantity' => 'required|numeric|min:0.01',
        'currentUnitCost' => 'nullable|numeric|min:0',
        'currentDescription' => 'nullable|string',
    ];

    public function mount()
    {
        $this->movementList = [];
        $this->resetCurrent();
    }

    public function resetCurrent()
    {
        $this->currentProductId = '';
        $this->currentProductSearch = '';
        $this->currentType = 'entry';
        $this->currentQuantity = 1;
        $this->currentUnitCost = null;
        $this->currentDescription = '';
        $this->searchResults = [];
        $this->editingIndex = null;
    }

    public function updatedCurrentProductSearch()
    {
        if (strlen($this->currentProductSearch) >= 2) {
            $this->searchResults = Product::where('name', 'like', '%' . $this->currentProductSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->currentProductSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->currentProductId = $product->id;
            $this->currentProductSearch = $product->name . ' (' . $product->sku . ') - Stock: ' . $product->current_stock;
            $this->searchResults = [];
        }
    }

    public function addToList()
    {
        $this->validate();

        $product = Product::find($this->currentProductId);

        if (in_array($this->currentType, ['exit', 'technician_out']) && $product->current_stock < $this->currentQuantity) {
            $this->addError('currentQuantity', 'Stock insuficiente. Stock actual: ' . $product->current_stock);
            return;
        }

        $newMovement = [
            'product_id' => $this->currentProductId,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'current_stock' => $product->current_stock,
            'type' => $this->currentType,
            'quantity' => $this->currentQuantity,
            'unit_cost' => $this->currentUnitCost,
            'description' => $this->currentDescription,
        ];

        if ($this->editingIndex !== null) {
            $this->movementList[$this->editingIndex] = $newMovement;
            $this->editingIndex = null;
        } else {
            $this->movementList[] = $newMovement;
        }

        $this->resetCurrent();
    }

    public function confirmEdit($index)
    {
        $this->modalAction = 'edit';
        $this->modalItemIndex = $index;
        $this->modalMessage = '¿Editar este movimiento? Los datos se cargarán en el formulario para modificarlos.';
        $this->showConfirmModal = true;
    }

    public function confirmDelete($index)
    {
        $this->modalAction = 'delete';
        $this->modalItemIndex = $index;
        $this->modalMessage = '¿Eliminar este movimiento de la lista?';
        $this->showConfirmModal = true;
    }

    public function executeModalAction()
    {
        if ($this->modalAction === 'edit') {
            $this->performEdit($this->modalItemIndex);
        } elseif ($this->modalAction === 'delete') {
            $this->performDelete($this->modalItemIndex);
        } elseif ($this->modalAction === 'save') {
            $this->performSave();
        }
        $this->closeModal();
    }

    public function performEdit($index)
    {
        $mov = $this->movementList[$index];
        $this->currentProductId = $mov['product_id'];
        $this->currentProductSearch = $mov['product_name'] . ' (' . $mov['product_sku'] . ') - Stock: ' . $mov['current_stock'];
        $this->currentType = $mov['type'];
        $this->currentQuantity = $mov['quantity'];
        $this->currentUnitCost = $mov['unit_cost'];
        $this->currentDescription = $mov['description'];
        $this->removeFromList($index, false);
        $this->editingIndex = $index;
        $this->dispatch('showToast', ['type' => 'info', 'message' => 'Movimiento cargado para edición. Modifica los campos y haz clic en "Agregar movimiento".']);
    }

    public function performDelete($index)
    {
        $this->removeFromList($index, true);
    }

    public function removeFromList($index, $showMessage = true)
    {
        unset($this->movementList[$index]);
        $this->movementList = array_values($this->movementList);
        if ($showMessage) {
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Movimiento eliminado de la lista.']);
        }
    }

    public function closeModal()
    {
        $this->showConfirmModal = false;
        $this->modalAction = null;
        $this->modalItemIndex = null;
        $this->modalMessage = '';
    }

    public function confirmSaveAll()
    {
        if (empty($this->movementList)) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Agrega al menos un movimiento antes de guardar.']);
            return;
        }
        $this->modalAction = 'save';
        $this->modalMessage = '¿Estás seguro de guardar estos movimientos? Los movimientos de stock se actualizarán automáticamente.';
        $this->showConfirmModal = true;
    }

    public function performSave()
    {
        $inventoryService = new InventoryService();

        foreach ($this->movementList as $mov) {
            $product = Product::find($mov['product_id']);

            // Validar stock para salidas
            if (in_array($mov['type'], ['exit', 'technician_out']) && $product->current_stock < $mov['quantity']) {
                $this->dispatch('showToast', ['type' => 'error', 'message' => "Stock insuficiente para {$product->name}. No se guardó ningún movimiento."]);
                return;
            }

            // Si es salida y no tiene costo, asignar el costo promedio actual
            if (in_array($mov['type'], ['exit', 'technician_out']) && is_null($mov['unit_cost'])) {
                $mov['unit_cost'] = $product->average_cost ?? 0;
            }

            $movement = Movement::create([
                'product_id' => $mov['product_id'],
                'type' => $mov['type'],
                'quantity' => $mov['quantity'],
                'unit_cost' => $mov['unit_cost'],
                'description' => $mov['description'],
                'user_id' => Auth::id(),
            ]);

            if ($mov['type'] === 'entry') {
                $inventoryService->processPurchaseEntry($product, $mov['quantity'], $mov['unit_cost'], $movement);
            } else {
                $inventoryService->processExit($product, $mov['quantity'], $movement);
            }
        }

        $this->dispatch('showToast', ['type' => 'success', 'message' => count($this->movementList) . ' movimiento(s) registrado(s).']);
        return redirect()->route('movements.index');
    }

    public function render()
    {
        return view('livewire.inventory.movements.form')->layout('components.layouts.app');
    }
}