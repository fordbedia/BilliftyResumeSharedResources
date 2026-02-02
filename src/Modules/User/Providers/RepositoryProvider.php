<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Providers;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\LaravelPassportTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository\EloquentUserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
		$this->app->bind(AuthTokenIssuer::class, LaravelPassportTokenIssuer::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
