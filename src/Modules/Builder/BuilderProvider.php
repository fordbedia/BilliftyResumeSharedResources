<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Providers\EloquentResumeRepositoryProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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

	public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}