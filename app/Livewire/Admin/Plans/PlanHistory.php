<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use Livewire\WithPagination;

class PlanHistory extends Component
{
    use WithPagination;

    public $historySearch = '';
    public $historyDateFrom = '';
    public $historyDateTo = '';

    public $showHistoryModal = false;
    public $historyPlanId = null;
    public $historyZoneId = null;
    public $historyRecords = [];

    public function loadPriceHistory($planId, $zoneId = null)
    {
        $this->historyPlanId = $planId;
        $this->historyZoneId = $zoneId;
        $query = \App\Models\PriceHistory::where('plan_id', $planId);
        if ($this->historyZoneId) {
            $query->where('zone_id', $this->historyZoneId);
        }
        $this->historyRecords = $query->with('user')->orderByDesc('created_at')->get();
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->historyPlanId = null;
        $this->historyZoneId = null;
        $this->historyRecords = [];
    }

    public function render()
    {
        $historyQuery = \App\Models\PriceHistory::with('plan', 'zone', 'user');
        if ($this->historySearch) {
            $historyQuery->where(function ($q) {
                $q->whereHas('plan', fn($q) => $q->where('name', 'like', '%' . $this->historySearch . '%'))
                  ->orWhereHas('zone', fn($q) => $q->where('name', 'like', '%' . $this->historySearch . '%'));
            });
        }
        if ($this->historyDateFrom) {
            $historyQuery->whereDate('created_at', '>=', $this->historyDateFrom);
        }
        if ($this->historyDateTo) {
            $historyQuery->whereDate('created_at', '<=', $this->historyDateTo);
        }
        $priceHistories = $historyQuery->orderByDesc('created_at')->paginate(50);

        return view('livewire.admin.plans.plan-history', compact('priceHistories'))
            ->layout('components.layouts.app');
    }
}
