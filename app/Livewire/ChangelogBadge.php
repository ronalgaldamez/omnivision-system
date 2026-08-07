<?php

namespace App\Livewire;

use App\Models\ChangelogEntry;
use App\Models\Setting;
use Livewire\Component;

class ChangelogBadge extends Component
{
    public $hasUpdates = false;
    public $newCount = 0;
    public $updates = [];
    public $showModal = false;

    public function mount()
    {
        $this->loadUpdates();
        if ($this->hasUpdates) {
            $this->open();
        }
    }

    public function loadUpdates()
    {
        $lastSeenId = (int) Setting::get('changelog_last_seen_' . auth()->id(), 0);
        $this->newCount = ChangelogEntry::published()->where('id', '>', $lastSeenId)->count();
        $this->hasUpdates = $this->newCount > 0;
    }

    public function open()
    {
        $this->updates = ChangelogEntry::published()->orderByDesc('published_at')->take(10)->get();
        $this->showModal = true;
    }

    public function close()
    {
        $lastSeenId = (int) Setting::get('changelog_last_seen_' . auth()->id(), 0);
        $latestId = ChangelogEntry::published()->where('id', '>', $lastSeenId)->max('id');
        if ($latestId) {
            Setting::set('changelog_last_seen_' . auth()->id(), (string) $latestId);
        }
        $this->showModal = false;
        $this->loadUpdates();
        $this->updates = collect();
    }

    public function render()
    {
        return view('livewire.changelog-badge');
    }
}
