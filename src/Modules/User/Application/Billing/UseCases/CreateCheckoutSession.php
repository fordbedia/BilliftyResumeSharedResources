<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts\StripeBilling;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;

class CreateCheckoutSession
{
    public function __construct(
        private readonly StripeBilling $stripe,
        private readonly UserRepository $users,
    ) {}

    public function handle(int $userId, string $interval = 'monthly'): string
	{
		$user = $this->users->find($userId);

		if (!$user) {
			throw new \RuntimeException('User not found.');
		}

		// Resolve price ID based on interval
		$priceId = match ($interval) {
			'yearly'  => env('STRIPE_PRO_PRICE_YEARLY'),
			default   => env('STRIPE_PRO_PRICE_MONTHLY'),
		};

		if (!$priceId) {
			throw new \RuntimeException('Stripe price ID not configured.');
		}

		// Ensure Stripe customer exists
		if (empty($user->stripe_customer_id)) {
			$customerId = $this->stripe->createCustomer([
				'email' => $user->email,
				'name'  => $user->name ?? null,
				'metadata' => ['user_id' => (string) $user->id],
			]);

			$this->users->update([
				'stripe_customer_id' => $customerId,
			], $user->id);

			$user->stripe_customer_id = $customerId;
		}

		$result = $this->stripe->createSubscriptionCheckoutSession([
			'mode' => 'subscription',
			'customer' => $user->stripe_customer_id,
			'line_items' => [[
				'price' => $priceId,
				'quantity' => 1,
			]],
			'success_url' => env('STRIPE_SUCCESS_URL'),
			'cancel_url'  => env('STRIPE_CANCEL_URL'),
			'client_reference_id' => (string) $user->id,
			'metadata' => [
				'user_id' => (string) $user->id,
				'plan' => 'pro',
				'interval' => $interval,
			],
		]);

		return $result['url'];
	}
}