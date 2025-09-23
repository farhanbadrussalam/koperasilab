<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfRenderService
{
    /**
     * $vars = ["NAMA" => "Farhan", "NO_VA" => "8902...", ...]
     */
    public function renderHtmlWithVars(string $html, array $vars): string
    {
        // Ganti {{VAR}} ke nilai
        return preg_replace_callback('/\{\{\s*([A-Z0-9_]+)\s*\}\}/', function($m) use ($vars) {
            $key = $m[1] ?? '';
            return $vars[$key] ?? $m[0];
        }, $html);
    }

    public function streamPdf(array $payload)
    {
        // payload: header_html, body_html, footer_html, paper_size, orientation
        $pdf = Pdf::loadView('pdf.master_document', $payload)
            ->setPaper($payload['paper_size'] ?? 'A4', $payload['orientation'] ?? 'portrait');

        return $pdf->stream();
    }
}
