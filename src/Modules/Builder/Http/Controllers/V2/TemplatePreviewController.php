<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ColorSchemeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeJsonResource;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Http\Request;

class TemplatePreviewController extends Controller
{
    public function show(
        Request $request,
        int $resume, // <-- route param {resume}
        ResumeRepository $resumes,
        TemplatesRepository $templates,
		ColorSchemeRepository $colorSchemes
    ) {
        $templateSlug = $request->string('template', 'echelon')->toString();
        $colorSchemeId  = $request->input('colorScheme', '1');
		$colorSchemeId = $colorSchemeId === 'teal' ? 1 : $colorSchemeId;

        // 1) Get resume model using repository
        // choose find() or getByKey() depending on your auth needs for preview
        $resumeModel = $resumes->getByKey($resume); // returns Resume

        // 2) Get template record from DB (active templates)
        $templateModel = $templates->all()->firstWhere('slug', $templateSlug);
		// Color Scheme
		$colorSchemePrimary = $colorSchemes->getPrimary($colorSchemeId);

        // fallback if slug not found or inactive
        if (!$templateModel) {
            $templateModel = $templates->all()->firstWhere('slug', 'moderno-one');
        }

        // 3) Convert resume to the array shape your blade expects
        // If you have a Resource that produces JSON Resume structure, use that instead.
        $resumeArray = (new ResumeJsonResource($resumeModel))->resolve();

        // 4) Provide blade include path based on DB
        // e.g. store "templates.moderno-one" in DB OR store "moderno-one" and prefix it here.
        $bladePath = $templateModel?->blade_path
            ?? 'templates.' . ($templateModel?->slug ?? 'moderno-one');

        $resumeArray['template'] = [
            'path' => $bladePath,
        ];

        return response()
            ->view('builder::resume', [
                'resume' => $resumeArray,
                'previewColorScheme' => $colorSchemePrimary,
            ])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
