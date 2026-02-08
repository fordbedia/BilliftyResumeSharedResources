<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Billing\Stripe;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts\StripeBilling;
use Stripe\StripeClient;

class StripeBillingAdapter implements StripeBilling
{
    public function __construct(private readonly StripeClient $stripe) {}

    public function createCustomer(array $data): string
    {
        $customer = $this->stripe->customers->create($data);
        return $customer->id;
    }

    public function createSubscriptionCheckoutSession(array $data): array
    {
        $session = $this->stripe->checkout->sessions->create($data);

        return [
            'url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    public function createCustomerPortalSession(array $data): array
    {
        $portal = $this->stripe->billingPortal->sessions->create($data);

        return [
            'url' => $portal->url,
        ];
    }
}