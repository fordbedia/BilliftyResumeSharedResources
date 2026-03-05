<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Outbound\Http\Facades;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\Adapter\HttpAiClientPort;
use Illuminate\Support\Facades\Facade;

class AiHttpClientFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HttpAiClientPort::class;
    }
}

