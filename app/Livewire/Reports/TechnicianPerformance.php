<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\User;
use App\Models\Requisition;
use App\Models\WorkOrder;
use App\Services\ChartDataService;

class TechnicianPerformance extends Component
{
    public $selectedTechnicianId = null;
    public $selectedMonths = 6;

    // Búsqueda de técnico (panel lateral)
    public $technicianSearch = '';
    public $technicianResults = [];
    public $showTechnicianModal = false;
    public $technicianListSearch = '';
    public $technicianList = [];

    // Búsqueda de técnico (modal de comparación)
    public $compareTechnicianSearch = '';
    public $compareTechnicianResults = [];
    public $showCompareTechnicianModal = false;
    public $compareTechnicianListSearch = '';
    public $compareTechnicianList = [];

    // Comparación de períodos
    public $compareTechnicianId = null;
    public $compareStartA = null;
    public $compareEndA = null;
    public $compareStartB = null;
    public $compareEndB = null;
    public $comparisonResult = null;
    public $showComparisonModal = false;


    public function mount()
    {
        $this->selectedTechnicianId = User::role('technician')->value('id');
        $this->compareTechnicianId = $this->selectedTechnicianId;

        // Default: mes actual vs mes anterior
        $this->compareStartA = now()->startOfMonth()->format('Y-m-d');
        $this->compareEndA = now()->endOfMonth()->format('Y-m-d');
        $this->compareStartB = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->compareEndB = now()->subMonth()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSelectedTechnicianId()
    {
        $this->compareTechnicianId = $this->selectedTechnicianId;
    }

    public function updatedSelectedMonths()
    {
        // No-op, el render se encarga
    }

    // ─────────── Búsqueda de técnico (panel lateral) ───────────
    public function updatedTechnicianSearch()
    {
        if (strlen($this->technicianSearch) >= 2) {
            $this->technicianResults = User::role('technician')
                ->where('name', 'like', '%' . $this->technicianSearch . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
        } else {
            $this->technicianResults = [];
        }
    }

    public function selectTechnician($id)
    {
        $technician = User::find($id);
        if ($technician) {
            $this->selectedTechnicianId = $technician->id;
            $this->compareTechnicianId = $technician->id;
            $this->technicianSearch = $technician->name;
            $this->technicianResults = [];
        }
    }

    public function clearTechnician()
    {
        $this->selectedTechnicianId = null;
        $this->technicianSearch = '';
        $this->technicianResults = [];
    }

    public function openTechnicianModal()
    {
        $this->technicianListSearch = '';
        $this->technicianList = User::role('technician')->orderBy('name')->take(50)->get();
        $this->showTechnicianModal = true;
    }

    public function closeTechnicianModal()
    {
        $this->showTechnicianModal = false;
        $this->technicianListSearch = '';
        $this->technicianList = [];
    }

    public function updatedTechnicianListSearch()
    {
        if (strlen($this->technicianListSearch) >= 2) {
            $this->technicianList = User::role('technician')
                ->where('name', 'like', '%' . $this->technicianListSearch . '%')
                ->orderBy('name')
                ->take(50)
                ->get();
        } else {
            $this->technicianList = User::role('technician')->orderBy('name')->take(50)->get();
        }
    }

    public function selectTechnicianFromList($id)
    {
        $this->selectTechnician($id);
        $this->closeTechnicianModal();
    }

    // ─────────── Búsqueda de técnico (modal de comparación) ───────────
    public function updatedCompareTechnicianSearch()
    {
        if (strlen($this->compareTechnicianSearch) >= 2) {
            $this->compareTechnicianResults = User::role('technician')
                ->where('name', 'like', '%' . $this->compareTechnicianSearch . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
        } else {
            $this->compareTechnicianResults = [];
        }
    }

    public function selectCompareTechnician($id)
    {
        $technician = User::find($id);
        if ($technician) {
            $this->compareTechnicianId = $technician->id;
            $this->compareTechnicianSearch = $technician->name;
            $this->compareTechnicianResults = [];
        }
    }

    public function clearCompareTechnician()
    {
        $this->compareTechnicianId = null;
        $this->compareTechnicianSearch = '';
        $this->compareTechnicianResults = [];
    }

    public function openCompareTechnicianModal()
    {
        $this->compareTechnicianListSearch = '';
        $this->compareTechnicianList = User::role('technician')->orderBy('name')->take(50)->get();
        $this->showCompareTechnicianModal = true;
    }

    public function closeCompareTechnicianModal()
    {
        $this->showCompareTechnicianModal = false;
        $this->compareTechnicianListSearch = '';
        $this->compareTechnicianList = [];
    }

    public function updatedCompareTechnicianListSearch()
    {
        if (strlen($this->compareTechnicianListSearch) >= 2) {
            $this->compareTechnicianList = User::role('technician')
                ->where('name', 'like', '%' . $this->compareTechnicianListSearch . '%')
                ->orderBy('name')
                ->take(50)
                ->get();
        } else {
            $this->compareTechnicianList = User::role('technician')->orderBy('name')->take(50)->get();
        }
    }

    public function selectCompareTechnicianFromList($id)
    {
        $this->selectCompareTechnician($id);
        $this->closeCompareTechnicianModal();
    }

    public function openComparisonModal($technicianId = null)
    {
        $this->compareTechnicianId = $technicianId ?? $this->selectedTechnicianId;
        $this->showComparisonModal = true;
        $this->comparisonResult = null;
    }

    public function closeComparisonModal()
    {
        $this->showComparisonModal = false;
        $this->comparisonResult = null;
    }

    public function generateComparison()
    {
        $this->validate([
            'compareTechnicianId' => 'required|exists:users,id',
            'compareStartA' => 'required|date',
            'compareEndA' => 'required|date|after_or_equal:compareStartA',
            'compareStartB' => 'required|date',
            'compareEndB' => 'required|date|after_or_equal:compareStartB',
        ]);

        $charts = app(ChartDataService::class);
        $this->comparisonResult = $charts->technicianComparison(
            $this->compareTechnicianId,
            $this->compareStartA,
            $this->compareEndA,
            $this->compareStartB,
            $this->compareEndB
        );
    }

    public function render()
    {
        $technicians = User::role('technician')->withCount([
            'requisitions as total_requests',
            'requisitions as approved_requests' => function ($q) {
                $q->where('status', 'approved');
            },
            'requisitions as rejected_requests' => function ($q) {
                $q->where('status', 'rejected');
            },
            'technicianReturns as surplus_returns' => function ($q) {
                $q->where('type', 'surplus');
            },
            'technicianReturns as damage_returns' => function ($q) {
                $q->where('type', 'damage');
            },
        ])->get();

        $charts = app(ChartDataService::class);
        $completedComparison = $charts->monthlyComparison(WorkOrder::class, ['status' => 'completed'], 'completed_date');

        // Datos del técnico seleccionado para el panel lateral
        $selectedTechnician = null;
        $technicianPerformance = null;
        if ($this->selectedTechnicianId) {
            $selectedTechnician = User::find($this->selectedTechnicianId);
            $technicianPerformance = $charts->technicianMonthlyPerformance($this->selectedTechnicianId, $this->selectedMonths);
        }

        // Técnico de comparación
        $compareTechnician = null;
        if ($this->compareTechnicianId) {
            $compareTechnician = User::find($this->compareTechnicianId);
        }

        return view('livewire.reports.technician-performance', compact(
            'technicians',
            'completedComparison',
            'selectedTechnician',
            'technicianPerformance',
            'compareTechnician'
        ))->layout('components.layouts.app');
    }
}
