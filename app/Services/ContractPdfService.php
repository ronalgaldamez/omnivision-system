<?php

namespace App\Services;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContractPdfService
{
    /**
     * Genera el PDF del contrato y lo guarda en disco.
     * Retorna la ruta relativa del archivo.
     */
    public function generate(Contract $contract): string
    {
        $data = $this->getTemplateData($contract);

        $pdf = Pdf::loadView('pdf.contract', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = 'contracts/' . $contract->contract_digital_code . '.pdf';

        $pdfContent = $pdf->output();
        Storage::disk('public')->put($filename, $pdfContent);

        // Actualizar la ruta en el contrato
        $contract->update(['signed_pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Genera el PDF para vista previa (sin guardar).
     */
    public function preview(Contract $contract): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getTemplateData($contract);

        $pdf = Pdf::loadView('pdf.contract', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    /**
     * Obtiene los datos necesarios para el template del PDF.
     */
    public function getTemplateData(Contract $contract): array
    {
        $contract->loadMissing([
            'client',
            'plan',
            'zone',
            'signatures',
            'documents',
            'creator',
        ]);

        $signatureService = app(ContractSignatureService::class);

        $clientSignature = $signatureService->getClientSignatureForPdf($contract);
        $salesRepSignature = $signatureService->getSalesRepSignatureForPdf($contract);

        return [
            'contract' => $contract,
            'client' => $contract->client,
            'plan' => $contract->plan,
            'zone' => $contract->zone,
            'clientSignature' => $clientSignature,
            'salesRepSignature' => $salesRepSignature,
            'creator' => $contract->creator,
            'documents' => $contract->documents,
            'companyName' => config('app.name', 'Omnivisión Sistemas'),
            'companyAddress' => setting('company_address', 'San Salvador, El Salvador'),
            'companyPhone' => setting('company_phone', ''),
        ];
    }

    /**
     * Obtiene la URL pública del PDF firmado.
     */
    public function getPdfUrl(Contract $contract): ?string
    {
        if (! $contract->signed_pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($contract->signed_pdf_path);
    }

    /**
     * Verifica si el contrato tiene PDF generado.
     */
    public function hasPdf(Contract $contract): bool
    {
        return ! empty($contract->signed_pdf_path)
            && Storage::disk('public')->exists($contract->signed_pdf_path);
    }
}
