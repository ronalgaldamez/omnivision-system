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

    public $uploaded = [];

    public $dui_front = null;
    public $dui_back = null;
    public $receipt = null;
    public $fachada = null;

    protected $rules = [
        'dui_front' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,heic,heif|max:20480',
        'dui_back' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,heic,heif|max:20480',
        'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,heic,heif|max:20480',
        'fachada' => 'nullable|file|mimes:jpg,jpeg,png,gif,heic,heif|max:20480',
    ];

    protected function saveUploadedFile($field, $file)
    {
        $folder = 'clients/' . $this->client->id . '/documents';
        $ext = $file->getClientOriginalExtension();
        $path = $folder . '/' . uniqid('doc_') . '.' . $ext;

        $fileData = file_get_contents($file->getRealPath());

        Storage::disk('s3')->put($path, $fileData);

        $docs = $this->client->uploaded_docs ?? [];
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
        $this->client = $this->client->fresh();
        $this->uploaded = $this->client->uploaded_docs ?? [];

        // Resetear la propiedad para permitir re-subidas
        $this->reset($field);

        $labels = [
            'dui_front' => 'DUI (Frente)',
            'dui_back' => 'DUI (Reverso)',
            'receipt' => 'Recibo de luz',
            'fachada' => 'Foto de Fachada',
        ];

        $this->dispatch('show-toast', type: 'success', message: $labels[$field] . ' subido correctamente.');
    }

    public function updated($propertyName)
    {
        $fieldMap = [
            'dui_front' => 'dui_front',
            'dui_back' => 'dui_back',
            'receipt' => 'receipt',
            'fachada' => 'fachada',
        ];

        if (!in_array($propertyName, ['dui_front', 'dui_back', 'receipt', 'fachada'])) {
            return;
        }

        $this->validateOnly($propertyName);

        $file = $this->{$propertyName};
        if (!$file) return;

        $this->saveUploadedFile($fieldMap[$propertyName], $file);
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

        $this->uploaded = $this->client->uploaded_docs ?? [];
    }

    public function uploadFromCamera($field, $base64Data, $originalName = null)
    {
        // Detectar mime type desde el base64
        $mime = '';
        $ext = 'jpg';
        if (preg_match('/^data:([^;]+);base64,/', $base64Data, $m)) {
            $mime = $m[1];
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'application/pdf' => 'pdf'];
            $ext = $extMap[$mime] ?? 'jpg';
        }

        $base64Data = preg_replace('/^data:[^;]+;base64,/', '', $base64Data);
        $fileData = base64_decode($base64Data);
        if (!$fileData) {
            $this->dispatch('capture-error', message: 'No se pudo decodificar el archivo.');
            return;
        }

        $folder = 'clients/' . $this->client->id . '/documents';
        $path = $folder . '/' . uniqid('doc_') . '.' . $ext;

        Storage::disk('s3')->put($path, $fileData);

        $docs = $this->client->uploaded_docs ?? [];
        $docs = array_filter($docs, fn($d) => $d['type'] !== $field);
        $docs[] = [
            'type' => $field,
            'path' => $path,
            'original_name' => $originalName ?? ($field . '.' . $ext),
            'mime_type' => $mime ?: 'image/jpeg',
            'file_size' => strlen($fileData),
            'uploaded_at' => now()->toIso8601String(),
        ];

        $this->client->update(['uploaded_docs' => array_values($docs)]);
        $this->client = $this->client->fresh();
        $this->uploaded = $this->client->uploaded_docs ?? [];

        $labels = [
            'dui_front' => 'DUI (Frente)',
            'dui_back' => 'DUI (Reverso)',
            'receipt' => 'Recibo de luz',
            'fachada' => 'Foto de Fachada',
        ];

        $this->dispatch('document-captured', field: $field, label: $labels[$field] ?? $field);
    }

    public function removeUpload($type)
    {
        $client = Client::where('docs_token', $this->token)->first();
        if (!$client) return;

        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $i => $d) {
            if ($d['type'] === $type) {
                Storage::disk('s3')->delete($d['path']);
                unset($docs[$i]);
                break;
            }
        }
        $client->update(['uploaded_docs' => array_values($docs)]);
        $this->client = $client->fresh();
        $this->uploaded = $this->client->uploaded_docs ?? [];
    }

    public function rejectUpload($type)
    {
        $this->removeUpload($type);
        $this->dispatch('show-toast', type: 'info', message: 'Documento eliminado.');
    }

    public function isUploaded($type): bool
    {
        $client = Client::where('docs_token', $this->token)->first();
        if (!$client) return false;
        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $doc) {
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
