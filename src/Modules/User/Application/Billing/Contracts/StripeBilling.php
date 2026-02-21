<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts;

interface StripeBilling
{
    public function createCustomer(array $data): string;

    /** @return array{url:string, session_id:string} */
    public function createSubscriptionCheckoutSession(array $data): array;

    /** @return array{url:string} */
    public function createCustomerPortalSession(array $data): array;

    /**
     * Cancel a Stripe subscription right away.
     * Returns true when a live subscription was canceled in this call,
     * false when it was already non-active/canceled.
     */
    public function cancelSubscriptionImmediately(string $subscriptionId): bool;
}
