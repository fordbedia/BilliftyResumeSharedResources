<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Middleware\DevAutoLogin;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Middleware\EnsureAdmin;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Providers\EloquentResumeRepositoryProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class BuilderProvider extends ServiceProvider
{
	protected array $providers = [
		EloquentResumeRepositoryProvider::class,
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
		// register alias
		$router->aliasMiddleware('builder.admin', EnsureAdmin::class);
		$router->aliasMiddleware('builder.dev-login', DevAutoLogin::class);

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}