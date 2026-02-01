<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateResumePdfAction
{
    /**
     * @param  array  $resume  JSON-Resume shaped array
     * @param  string $templateView e.g. 'builder::templates.edbedia-green'
     * @param  string $disk e.g. 'public' or 's3'
     * @return array{path:string, url:?string}
     */
    public function handle(
        array $resume,
        string $templateView = 'builder::resume',
        string $disk = 'public'
    ): array {
        // 1) Render Blade -> HTML
        $html = view($templateView, ['resume' => $resume])->render();

        // 2) Call pdf-service
        $pdfService = rtrim(config('services.pdf.url', env('PDF_SERVICE_URL', '')), '/');
        if ($pdfService === '') {
            throw new \RuntimeException('PDF_SERVICE_URL (services.pdf.url) is not configured.');
        }

        try {
            $resp = Http::timeout(90)
                ->retry(2, 250) // small retry for transient chromium warm-up
                ->accept('application/pdf')
                ->asJson()
                ->post($pdfService . '/render', [
                    'html' => $html,
                    'pdfOptions' => [
                        'format' => 'A4',
                        'printBackground' => true,
                        // Optional margins:
                        // 'margin' => ['top'=>'0.5in','right'=>'0.5in','bottom'=>'0.5in','left'=>'0.5in'],
                    ],
                ]);

            if (!$resp->successful()) {
                Log::error('pdf-service failed', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                    'pdf_url' => $pdfService . '/render',
                    'templateView' => $templateView,
                ]);

                throw new \RuntimeException('PDF render failed (pdf-service).');
            }

            $bytes = $resp->body();
            if (!is_string($bytes) || $bytes === '') {
                throw new \RuntimeException('PDF render failed: empty response body.');
            }
        } catch (\Throwable $e) {
            Log::error('pdf-service exception', [
                'message' => $e->getMessage(),
                'templateView' => $templateView,
            ]);

            // OPTIONAL FALLBACK TO DOMPDF (uncomment if you want a backup engine)
            /*
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($templateView, ['resume' => $resume])
                ->setOption('isRemoteEnabled', true)
                ->setOption('isHtml5ParserEnabled', true)
                ->setPaper('a4', 'portrait');
            $bytes = $pdf->output();
            */

            throw $e;
        }

        // 3) Save bytes
        $path = 'resume_pdfs/'
            . now()->format('Y/n')
            . '/resume_' . Str::random(20) . '.pdf';

        Storage::disk($disk)->put($path, $bytes, [
            'visibility' => 'public',
            'ContentType' => 'application/pdf',
        ]);

        // If you want URL, you can add:
        // $url = Storage::disk($disk)->url($path);
        // return ['path' => $path, 'url' => $url];

        return ['path' => $path];
    }
}
