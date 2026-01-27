<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeJsonResource;
use Illuminate\Http\Request;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume; // adjust to your model
use Barryvdh\DomPDF\Facade\Pdf; // adjust if your facade differs

class PdfPreviewController extends Controller
{
    public function show(Request $request, int $resumeId)
    {
        // Load your resume (adjust query as needed)
        $resume = Resume::query()->findOrFail($resumeId);

		$resumeArray = (new ResumeJsonResource($resume))->resolve();

        $templateView = 'builder::resume';

        $pdf = Pdf::loadView($templateView, [
                'resume' => $resumeArray,
            ])
            ->setPaper('a4', 'portrait');

        // Stream inline (browser preview)
        return $pdf->stream("resume-{$resumeId}.pdf", [
            'Attachment' => false, // IMPORTANT: inline preview
        ]);
    }
}
