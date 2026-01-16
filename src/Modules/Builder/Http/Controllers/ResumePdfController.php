<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Action\BuildResume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Action\EdBediaResume;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ResumePdfController extends Controller
{
    public function download(EdBediaResume $buildResume): Response
	{
		// prevent stray output from contaminating Dompdf
		if (ob_get_length()) {
			ob_end_clean();
		}

		$resume = $buildResume();

		$resumeArray = json_decode(
			json_encode($resume, JSON_THROW_ON_ERROR),
			true,
			512,
			JSON_THROW_ON_ERROR
		);

		$pdf = Pdf::loadView('builder::resume', [
				'resume' => $resumeArray,
			])
			->setPaper('A4', 'portrait');
//			->setOption('isHtml5ParserEnabled', true)
//			->setOption('isRemoteEnabled', false) // turn on only if you embed remote images
//			->setOption('dpi', 96)
//			->setOption('defaultFont', 'DejaVu Sans')
//			->setOption('enable_font_subsetting', true);

		return $pdf->download('resume.pdf');
	}

    public function stream(BuildResume $buildResume): Response
    {
        $resume = $buildResume();
        $resumeArray = json_decode(json_encode($resume, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        return Pdf::loadView('builder::templates.resume', ['resume' => $resumeArray])
            ->setPaper('a4')
            ->stream('resume.pdf');
    }

	public function exportJson(EdBediaResume $buildResume): \Illuminate\Http\Response
	{
		$resume = $buildResume();

		$json = json_encode(
			$resume,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
		);

		Storage::put('resumes/edbedia.json', $json);

		return response("Saved to storage/app/resumes/edbedia.json", 200);
	}

	public function buildFromStrings(Request $request): Response
	{
		// This maps to: storage/app/resumes/edbedia.json
		$path = 'resumes/edbedia.json';

		abort_unless(
			Storage::exists($path),
			404,
			"Resume JSON not found at storage/app/{$path}"
		);

		$json = Storage::get($path);

		// remove UTF-8 BOM if present
		$json = preg_replace('/^\xEF\xBB\xBF/', '', $json);

		// remove control chars except common whitespace (\n \r \t)
		$json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json);

		try {
			$resume = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			abort(422, 'Invalid JSON Resume file (still): ' . $e->getMessage());
		}

		return Pdf::loadView('builder::templates.resume', [
				'resume' => $resume,
			])
			->setPaper('a4')
			->download('EdBedia-Resume.pdf');
	}
}
