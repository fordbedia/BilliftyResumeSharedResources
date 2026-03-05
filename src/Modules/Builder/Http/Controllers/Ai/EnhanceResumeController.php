<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Concerns\InteractsWithAiHttpClient;
use Illuminate\Http\Request;

class EnhanceResumeController extends Controller
{
	use InteractsWithAiHttpClient;

    public function run(Request $request)
	{
		$url = $this->enhancementDataUrl();
        $verifySsl = (bool) config('services.ai.verify_ssl', true);
        $hostHeader = trim((string) config('services.ai.host', ''));
        $draftText = trim((string) $request->input('text', ''));
        if ($draftText === '') {
            return response()->json([
                'message' => 'Text is required for enhancement.',
            ], 422);
        }

        // AI endpoint currently reads request.POST.get("text"), so send as form-encoded body.
        $content = http_build_query([
            'text' => $draftText,
        ]);
        $lastError = null;

		$response = $this->aiHttpPost(
			url: $url,
			contents: $content,
            mimeType: 'application/x-www-form-urlencoded',
            verifySsl: $verifySsl,
            hostHeader: $hostHeader,
            error: $lastError
		);

        if (!$response || !$response->successful()) {
            return response()->json([
                'message' => 'Unable to enhance resume text at this time.',
                'upstream_url' => $url,
                'upstream_status' => $response?->status(),
                'upstream_body' => $response ? ($response->json() ?? $response->body()) : null,
                'upstream_error' => $lastError,
            ], 502);
        }

        return response()->json($response->json() ?? [
            'data' => $response->body(),
        ]);
	}
}
