<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Shelf;

class ShelvesManager extends Component
{
    public $showModal = false;
    public $editingId = null;
    public $parent_id = '';
    public $code = '';
    public $label = '';
    public $description = '';
    public $type = 'rack';
    public $warehouse = '';
    public $is_active = true;
    public $is_full = false;

    public $confirmingDelete = false;
    public $deletingId = null;

    public $viewMode = 'tree';

    // Inline quick-create (solo para hijos)
    public $showInlineForm = null;
    public $quickCode = '';
    public $quickLabel = '';
    public $quickType = 'bin';

    protected function rules()
    {
        return [
            'code' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|max:50',
            'warehouse' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:shelves,id',
            'is_active' => 'boolean',
            'is_full' => 'boolean',
        ];
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    protected function getListeners()
    {
        return [
            'moveContainer' => 'moveContainer',
        ];
    }

    public function openCreate()
    {
        $this->resetModal();
        $this->code = $this->suggestRootCode();
        $this->type = 'rack';
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $this->resetModal();
        $shelf = Shelf::findOrFail($id);
        $this->editingId = $shelf->id;
        $this->parent_id = $shelf->parent_id ?? '';
        $this->code = $shelf->code;
        $this->label = $shelf->label;
        $this->description = $shelf->description ?? '';
        $this->type = $shelf->type;
        $this->warehouse = $shelf->warehouse ?? '';
        $this->is_active = $shelf->is_active;
        $this->is_full = $shelf->is_full;
        $this->showModal = true;
    }

    public function toggleFull($id)
    {
        $shelf = Shelf::find($id);
        if (!$shelf) return;
        $shelf->update(['is_full' => !$shelf->is_full]);
        $this->dispatch('show-toast', type: 'success', message: $shelf->is_full ? 'Marcado como lleno.' : 'Marcado como disponible.');
    }

    public function save()
    {
        if (!$this->editingId) {
            $this->validate([
                'label' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
            ]);
        } else {
            $this->validate();
        }

        Shelf::updateOrCreate(
            ['id' => $this->editingId],
            [
                'parent_id' => $this->parent_id ?: null,
                'code' => $this->code,
                'label' => $this->label,
                'description' => $this->description ?: null,
                'type' => $this->editingId ? $this->type : 'rack',
                'warehouse' => $this->warehouse ?: null,
                'is_active' => $this->is_active,
                'is_full' => $this->is_full,
            ]
        );

        $this->dispatch('show-toast', type: 'success', message: $this->editingId ? 'Estantería actualizada.' : 'Estantería creada.');
        $this->showModal = false;
        $this->resetModal();
    }

    private function suggestRootCode()
    {
        $last = Shelf::whereNull('parent_id')
            ->where('code', 'like', 'EST-%')
            ->orderByDesc('code')
            ->first();

        if ($last && preg_match('/EST-(\d+)/', $last->code, $m)) {
            $num = (int) $m[1] + 1;
        } else {
            $num = 1;
        }

        return 'EST-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    // ========== INLINE QUICK CREATE (hijos) ==========

    public function toggleInlineForm($parentId)
    {
        if ($this->showInlineForm === $parentId) {
            $this->showInlineForm = null;
            return;
        }
        $this->showInlineForm = $parentId;
        $parent = Shelf::find($parentId);
        $this->quickType = $parent ? $this->suggestType($parent) : 'bin';
        $this->quickCode = $parent ? $this->suggestCode($parent) : '';
        $this->quickLabel = '';
        $this->resetValidation();
    }

    public function quickCreate()
    {
        $this->validate([
            'quickCode' => 'required|string|max:50',
            'quickLabel' => 'required|string|max:255',
            'quickType' => 'required|string|max:50',
        ]);

        $parent = Shelf::find($this->showInlineForm);

        Shelf::create([
            'parent_id' => $this->showInlineForm,
            'code' => $this->quickCode,
            'label' => $this->quickLabel,
            'type' => $this->quickType,
            'warehouse' => $parent?->warehouse,
            'is_active' => true,
        ]);

        $this->showInlineForm = null;
        $this->quickCode = '';
        $this->quickLabel = '';
        $this->quickType = 'bin';

        $this->dispatch('show-toast', type: 'success', message: 'Contenedor creado.');
    }

    public function suggestCode($parent)
    {
        $existingCodes = $parent->children()->pluck('code')->toArray();
        $prefix = $parent->code . '-';
        $max = 0;
        foreach ($existingCodes as $existing) {
            if (str_starts_with($existing, $prefix)) {
                $num = substr($existing, strlen($prefix));
                if (is_numeric($num)) {
                    $max = max($max, (int) $num);
                }
            }
        }
        return $prefix . str_pad($max + 1, 2, '0', STR_PAD_LEFT);
    }

    public function suggestType($parent)
    {
        $map = [
            'rack' => 'bin',
            'shelf' => 'bin',
            'bin' => 'container',
            'container' => 'drawer',
            'drawer' => 'drawer',
        ];
        return $map[$parent->type] ?? 'bin';
    }

    // ========== DELETE ==========

    public function promptDelete($id)
    {
        $this->confirmingDelete = true;
        $this->deletingId = $id;
    }

    public function executeDelete()
    {
        $shelf = Shelf::find($this->deletingId);
        if ($shelf) {
            if ($shelf->children()->exists()) {
                $this->dispatch('show-toast', type: 'error', message: 'No se puede eliminar: tiene sub-ubicaciones.');
                $this->cancelDelete();
                return;
            }
            $shelf->delete();
            $this->dispatch('show-toast', type: 'success', message: 'Estantería eliminada.');
        }
        $this->cancelDelete();
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->deletingId = null;
    }

    public function moveContainer($containerId, $newParentId)
    {
        $container = Shelf::find($containerId);
        if (!$container) return;

        $container->parent_id = $newParentId ?: null;
        $container->save();

        $this->dispatch('show-toast', type: 'success', message: 'Contenedor movido correctamente.');
    }

    private function resetModal()
    {
        $this->editingId = null;
        $this->parent_id = '';
        $this->code = '';
        $this->label = '';
        $this->description = '';
        $this->type = 'rack';
        $this->warehouse = '';
        $this->is_active = true;
        $this->is_full = false;
    }

    public function render()
    {
        $shelves = Shelf::with('children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $availableParents = Shelf::where('is_active', true)
            ->orderBy('code')
            ->get();

        $typeOptions = [
            'rack' => 'Rack / Estante metálico',
            'shelf' => 'Bandeja / Repisa',
            'bin' => 'Caja / Bin',
            'container' => 'Contenedor plástico',
            'drawer' => 'Gaveta / Cajón',
        ];

        $childTypeOptions = [
            'shelf' => 'Bandeja / Repisa',
            'bin' => 'Caja / Bin',
            'container' => 'Contenedor plástico',
            'drawer' => 'Gaveta / Cajón',
        ];

        $racks = Shelf::with(['children' => function ($q) {
            $q->with('products')->orderBy('code');
        }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('livewire.admin.shelves-manager', compact(
            'shelves', 'availableParents', 'typeOptions', 'childTypeOptions', 'racks'
        ))->layout('components.layouts.app');
    }
}
