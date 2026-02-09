<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class HandleStripeWebhook
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function handle(Event $event): void
    {
        // Only handle the events we care about
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                return;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpsert($event->data->object);
                return;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                return;


            default:
                // ignore everything else
                return;
        }
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $userId = data_get($session, 'metadata.user_id')
            ?? data_get($session, 'client_reference_id');

        if (!$userId) return;

        $this->users->update([
            'stripe_customer_id'     => data_get($session, 'customer'),
            'stripe_subscription_id' => data_get($session, 'subscription'),
        ], (int) $userId);
    }

	private function billingCycleFromPriceId(?string $priceId): ?string
	{
		if (!$priceId) return null;

		return match ($priceId) {
			config('services.stripe.pro_price_monthly') => 'monthly',
			config('services.stripe.pro_price_yearly')  => 'yearly',
			default => null,
		};
	}

    private function handleSubscriptionUpsert(object $sub): void
	{
		$customerId = (string) data_get($sub, 'customer');
		if (!$customerId) return;

		$user = $this->users->findStripeCustomer($customerId);
		if (!$user) return;

		$status  = (string) data_get($sub, 'status', '');

		// Price id for the first subscription item
		$priceId = data_get($sub, 'items.data.0.price.id');

		// Stripe moved period end to the subscription item in newer API versions
		$periodEndRaw =
			data_get($sub, 'items.data.0.current_period_end')
			?? data_get($sub, 'current_period_end') // fallback (older versions)
			?? data_get($sub, 'cancel_at');         // fallback if present

		$periodEndTs = is_numeric($periodEndRaw) ? (int) $periodEndRaw : null;
		$periodEndAt = $periodEndTs ? now()->setTimestamp($periodEndTs) : null;

		$cancelAtPeriodEnd = (bool) data_get($sub, 'cancel_at_period_end', false);
		$isEntitled = in_array($status, ['active', 'trialing'], true);

		$billingCycle = $this->billingCycleFromPriceId(is_string($priceId) ? $priceId : null);

		$this->users->update([
			'stripe_subscription_id'      => data_get($sub, 'id'),
			'stripe_price_id'             => $priceId,
			'billing_cycle'               => $billingCycle, // ✅ new
			'stripe_status'               => $status,
			'stripe_cancel_at_period_end' => $cancelAtPeriodEnd,
			'stripe_current_period_end'   => $periodEndAt,
			'plan'                        => $isEntitled ? 'pro' : 'free',

			'plan_expires_at' => ($cancelAtPeriodEnd && $periodEndAt) ? $periodEndAt : null,
		], (int) $user->id);
	}


    private function handleSubscriptionDeleted(object $sub): void
	{
		$customerId = (string) data_get($sub, 'customer');
		if (!$customerId) return;

		$user = $this->users->findStripeCustomer($customerId);
		if (!$user) return;

		$this->users->update([
			'stripe_status' => 'canceled',
			'stripe_cancel_at_period_end' => false,
			'stripe_current_period_end' => null,
			'plan' => 'free',
			'plan_expires_at' => null,
		], (int) $user->id);
	}

}