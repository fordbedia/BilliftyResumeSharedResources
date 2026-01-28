<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateResumePdfAction
{
    /**
     * @param  array  $resume  JSON-Resume shaped array
     * @param  string $templateView e.g. 'builder::templates.resume'
     * @param  string $disk e.g. 'public' or 's3'
     * @return array{path:string, url:?string}
     */
    public function handle(
        array $resume,
        string $templateView = 'builder::resume',
        string $disk = 'public'
    ): array {
        $pdf = Pdf::loadView($templateView, ['resume' => $resume])
			->setOption('isRemoteEnabled', true)
  			->setOption('isHtml5ParserEnabled', true)
            ->setPaper('a4', 'portrait');

        $bytes = $pdf->output();

        // ONE canonical location
        $path = 'resume_pdfs/'
            . now()->format('Y/n')
            . '/resume_' . Str::random(20) . '.pdf';

        Storage::disk($disk)->put($path, $bytes, [
            'visibility' => 'public',
            'ContentType' => 'application/pdf',
        ]);

        return ['path' => $path];
    }

}