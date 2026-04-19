<?php

namespace App\Core\Pdf\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic PDF generation (DomPDF + Blade), similar in role to {@see EmailService}.
 * Use from domain services (e.g. InvoicePdfService) or controllers when you need a PDF anywhere.
 */
class PdfService
{
    /**
     * Build a DomPDF instance from a Blade view name and view data.
     *
     * @param  array<string, mixed>  $data
     */
    public function make(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait')
    {
        return Pdf::loadView($view, $data)->setPaper($paper, $orientation);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function download(string $filename, string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait'): Response
    {
        return $this->make($view, $data, $paper, $orientation)
            ->download($this->sanitizeFilename($filename));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function stream(string $filename, string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait'): Response
    {
        return $this->make($view, $data, $paper, $orientation)
            ->stream($this->sanitizeFilename($filename));
    }

    /**
     * Raw PDF bytes (e.g. email attachments, storage).
     *
     * @param  array<string, mixed>  $data
     */
    public function output(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait'): string
    {
        return $this->make($view, $data, $paper, $orientation)->output();
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = trim($filename);
        if ($filename === '') {
            return 'document.pdf';
        }

        $base = pathinfo($filename, PATHINFO_FILENAME);
        $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $base);

        return ($safe !== '' ? $safe : 'document') . '.pdf';
    }
}
