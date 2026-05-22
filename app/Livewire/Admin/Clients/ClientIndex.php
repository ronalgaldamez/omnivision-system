<?php

namespace App\Livewire\Admin\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;

class ClientIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        if (auth()->user()->can('delete clients')) {
            Client::findOrFail($id)->delete();
            session()->flash('message', 'Cliente eliminado correctamente.');
        }
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.clients.client-index', [
            'clients' => $clients,
        ])->layout('components.layouts.app');
    }
}