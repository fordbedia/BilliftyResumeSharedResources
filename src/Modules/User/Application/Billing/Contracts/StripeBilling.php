<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts;

interface StripeBilling
{
    public function createCustomer(array $data): string;

    /** @return array{url:string, session_id:string} */
    public function createSubscriptionCheckoutSession(array $data): array;

    /** @return array{url:string} */
    public function createCustomerPortalSession(array $data): array;
}