<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Outbound\Http\Adapter;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\Adapter\HttpAiClientPort;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\HttpClientPort;
use Illuminate\Http\Client\Response;

class AiHttpClient implements HttpAiClientPort
{
	public function __construct(protected HttpClientPort $client)
	{}

	public function aiRequest(
        string $url,
        string $contents,
        string $mimeType,
        string $field,
        bool $verifySsl,
        string $hostHeader,
        ?string $filename = null,
        ?string &$error = null
    ): ?Response
	{
		return $this->client->post(
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
