<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Contrato {{ $contract->contract_digital_code }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            color: #1e40af;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13pt;
            color: #2563eb;
            margin: 0;
        }
        .header .code {
            font-size: 11pt;
            color: #6b7280;
            margin-top: 5px;
            letter-spacing: 1px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .info-grid .label {
            font-weight: bold;
            color: #6b7280;
            width: 140px;
            font-size: 9pt;
        }
        .info-grid .value {
            color: #1a1a1a;
        }
        .plan-box {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px;
            margin: 10px 0;
        }
        .plan-box .plan-name {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
        }
        .plan-box .plan-detail {
            font-size: 9pt;
            color: #4b5563;
        }
        .plan-box .plan-price {
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
            text-align: right;
        }
        .terms {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 12px;
            font-size: 8.5pt;
            color: #4b5563;
            max-height: 200px;
            overflow: hidden;
        }
        .signatures {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 0 2%;
            vertical-align: top;
        }
        .signature-box .sig-image {
            max-width: 200px;
            max-height: 60px;
            margin: 5px auto;
            display: block;
        }
        .signature-box .sig-line {
            border-top: 1px solid #1a1a1a;
            margin: 5px 0;
        }
        .signature-box .sig-label {
            font-size: 8pt;
            color: #6b7280;
        }
        .signature-box .sig-name {
            font-size: 9pt;
            font-weight: bold;
            color: #1a1a1a;
        }
        .footer {
            text-align: center;
            font-size: 7.5pt;
            color: #9ca3af;
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: bold;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .doc-list {
            font-size: 8.5pt;
            color: #4b5563;
            list-style: none;
            padding: 0;
        }
        .doc-list li {
            padding: 2px 0;
        }
        .doc-list li::before {
            content: "✓ ";
            color: #059669;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>CONTRATO DE SERVICIOS</h2>
        <div class="code">No. {{ $contract->contract_digital_code }}</div>
    </div>

    {{-- DATOS DEL CLIENTE --}}
    <div class="section">
        <div class="section-title">Datos del Cliente</div>
        <table class="info-grid">
            <tr>
                <td class="label">Nombre Completo:</td>
                <td class="value">{{ $client->name }}</td>
            </tr>
            @if($client->document_type && $client->document_number)
            <tr>
                <td class="label">{{ strtoupper($client->document_type) }}:</td>
                <td class="value">{{ $client->document_number }}</td>
            </tr>
            @endif
            @if($client->phone)
            <tr>
                <td class="label">Teléfono:</td>
                <td class="value">{{ $client->phone }}</td>
            </tr>
            @endif
            @if($client->email)
            <tr>
                <td class="label">Correo Electrónico:</td>
                <td class="value">{{ $client->email }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Dirección:</td>
                <td class="value">{{ $client->address ?? '—' }}</td>
            </tr>
            @if($contract->installation_address)
            <tr>
                <td class="label">Dirección de Instalación:</td>
                <td class="value">{{ $contract->installation_address }}</td>
            </tr>
            @endif
            @if($zone)
            <tr>
                <td class="label">Zona:</td>
                <td class="value">{{ $zone->name }} ({{ $zone->level }})</td>
            </tr>
            @endif
            <tr>
                <td class="label">Fecha del Contrato:</td>
                <td class="value">{{ $contract->contract_date ? $contract->contract_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- PLAN CONTRATADO --}}
    <div class="section">
        <div class="section-title">Plan Contratado</div>
        @if($plan)
        <div class="plan-box">
            <table style="width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:60%;">
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="plan-detail">
                            Tipo: {{ str_replace('_', ' ', $plan->service_type) }}<br>
                            @if($plan->speed) Velocidad: {{ $plan->speed }}<br> @endif
                            @if($plan->channels) Canales: {{ $plan->channels }}<br> @endif
                        </div>
                    </td>
                    <td style="width:40%; text-align: right; vertical-align: middle;">
                        <div class="plan-price">${{ number_format($contract->price ?? $plan->base_price, 2) }}</div>
                        <div style="font-size: 8pt; color: #6b7280;">Precio mensual</div>
                    </td>
                </tr>
            </table>
        </div>
        @endif
    </div>

    {{-- TÉRMINOS Y CONDICIONES --}}
    <div class="section">
        <div class="section-title">Términos y Condiciones</div>
        <div class="terms">
            {!! $contract->contract_terms ?? '
            <p><strong>Primero:</strong> El proveedor se compromete a instalar y proporcionar el servicio contratado en la dirección indicada por el cliente.</p>
            <p><strong>Segundo:</strong> El cliente se obliga al pago puntual de la tarifa acordada por el servicio, la cual podrá ser ajustada previa notificación con 30 días de anticipación.</p>
            <p><strong>Tercero:</strong> El período mínimo de contratación es de 12 meses. En caso de cancelación anticipada, el cliente deberá pagar una penalidad equivalente al 25% del saldo restante.</p>
            <p><strong>Cuarto:</strong> El proveedor garantiza el servicio con una disponibilidad mínima del 99.5% mensual, excluyendo mantenimientos programados y casos de fuerza mayor.</p>
            <p><strong>Quinto:</strong> El cliente autoriza el uso de sus datos personales únicamente para fines de facturación y soporte técnico, conforme a la Ley de Protección de Datos.</p>
            ' !!}
        </div>
    </div>

    {{-- DOCUMENTACIÓN ADJUNTA --}}
    @if($documents->isNotEmpty())
    <div class="section">
        <div class="section-title">Documentación Adjunta</div>
        <ul class="doc-list">
            @foreach($documents as $doc)
                <li>{{ $doc->typeLabel() }} @if($doc->notes)({{ $doc->notes }})@endif</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- FIRMAS --}}
    <div class="signatures clearfix">
        <div class="section-title">Firmas</div>
        <br>
        <div class="signature-box">
            @if($clientSignature)
                <img src="{{ $clientSignature }}" class="sig-image" alt="Firma del Cliente">
            @else
                <div style="height: 60px;"></div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-label">Firma del Cliente</div>
            <div class="sig-name">{{ $client->name }}</div>
            <div style="font-size: 7pt; color: #9ca3af;">
                {{ $contract->signed_at ? $contract->signed_at->format('d/m/Y h:i A') : '—' }}
            </div>
        </div>

        <div class="signature-box">
            @if($salesRepSignature)
                <img src="{{ $salesRepSignature }}" class="sig-image" alt="Firma del Agente">
            @else
                <div style="height: 60px;"></div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-label">Firma del Agente de Ventas</div>
            <div class="sig-name">{{ $creator->name ?? '—' }}</div>
            <div style="font-size: 7pt; color: #9ca3af;">
                {{ now()->format('d/m/Y h:i A') }}
            </div>
        </div>
    </div>

    {{-- PIE DE PÁGINA --}}
    <div class="footer">
        <p>{{ $companyName }} | {{ $companyAddress }} | {{ $companyPhone }}</p>
        <p>Documento generado electrónicamente el {{ now()->format('d/m/Y \a \l\a\s h:i A') }} | Código: {{ $contract->contract_digital_code }}</p>
        <p>Este documento es válido sin firma autógrafa según la Ley de Firma Electrónica de El Salvador.</p>
    </div>

</body>
</html>
