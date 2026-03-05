<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Concerns;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\Adapter\HttpAiClientPort;
use Illuminate\Http\Client\Response;

trait InteractsWithAiHttpClient
{
    protected function resumeAnalyzerStructuredDataUrl(): string
    {
        return $this->buildAiUrl(
            'services.resume_analyzer.structured_data_path',
            '/agent/{access_key}/analyze-resume/structured-data'
        );
    }

	protected function enhancementDataUrl(): string
	{
		return $this->buildAiUrl(
            'services.resume_enhance.structured_data_path',
            '/agent/{access_key}/enhance-resume'
        );
	}

	protected function buildAiUrl(string $pathConfigKey, string $defaultPath): string
	{
        $baseUrl = rtrim((string) config('services.ai.base_url', ''), '/');
        $pathTemplate = (string) config($pathConfigKey, $defaultPath);
		$accessKey = trim((string) config('services.ai.access_key', ''));
        $resolvedPath = str_replace('{access_key}', $accessKey, $pathTemplate);
        $path = '/' . ltrim($resolvedPath, '/');

        return $baseUrl . $path;
	}


    protected function aiHttpPost(
        string $url,
        string $contents,
        string $mimeType = 'application/json',
        string $field = 'payload',
        bool $verifySsl = true,
        string $hostHeader = '',
        ?string $filename = null,
        ?string &$error = null
    ): ?Response {
        $client = app(HttpAiClientPort::class);

        return $client->aiRequest(
            $url,
            $contents,
            $mimeType,
            $field,
            $verifySsl,
            $hostHeader,
            $filename,
            $error
        );
    }
}
