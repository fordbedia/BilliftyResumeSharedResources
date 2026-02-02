<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Middleware\DevAutoLogin;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Middleware\EnsureAdmin;
use BilliftyResumeSDK\SharedResources\Modules\User\Providers\RepositoryProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class UserProvider extends ServiceProvider
{
	protected array $providers = [
		RepositoryProvider::class,
	];

	protected array $policies = [
		//
    ];

	public function register()
	{
		foreach ($this->providers as $provider) {
			$this->app->register($provider);
		}
	}

	public function boot(Router $router): void
    {

    }
}