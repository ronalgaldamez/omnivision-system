<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class UploadDocuments extends Component
{
    use WithFileUploads;

    public $token;
    public $client;
    public $expired = false;
    public $successMessage = false;

    public $dui_front = null;
    public $dui_back = null;
    public $receipt = null;

    public $uploaded = [];

    protected function rules()
    {
        return [
            'dui_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'dui_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function mount($token)
    {
        $this->token = $token;
        $this->client = Client::where('docs_token', $token)->first();

        if (!$this->client) {
            abort(404);
        }

        if ($this->client->docs_token_expires_at && $this->client->docs_token_expires_at->isPast()) {
            $this->expired = true;
            return;
        }

        // Cargar documentos ya subidos
        $this->uploaded = $this->client->uploaded_docs ?? [];
    }

    public function upload($field)
    {
        $this->validateOnly($field);

        $file = $this->$field;
        if (!$file) return;

        $folder = 'clients/' . $this->client->id . '/documents';
        $path = $file->store($folder, 's3');

        // Actualizar registro del cliente
        $docs = $this->client->uploaded_docs ?? [];
        // Eliminar entrada previa del mismo tipo si existe
        $docs = array_filter($docs, fn($d) => $d['type'] !== $field);
        $docs[] = [
            'type' => $field,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now()->toIso8601String(),
        ];

        $this->client->update(['uploaded_docs' => array_values($docs)]);
        $this->uploaded = $this->client->fresh()->uploaded_docs ?? [];

        $this->$field = null;

        $labels = [
            'dui_front' => 'DUI (Frente)',
            'dui_back' => 'DUI (Reverso)',
            'receipt' => 'Recibo de luz',
        ];

        $this->dispatch('show-toast', type: 'success', message: $labels[$field] . ' subido correctamente.');
    }

    public function updatedDuiFront()
    {
        $this->upload('dui_front');
    }

    public function updatedDuiBack()
    {
        $this->upload('dui_back');
    }

    public function updatedReceipt()
    {
        $this->upload('receipt');
    }

    public function removeUpload($type)
    {
        $docs = $this->client->uploaded_docs ?? [];
        foreach ($docs as $i => $d) {
            if ($d['type'] === $type) {
                Storage::disk('s3')->delete($d['path']);
                unset($docs[$i]);
                break;
            }
        }
        $this->client->update(['uploaded_docs' => array_values($docs)]);
        $this->uploaded = $this->client->fresh()->uploaded_docs ?? [];
    }

    public function isUploaded($type): bool
    {
        foreach ($this->uploaded as $doc) {
            if ($doc['type'] === $type) return true;
        }
        return false;
    }

    public function finalize()
    {
        $this->successMessage = true;
    }

    public function render()
    {
        return view('livewire.public.upload-documents')
            ->layout('components.layouts.blank');
    }
}
