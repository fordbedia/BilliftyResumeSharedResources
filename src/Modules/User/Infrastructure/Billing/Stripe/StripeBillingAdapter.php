<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Billing\Stripe;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts\StripeBilling;
use Carbon\CarbonImmutable;
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

	/**
     * Returns the next billing/renewal date (UTC) as a CarbonImmutable,
     * or null if the user doesn't have an active subscription.
     */
    public function getNextBillingDate(string $stripeSubscriptionId): ?CarbonImmutable
    {
        $sub = $this->stripe->subscriptions->retrieve($stripeSubscriptionId, []);

        // For most subscriptions, Stripe's "current_period_end" is the next renewal boundary.
        $periodEnd = $sub->current_period_end ?? null; // unix timestamp
        if (!$periodEnd) {
            return null;
        }

        // Optional: if you only want to show it for active/trialing
        $status = (string) ($sub->status ?? '');
        if (!in_array($status, ['active', 'trialing', 'past_due', 'unpaid'], true)) {
            return null;
        }

        return CarbonImmutable::createFromTimestampUTC((int) $periodEnd);
    }
}