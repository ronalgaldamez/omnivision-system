<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contrato #' . $this->contract->contract_digital_code . ' — Omnivisión',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract',
        );
    }

    /**
     * Adjunta el PDF del contrato si ya fue generado.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        $pdfService = app(\App\Services\ContractPdfService::class);

        if ($pdfService->hasPdf($this->contract)) {
            $attachments[] = Attachment::fromStorageDisk(
                'public',
                $this->contract->signed_pdf_path
            )->as('contrato-' . $this->contract->contract_digital_code . '.pdf');
        }

        return $attachments;
    }
}
