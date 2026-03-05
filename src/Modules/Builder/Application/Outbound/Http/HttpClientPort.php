<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http;

use Illuminate\Http\Client\Response;

interface HttpClientPort
{
	public function post(
        string $url,
        string $contents,
        string $mimeType,
        string $field,
        bool $verifySsl,
        string $hostHeader,
		?string $filename = null,
        ?string &$error = null
    ): ?Response;
}
