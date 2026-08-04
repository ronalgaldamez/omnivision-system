<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $items;
    protected $consolidated;

    public function __construct($items, $consolidated = null)
    {
        $this->items = $items;
        $this->consolidated = $consolidated;
    }

    public function collection()
    {
        $rows = collect($this->items)->map(function ($item) {
            return $this->map($item);
        });

        if ($this->consolidated) {
            $rows->push([
                'TOTAL CONSOLIDADO DE SALDOS',
                '',
                '',
                null,
                null,
                null,
                null,
                null,
                null,
                (int) ($this->consolidated->total_stock ?? 0),
                '',
                round($this->consolidated->total_value ?? 0, 2),
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No.',
            'FECHA',
            'DESCRIPCIÓN',
            'ENTRADAS - UNIDADES',
            'ENTRADAS - C.U.',
            'ENTRADAS - C.T.',
            'SALIDAS - UNIDADES',
            'SALIDAS - C.U.',
            'SALIDAS - C.T.',
            'EXISTENCIAS - UNIDADES',
            'EXISTENCIAS - C.U.',
            'EXISTENCIAS - C.T.',
        ];
    }

    public function map($item): array
    {
        return [
            $item->line_number,
            $item->created_at->format('d/m/Y'),
            $this->typeLabel($item->type),
            $item->entry_qty ?? null,
            $item->entry_cost !== null ? round($item->entry_cost, 4) : null,
            $item->entry_total !== null ? round($item->entry_total, 2) : null,
            $item->exit_qty ?? null,
            $item->exit_cost !== null ? round($item->exit_cost, 4) : null,
            $item->exit_total !== null ? round($item->exit_total, 2) : null,
            $item->balance_qty,
            $item->balance_cost !== null ? round($item->balance_cost, 4) : null,
            $item->balance_total !== null ? round($item->balance_total, 2) : null,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function typeLabel($type)
    {
        return [
            'entry' => 'COMPRA',
            'exit' => 'SALIDA',
            'technician_out' => 'SALIDA A TÉCNICO',
            'technician_return' => 'DEVOLUCIÓN TÉCNICO',
            'damage' => 'DAÑADO',
            'return_to_supplier' => 'DEV. PROVEEDOR',
            'requisition_out' => 'REQUISICIÓN',
            'branch_allocation' => 'REPARTICIÓN',
        ][$type] ?? strtoupper((string) $type);
    }
}
