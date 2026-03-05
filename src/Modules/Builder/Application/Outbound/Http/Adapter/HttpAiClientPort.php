<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\Adapter;

use Illuminate\Http\Client\Response;

interface HttpAiClientPort
{
    public function aiRequest(
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
