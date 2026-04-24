<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Movement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MovementsExport;

class MovementsReport extends Component
{
    public $typeFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function exportExcel()
    {
        $movements = $this->getMovements();
        return Excel::download(new MovementsExport($movements), 'movimientos_' . now()->format('Ymd_His') . '.xlsx');
    }

    protected function getMovements()
    {
        return Movement::with('product', 'user')
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        $movements = $this->getMovements();
        return view('livewire.reports.movements-report', compact('movements'))->layout('components.layouts.app');
    }
}