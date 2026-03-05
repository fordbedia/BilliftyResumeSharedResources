<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Outbound\Http;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\HttpClientPort;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class HttpClient implements HttpClientPort
{
	public function __construct(
		public string $baseUrl,
        public string $apiKey,
        public int $timeoutSeconds = 30,
	) {}

	public function post(string $url, string $contents, string $mimeType, string $field, bool $verifySsl, string $hostHeader, ?string $filename = null, ?string &$error = null): ?Response
	{
		try {
            $request = $this->requestBuilder($verifySsl, $hostHeader);

			if ($filename !== null) {
				$request = $request->attach($field, $contents, $filename, [
                    'Content-Type' => $mimeType,
                ]);
                return $request->post($this->buildUrl($url));
			}

            // Non-upload requests (AI enhance) send raw content payload.
            return $request
                ->withBody($contents, $mimeType)
                ->post($this->buildUrl($url));
        } catch (Throwable $e) {
            $error = $e->getMessage();
            return null;
        }
	}

    private function requestBuilder(bool $verifySsl, string $hostHeader)
    {
        $request = Http::timeout($this->timeoutSeconds)
            ->retry(1, 200, throw: false)
            ->acceptJson()
            ->withOptions([
                'verify' => $verifySsl,
            ]);

        if ($this->apiKey !== '') {
            $request = $request->withToken($this->apiKey);
        }

        if ($hostHeader !== '') {
            $request = $request->withHeaders([
                'Host' => $hostHeader,
            ]);
        }

        return $request;
    }

    private function buildUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
