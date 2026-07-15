<?php

namespace App\Livewire\Supervisor;

use Livewire\Component;
use App\Models\Vehiculo;

class VehiculoManager extends Component
{
    public $showForm = false;
    public $editingId = null;
    public $placa = '';
    public $marca = '';
    public $modelo = '';
    public $anio = '';
    public $color = '';
    public $tipo = '';
    public $estado = 'activo';
    public $notas = '';
    public $search = '';

    protected function rules()
    {
        return [
            'placa' => 'required|string|max:20|unique:vehiculos,placa,' . $this->editingId,
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'anio' => 'nullable|integer|min:1900|max:2099',
            'color' => 'nullable|string|max:50',
            'tipo' => 'nullable|string|max:50',
            'estado' => 'required|in:activo,averiado,mantenimiento,baja',
            'notas' => 'nullable|string',
        ];
    }

    public function openForm($id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $v = Vehiculo::findOrFail($id);
            $this->placa = $v->placa;
            $this->marca = $v->marca;
            $this->modelo = $v->modelo;
            $this->anio = $v->anio;
            $this->color = $v->color;
            $this->tipo = $v->tipo;
            $this->estado = $v->estado;
            $this->notas = $v->notas;
        } else {
            $this->reset(['placa', 'marca', 'modelo', 'anio', 'color', 'tipo', 'notas']);
            $this->estado = 'activo';
        }

        $this->showForm = true;
    }

    public function save()
    {
        $this->anio = $this->anio ?: null;
        $this->validate();

        $data = [
            'placa' => strtoupper($this->placa),
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio ?: null,
            'color' => $this->color ?: null,
            'tipo' => $this->tipo ?: null,
            'estado' => $this->estado,
            'notas' => $this->notas ?: null,
        ];

        if ($this->editingId) {
            Vehiculo::findOrFail($this->editingId)->update($data);
        } else {
            Vehiculo::create($data);
        }

        $this->dispatch('show-toast', type: 'success', message: $this->editingId ? 'Vehículo actualizado.' : 'Vehículo registrado.');
        $this->showForm = false;
        $this->editingId = null;
    }

    public function render()
    {
        $vehiculos = Vehiculo::with('encargadoActual.encargado')
            ->when($this->search, fn($q) => $q->where('placa', 'like', "%{$this->search}%")
                ->orWhere('marca', 'like', "%{$this->search}%")
                ->orWhere('modelo', 'like', "%{$this->search}%"))
            ->orderBy('estado')
            ->orderBy('placa')
            ->paginate(15);

        return view('livewire.supervisor.vehiculo-manager', compact('vehiculos'))
            ->layout('components.layouts.app');
    }
}
