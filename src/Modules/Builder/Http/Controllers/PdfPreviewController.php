<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ColorSchemeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeJsonResource;
use Illuminate\Http\Request;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume; // adjust to your model
use Barryvdh\DomPDF\Facade\Pdf; // adjust if your facade differs
use Illuminate\Support\Facades\Http;

class PdfPreviewController extends Controller
{
	/**
	 * @param Request $request
	 * @param $resume
	 * @param ResumeRepository $resumeRepo
	 * @param ColorSchemeRepository $colorSchemeRepo
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
	 * @throws \Illuminate\Http\Client\ConnectionException
	 */
    public function show(Request $request, $resume, ResumeRepository $resumeRepo, ColorSchemeRepository $colorSchemeRepo)
    {
        $resumeModel = is_object($resume) ? $resume : $this->loadResume($resume, $resumeRepo);

        // 1) Decide template
        $template = (string) $request->query('template', '');
        if ($template === '') {
            $template = (string) ($resumeModel->template?->path ?? '');
        } else {
			$template = "templates.{$template}";
		}
		// 2 For Preview Color scheme
		$previewColorScheme = null;
		$colorSchemeId = $this->resolveColorSchemeId($request->query('colorScheme'));
		if ($colorSchemeId) {
			$previewColorScheme = $colorSchemeRepo->getPrimary($colorSchemeId, $resumeModel->color_scheme_id);
		}

        // 3) Build resume array payload for the Blade template
        // Adapt depending on how you store it
        $resumeData = (new ResumeJsonResource($resumeModel))->resolve();
        // OR: $resumeData = $resumeModel->payload; etc.

        // 4) Render Blade => HTML via wrapper
        $view = "builder::resume";
        $html = view($view, [
			'resume' => $resumeData,
			'previewColorScheme' => $previewColorScheme,
			'templatePath' => $template,
		])->render();

        // 5) Call pdf-service
        $pdfService = rtrim(config('services.pdf.url'), '/');

        $resp = Http::retry(3, 200, throw: false)
			->timeout(60)
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
         return $resume->getByKey($id);

        throw new \RuntimeException('Implement loadResume()');
    }

	private function resolveColorSchemeId(mixed $colorScheme): ?int
	{
		if (is_numeric($colorScheme)) {
			return (int) $colorScheme;
		}

		if (!is_string($colorScheme) || trim($colorScheme) === '') {
			return null;
		}

		return match (strtolower(trim($colorScheme))) {
			'teal' => 1,
			'blue' => 2,
			'indigo' => 3,
			'purple' => 4,
			'pink' => 5,
			'rose' => 6,
			'red' => 7,
			'orange' => 8,
			'amber' => 9,
			'yellow' => 10,
			'lime' => 11,
			'green' => 12,
			'emerald' => 13,
			'cyan' => 14,
			'slate' => 15,
			'gray' => 16,
			'zinc' => 17,
			'stone' => 18,
			default => null,
		};
	}
}
