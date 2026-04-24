<?php

namespace App\Exports;

use App\Models\Movement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MovementsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $movements;

    public function __construct($movements)
    {
        $this->movements = $movements;
    }

    public function collection()
    {
        return $this->movements;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Producto',
            'Tipo',
            'Cantidad',
            'Costo unitario',
            'Descripción',
            'Usuario',
            'Fecha'
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->id,
            $movement->product->name,
            $movement->type,
            $movement->quantity,
            $movement->unit_cost,
            $movement->description,
            $movement->user->name,
            $movement->created_at->format('d/m/Y H:i'),
        ];
    }
}