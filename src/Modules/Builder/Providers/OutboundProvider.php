<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Providers;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\Adapter\HttpAiClientPort;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Outbound\Http\HttpClientPort;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Outbound\Http\Adapter\AiHttpClient;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Outbound\Http\HttpClient;
use Illuminate\Support\ServiceProvider;

class OutboundProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(HttpClientPort::class, function () {
            return new HttpClient(
                baseUrl: rtrim((string) config('services.ai.base_url', ''), '/'),
                apiKey: (string) config('services.ai.api_key', ''),
                timeoutSeconds: (int) config('services.ai.timeout', 120),
            );
        });

        $this->app->singleton(HttpAiClientPort::class, function ($app) {
            return new AiHttpClient(
                client: $app->make(HttpClientPort::class),
            );
        });

        $this->app->alias(HttpAiClientPort::class, 'ai.http');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
