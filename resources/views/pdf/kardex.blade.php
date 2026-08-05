<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex - {{ $product->sku }}</title>
    <style>
        * { font-family: Arial, Helvetica, sans-serif; }
        body { margin: 0; padding: 20px; color: #111827; font-size: 9px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2563eb; padding-bottom: 8px; margin-bottom: 12px; }
        .brand { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .sub { color: #6b7280; font-size: 9px; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 14px; color: #1e3a8a; }
        .title p { margin: 2px 0 0; color: #6b7280; }
        .meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 8px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; }
        .meta strong { font-size: 12px; }
        .meta span { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 5px; text-align: center; }
        th { background: #f3f4f6; font-size: 8px; }
        .group-ent { background: #eff6ff; }
        .group-sal { background: #fef2f2; }
        .group-exi { background: #f9fafb; }
        .num { text-align: right; }
        .left { text-align: left; }
        .desc { font-weight: bold; }
        .badge { font-size: 7px; padding: 1px 5px; border-radius: 8px; font-weight: bold; }
        .total-row td { font-weight: bold; background: #eff6ff; border-top: 2px solid #2563eb; }
        .footer { margin-top: 12px; font-size: 8px; color: #6b7280; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        @php
            $reportLogoPath = setting('logo_reportes');
            $reportLogoData = null;
            if ($reportLogoPath) {
                $full = storage_path('app/public/' . $reportLogoPath);
                if (file_exists($full)) {
                    $mime = mime_content_type($full) ?: 'image/png';
                    $reportLogoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
                }
            }
        @endphp
        @if($reportLogoData)
            <img src="{{ $reportLogoData }}" style="max-height:48px;max-width:200px;">
        @else
            <div>
                <div class="brand">OMNIVISIÓN</div>
                <div class="sub">Sistema de Control de Inventario</div>
            </div>
        @endif
        <div class="title">
            <h1>TARJETA DE CONTROL DE INVENTARIO (KARDEX)</h1>
            <p>Método: Costo Promedio Ponderado</p>
        </div>
    </div>

    <div class="meta">
        <div>
            <strong>{{ $product->name }}</strong>
            <br><span>SKU: {{ $product->sku }} · Categoría: {{ $product->category->name ?? 'Sin categoría' }}</span>
        </div>
        <div class="right" style="text-align:right">
            <span>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:3%">No.</th>
                <th rowspan="2" style="width:8%">FECHA</th>
                <th rowspan="2" style="width:20%">DESCRIPCIÓN</th>
                <th colspan="3" class="group-ent">ENTRADAS</th>
                <th colspan="3" class="group-sal">SALIDAS</th>
                <th colspan="3" class="group-exi">EXISTENCIAS</th>
            </tr>
            <tr>
                <th class="group-ent">UNIDADES</th>
                <th class="group-ent">C.U.</th>
                <th class="group-ent">C.T.</th>
                <th class="group-sal">UNIDADES</th>
                <th class="group-sal">C.U.</th>
                <th class="group-sal">C.T.</th>
                <th class="group-exi">UNIDADES</th>
                <th class="group-exi">C.U.</th>
                <th class="group-exi">C.T.</th>
            </tr>
        </thead>
        <tbody>
            @php
                $typeLabels = [
                    'entry' => 'COMPRA',
                    'exit' => 'SALIDA',
                    'technician_out' => 'SALIDA A TÉCNICO',
                    'technician_return' => 'DEVOLUCIÓN TÉCNICO',
                    'damage' => 'DAÑADO',
                    'return_to_supplier' => 'DEV. PROVEEDOR',
                    'requisition_out' => 'REQUISICIÓN',
                    'branch_allocation' => 'REPARTICIÓN',
                ];
            @endphp
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->line_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td class="left">
                        <span class="badge" style="background:#dcfce7;color:#166534">{{ $typeLabels[$item->type] ?? strtoupper($item->type) }}</span>
                    </td>
                    <td class="num">{{ $item->entry_qty ?? '' }}</td>
                    <td class="num">{{ $item->entry_cost !== null ? number_format($item->entry_cost, 4) : '' }}</td>
                    <td class="num">{{ $item->entry_total !== null ? number_format($item->entry_total, 2) : '' }}</td>
                    <td class="num">{{ $item->exit_qty ?? '' }}</td>
                    <td class="num">{{ $item->exit_cost !== null ? number_format($item->exit_cost, 4) : '' }}</td>
                    <td class="num">{{ $item->exit_total !== null ? number_format($item->exit_total, 2) : '' }}</td>
                    <td class="num">{{ $item->balance_qty }}</td>
                    <td class="num">{{ number_format($item->balance_cost, 4) }}</td>
                    <td class="num">{{ number_format($item->balance_total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="9" style="text-align:left;font-size:9px">TOTAL CONSOLIDADO DE SALDOS</td>
                <td class="num">{{ (int) ($consolidated->total_stock ?? 0) }}</td>
                <td class="num"></td>
                <td class="num">{{ number_format($consolidated->total_value ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Sistema omnivision-system · {{ now()->format('Y') }} · OMNIVISIÓN
    </div>
</body>
</html>
