<?php

namespace App\Livewire\Admin;

use App\Models\ChangelogEntry;
use Livewire\Component;

class ChangelogManager extends Component
{
    public $entries;
    public $showModal = false;
    public $editingId = null;
    public $version = '';
    public $title = '';
    public $description = '';
    public $publishNow = true;

    public function mount()
    {
        $this->loadEntries();
    }

    public function loadEntries()
    {
        $this->entries = ChangelogEntry::orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $entry = ChangelogEntry::findOrFail($id);
        $this->editingId = $entry->id;
        $this->version = $entry->version;
        $this->title = $entry->title;
        $this->description = $entry->description;
        $this->publishNow = !is_null($entry->published_at);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $data = [
            'version' => $this->version ?: null,
            'title' => $this->title,
            'description' => $this->description,
            'published_at' => $this->publishNow ? now() : null,
        ];

        if ($this->editingId) {
            ChangelogEntry::findOrFail($this->editingId)->update($data);
            $message = 'Actualización actualizada.';
        } else {
            ChangelogEntry::create($data);
            $message = 'Actualización creada.';
        }

        $this->dispatch('show-toast', type: 'success', message: $message);
        $this->showModal = false;
        $this->resetForm();
        $this->loadEntries();
    }

    public function delete($id)
    {
        ChangelogEntry::findOrFail($id)->delete();
        $this->dispatch('show-toast', type: 'success', message: 'Actualización eliminada.');
        $this->loadEntries();
    }

    public function importFromGit()
    {
        $output = [];
        $exitCode = 1;

        try {
            @exec('git log --pretty=format:"%h|%s" -10 2>&1', $output, $exitCode);
        } catch (\Throwable $e) {
            $exitCode = 1;
        }

        if ($exitCode !== 0 || empty($output)) {
            $this->dispatch('show-toast', type: 'error', message: 'No se pudo leer el historial de Git. Verificá que el repositorio esté disponible en el servidor.');
            return;
        }

        $created = 0;
        foreach ($output as $line) {
            if (!str_contains($line, '|')) continue;

            [$hash, $message] = explode('|', $line, 2);
            $message = trim($message);

            if (empty($message) || ChangelogEntry::where('title', $message)->exists()) {
                continue;
            }

            ChangelogEntry::create([
                'title' => $message,
                'description' => '',
                'published_at' => null,
            ]);
            $created++;
        }

        $this->loadEntries();
        $this->dispatch('show-toast', type: 'success', message: "Se importaron {$created} commit(s) como borradores.");
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->version = '';
        $this->title = '';
        $this->description = '';
        $this->publishNow = true;
    }

    public function render()
    {
        return view('livewire.admin.changelog-manager')->layout('components.layouts.app');
    }
}
