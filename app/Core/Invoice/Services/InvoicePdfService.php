<?php

namespace App\Core\Invoice\Services;

use App\Core\Pdf\Services\PdfService;
use App\Models\Invoice;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function __construct(
        private PdfService $pdf
    ) {}

    /**
     * Build a PDF document for the given invoice using the invoicepdf Blade template.
     */
    public function makePdf(Invoice $invoice)
    {
        $this->loadInvoiceForPdf($invoice);

        return $this->pdf->make('invoice.invoicepdf', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Stream the PDF in the browser.
     */
    public function stream(Invoice $invoice): Response
    {
        $this->loadInvoiceForPdf($invoice);

        return $this->pdf->stream($this->filename($invoice), 'invoice.invoicepdf', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Force download of the PDF file.
     */
    public function download(Invoice $invoice): Response
    {
        $this->loadInvoiceForPdf($invoice);

        return $this->pdf->download($this->filename($invoice), 'invoice.invoicepdf', [
            'invoice' => $invoice,
        ]);
    }

    private function loadInvoiceForPdf(Invoice $invoice): void
    {
        $invoice->loadMissing([
            'organization',
            'outlet',
            'vendor',
            'department',
            'details' => fn ($q) => $q->orderBy('id'),
            'statusHistories' => fn ($q) => $q->latest('id'),
            'statusHistories.user',
        ]);
    }

    private function filename(Invoice $invoice): string
    {
        $base = $invoice->invoice_number ?: ('invoice-' . $invoice->id);

        return preg_replace('/[^a-zA-Z0-9._-]+/', '_', $base) . '.pdf';
    }
}
