<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\OpenAiResumeDataStructure;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Concerns\InteractsWithAiHttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

class ResumeUploadController extends Controller
{
    use InteractsWithAiHttpClient;

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);
		// ----------------------------------------------------------------------------
		// @TODO: PLEASE DELETE AFTER TEST
		// ----------------------------------------------------------------------------
//		$storage = \Storage::disk('local')->path('resumes/parsed_resume.json');
//		$json = json_decode(file_get_contents($storage), true);
//		$userId = $request->user()->id;
//		$resume = OpenAiResumeDataStructure::make()->upsert($userId, $json, templateId: 1, colorSchemeId: 1);
//		return response()->json([
//			'data' => $resume,
//			'resume' => $resume,
//		]);
		// ----------------------------------------------------------------------------
		// @TODO: PLEASE DELETE AFTER TEST
		// ----------------------------------------------------------------------------

        $file = $validated['resume_file'];
        $url = $this->resumeAnalyzerStructuredDataUrl();
        $verifySsl = (bool) config('services.ai.verify_ssl', true);
        $hostHeader = trim((string) config('services.ai.host', ''));

        $contents = $file->get();
        $filename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $lastError = null;

        $response = $this->sendAnalyzeRequest($url, $contents, $filename, $mimeType, 'file', $verifySsl, $hostHeader, $lastError);

        if (!$response || !$response->successful()) {
            $response = $this->sendAnalyzeRequest($url, $contents, $filename, $mimeType, 'resume', $verifySsl, $hostHeader, $lastError);
        }

        if (!$response || !$response->successful()) {
            $response = $this->sendAnalyzeRequest($url, $contents, $filename, $mimeType, 'resume_file', $verifySsl, $hostHeader, $lastError);
        }

        if (!$response || !$response->successful()) {
            return response()->json([
                'message' => 'Unable to analyze the uploaded resume at this time.',
                'upstream_url' => $url,
                'upstream_status' => $response?->status(),
                'upstream_body' => $response ? ($response->json() ?? $response->body()) : null,
                'upstream_error' => $lastError,
            ], 502);
        }
		$userId = $request->user()->id;
		$resume = OpenAiResumeDataStructure::make()->upsert($userId, $response->json(), templateId: 1, colorSchemeId: 1);

        return response()->json([
            'data' => $response->json(),
			'resume' => $resume,
        ]);
    }

    private function sendAnalyzeRequest(
        string $url,
        string $contents,
        string $filename,
        string $mimeType,
        string $field,
        bool $verifySsl,
        string $hostHeader,
        ?string &$error = null
    ): ?Response {
        return $this->aiHttpPost(
            url: $url,
            contents: $contents,
            mimeType: $mimeType,
            field: $field,
            verifySsl: $verifySsl,
            hostHeader: $hostHeader,
            filename: $filename,
            error: $error
        );
    }
}
