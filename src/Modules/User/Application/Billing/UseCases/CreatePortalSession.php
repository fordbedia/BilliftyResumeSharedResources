<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts\StripeBilling;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;

class CreatePortalSession
{
    public function __construct(
        private readonly StripeBilling $stripe,
        private readonly UserRepository $users,
    ) {}

    public function handle(int $userId): string
    {
        $user = $this->users->find($userId);

        if (!$user || empty($user->stripe_customer_id)) {
            throw new \RuntimeException('No Stripe customer found. Upgrade first.');
        }

        $result = $this->stripe->createCustomerPortalSession([
            'customer' => $user->stripe_customer_id,
            'return_url' => rtrim(config('app.frontend_url'), '/') . '/dashboard/subscription',
        ]);

        return $result['url'];
    }
}