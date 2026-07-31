<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Client;
use App\Models\Contract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ClientPortal extends Component
{
    public $token;
    public $client;
    public $expired = false;
    public $step = 1;

    // Paso 1: Documentos
    public $uploaded = [];

    // Paso 2: Coordenadas
    public $latitude = '';
    public $longitude = '';
    public $coordinatesCaptured = false;
    public $privacyAccepted = false;
    public $showManualMode = false;
    public $error = null;

    // Paso 3: Firma
    public $signatureData = null;
    public $alreadySigned = false;

    public function mount($token)
    {
        $this->token = $token;
        $this->client = Client::where('portal_token', $token)->first();

        if (!$this->client) {
            abort(404, 'Enlace inválido o expirado.');
        }

        if ($this->client->portal_token_expires_at && $this->client->portal_token_expires_at->isPast()) {
            $this->expired = true;
            return;
        }

        // Cargar estado actual
        $this->uploaded = $this->client->uploaded_docs ?? [];

        if ($this->client->latitude && $this->client->longitude) {
            $this->latitude = $this->client->latitude;
            $this->longitude = $this->client->longitude;
            $this->coordinatesCaptured = true;
        }

        $docsApproved = $this->client->portal_docs_approved ?? false;
        $coordsApproved = $this->client->coordinates_approved ?? false;

        // Solo mostrar "completado" si docs y coordenadas fueron aprobados Y hay firma
        if ($this->client->client_signature_data && $docsApproved && $coordsApproved) {
            $this->alreadySigned = true;
        }

        // Determinar paso actual
        $this->resolveStep();
    }

    private function resolveStep(): void
    {
        $docsComplete = $this->allDocumentsUploaded();
        $coordsComplete = $this->coordinatesCaptured;
        $docsApproved = $this->client->portal_docs_approved ?? false;
        $coordsApproved = $this->client->coordinates_approved ?? false;

        if ($docsComplete && $coordsComplete && $docsApproved && $coordsApproved) {
            $this->step = 3;
        } elseif ($docsComplete && $coordsComplete) {
            $this->step = 2;
        } elseif ($docsComplete) {
            $this->step = 2;
        } else {
            $this->step = 1;
        }
    }

    // ─── Paso 1: Documentos ───

    public function allDocumentsUploaded(): bool
    {
        $required = ['dui_front', 'dui_back', 'receipt', 'fachada'];
        $uploadedTypes = array_map(fn($d) => $d['type'], $this->uploaded);
        return empty(array_diff($required, $uploadedTypes));
    }

    public function saveBase64File($field, $base64Data, $originalName = null)
    {
        try {
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

            // Si ya completó todos los docs, avanzar al paso 2
            if ($this->allDocumentsUploaded() && $this->step === 1) {
                $this->step = 2;
            }

            $this->dispatch('document-captured', field: $field, label: $labels[$field] ?? $field);
        } catch (\Exception $e) {
            Log::error('Error al guardar documento: ' . $e->getMessage());
            $this->dispatch('capture-error', message: 'Error al guardar el documento. Verifica tu conexión e intenta de nuevo.');
        }
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
        $this->client = $this->client->fresh();
        $this->uploaded = $this->client->uploaded_docs ?? [];
        $this->step = 1;
    }

    public function rejectUpload($type)
    {
        $this->removeUpload($type);
        $this->dispatch('show-toast', type: 'info', message: 'Documento eliminado.');
    }

    public function isUploaded($type): bool
    {
        foreach ($this->uploaded as $doc) {
            if ($doc['type'] === $type) return true;
        }
        return false;
    }

    // ─── Paso 2: Coordenadas ───

    public function saveCoordinates($lat, $lng)
    {
        $this->client->update([
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        Contract::where('client_id', $this->client->id)
            ->whereNull('latitude')
            ->update([
                'latitude' => $lat,
                'longitude' => $lng,
            ]);

        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->coordinatesCaptured = true;
        $this->error = null;

        $docsApproved = $this->client->portal_docs_approved ?? false;
        $coordsApproved = $this->client->coordinates_approved ?? false;

        if ($this->step === 2 && $docsApproved && $coordsApproved) {
            $this->step = 3;
        }

        $this->dispatch('coordinates-saved');
    }

    public function saveManual()
    {
        $this->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $this->saveCoordinates($this->latitude, $this->longitude);
    }

    public function enableManualMode()
    {
        $this->showManualMode = true;
        $this->error = null;
    }

    // ─── Paso 3: Firma ───

    public function saveSignature($signatureData)
    {
        if (!$signatureData || !is_string($signatureData)) {
            $this->dispatch('show-toast', type: 'error', message: 'No se recibieron datos de firma válidos.');
            return;
        }

        if ($this->client->client_signature_data) {
            $this->dispatch('show-toast', type: 'error', message: 'Esta firma ya fue registrada.');
            return;
        }

        $docsApproved = $this->client->portal_docs_approved ?? false;
        $coordsApproved = $this->client->coordinates_approved ?? false;

        if (!$docsApproved || !$coordsApproved) {
            $this->dispatch('show-toast', type: 'error', message: 'Tus documentos y coordenadas aún no han sido aprobados por un agente. Intentá más tarde.');
            return;
        }

        $this->client->update([
            'client_signature_data' => $signatureData,
            'portal_token' => null,
            'portal_token_expires_at' => null,
        ]);

        $this->alreadySigned = true;
        $this->step = 3;
        $this->dispatch('show-toast', type: 'success', message: 'Firma registrada correctamente.');
    }

    // ─── Navegación manual entre pasos (si ya completó el paso anterior) ───

    public function goToStep($step)
    {
        if ($step === 2 && !$this->allDocumentsUploaded()) return;
        if ($step === 3 && (!$this->allDocumentsUploaded() || !$this->coordinatesCaptured || !($this->client->portal_docs_approved ?? false) || !($this->client->coordinates_approved ?? false))) return;
        $this->step = $step;
    }

    public function render()
    {
        return view('livewire.public.client-portal')
            ->layout('components.layouts.blank');
    }
}
