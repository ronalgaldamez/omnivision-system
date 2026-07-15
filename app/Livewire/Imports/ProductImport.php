<?php

namespace App\Livewire\Imports;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public $preview = [];
    public $columns = [];
    public $step = 'upload';
    public $stats = null;
    public $importing = false;

    protected function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ];
    }

    public function updatedFile()
    {
        $this->validate();

        $this->preview = [];
        $this->stats = null;
        $this->step = 'preview';

        try {
            $import = new ProductsImport();
            $collection = Excel::toCollection($import, $this->file->getRealPath());

            if ($collection->isEmpty() || $collection->first()->isEmpty()) {
                $this->dispatch('show-toast', type: 'error', message: 'El archivo está vacío.');
                $this->step = 'upload';
                return;
            }

            $rows = $collection->first();
            $this->columns = array_keys($rows->first()->toArray());
            $this->preview = $rows->take(5)->toArray();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error al leer el archivo: ' . $e->getMessage());
            $this->step = 'upload';
        }
    }

    public function import()
    {
        if (!$this->file || $this->importing) return;

        $this->importing = true;

        try {
            $import = new ProductsImport();
            Excel::import($import, $this->file->getRealPath());

            $this->stats = $import->getStats();
            $this->step = 'results';
            $this->dispatch('show-toast', type: 'success', message: "{$this->stats['imported']} producto(s) importado(s).");
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error durante la importación: ' . $e->getMessage());
        }

        $this->importing = false;
    }

    public function resetUpload()
    {
        $this->file = null;
        $this->preview = [];
        $this->columns = [];
        $this->stats = null;
        $this->step = 'upload';
    }

    public function render()
    {
        return view('livewire.imports.product-import')->layout('components.layouts.app');
    }
}
