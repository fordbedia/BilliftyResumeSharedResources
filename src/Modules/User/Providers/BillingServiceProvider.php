<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Providers;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts\StripeBilling;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Billing\Stripe\StripeBillingAdapter;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class BillingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });

        $this->app->bind(StripeBilling::class, StripeBillingAdapter::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
