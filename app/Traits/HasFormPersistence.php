<?php

namespace App\Traits;

trait HasFormPersistence
{
    protected function persistableProperties(): array
    {
        return [];
    }

    protected function persistenceKey(): string
    {
        return 'form_persistence_'.md5(static::class);
    }

    public function persistState(): void
    {
        $data = [];
        foreach ($this->persistableProperties() as $prop) {
            if (property_exists($this, $prop)) {
                $data[$prop] = $this->{$prop};
            }
        }
        session()->put($this->persistenceKey(), $data);
        $this->hasUnsavedChanges = $this->detectUnsavedChanges();
    }

    public function restorePersistedState(): void
    {
        if (session()->has($this->persistenceKey())) {
            $data = session()->get($this->persistenceKey());
            foreach ($data as $prop => $value) {
                if (property_exists($this, $prop)) {
                    $this->{$prop} = $value;
                }
            }
            $this->hasUnsavedChanges = $this->detectUnsavedChanges();
        }
    }

    public function clearPersistedState(): void
    {
        session()->forget($this->persistenceKey());
        $this->hasUnsavedChanges = false;
    }

    protected function detectUnsavedChanges(): bool
    {
        return false;
    }
}
