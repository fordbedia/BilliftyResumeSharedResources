<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Providers;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserDataExportRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\GoogleAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\LinkedInAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\Social\SocialiteGoogleAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\Social\SocialiteLinkedInAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\LaravelPassportTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository\EloquentUserDataExportRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository\EloquentUserRepository;
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
		$this->app->bind(GoogleAuthProvider::class, SocialiteGoogleAuthProvider::class);
		$this->app->bind(LinkedInAuthProvider::class, SocialiteLinkedInAuthProvider::class);
		$this->app->bind(UserDataExportRepository::class, EloquentUserDataExportRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
