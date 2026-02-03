<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeJsonResource;
use Illuminate\Http\Request;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume; // adjust to your model
use Barryvdh\DomPDF\Facade\Pdf; // adjust if your facade differs
use Illuminate\Support\Facades\Http;

class PdfPreviewController extends Controller
{
//    public function show(Request $request, int $resumeId)
//    {
//        // Load your resume (adjust query as needed)
//        $resume = Resume::query()->findOrFail($resumeId)->loadMissing(Resume::relationships());
//
//		$resumeArray = (new ResumeJsonResource($resume))->resolve();
//
//        $templateView = 'builder::resume';
//
//        $pdf = Pdf::loadView($templateView, [
//                'resume' => $resumeArray,
//            ])
//            ->setPaper('a4', 'portrait');
//
//        // Stream inline (browser preview)
//        return $pdf->stream("resume-{$resumeId}.pdf", [
//            'Attachment' => false, // IMPORTANT: inline preview
//        ]);
//    }

	// Put your allowed templates here (slugs)
    private array $allowedTemplates = [
        'moderno-one',
		'simple-one',
        // add more slugs...
    ];

    public function show(Request $request, $resume, ResumeRepository $resumeRepo)
    {
        // $resume might be route-model bound or ID; adapt as needed.
        // If it's an ID, fetch it here.
        // Example:
        // $resumeModel = Resume::with(...)->findOrFail($resume);

        $resumeModel = is_object($resume) ? $resume : $this->loadResume($resume, $resumeRepo);

        // 1) Decide template
        $template = (string) $request->query('template', '');
        if ($template === '') {
            $template = (string) ($resumeModel->template?->path ?? '');
        } else {
			$template = "templates.{$template}";
		}
        // 2) Validate template (critical)
//        abort_unless(in_array($template, $this->allowedTemplates, true), 404);

        // 3) Build resume array payload for the Blade template
        // Adapt depending on how you store it
        $resumeData = (new ResumeJsonResource($resumeModel))->resolve();
        // OR: $resumeData = $resumeModel->payload; etc.

        // 4) Render Blade => HTML
        $view = "builder::$template";
        $html = view($view, ['resume' => $resumeData])->render();

        // 5) Call pdf-service
        $pdfService = rtrim(config('services.pdf.url'), '/');

        $resp = Http::timeout(60)
            ->accept('application/pdf')
            ->post($pdfService . '/render', [
                'html' => $html,
                'pdfOptions' => [
                    'format' => 'A4',
                    'printBackground' => true,
                    // margins optional:
                    // 'margin' => ['top'=>'0.5in','right'=>'0.5in','bottom'=>'0.5in','left'=>'0.5in'],
                ],
            ]);

        if (!$resp->successful()) {
            abort(500, 'PDF render failed: ' . $resp->body());
        }

        return response($resp->body(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="resume-preview.pdf"');
    }

    private function loadResume(int $id, ResumeRepository $resume)
    {
        // Implement for your app
         return $resume->find($id);

        throw new \RuntimeException('Implement loadResume()');
    }
}
