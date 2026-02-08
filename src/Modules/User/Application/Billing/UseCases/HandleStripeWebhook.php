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

    private function handleSubscriptionUpsert(object $sub): void
	{
		$customerId = (string) data_get($sub, 'customer');
		if (!$customerId) return;

		$user = $this->users->findStripeCustomer($customerId);
		if (!$user) return;

		$status  = (string) data_get($sub, 'status', '');
		$priceId = data_get($sub, 'items.data.0.price.id');

		$cancelAtPeriodEnd = (bool) data_get($sub, 'cancel_at_period_end', false);

		// Prefer current_period_end; fallback to cancel_at if present
		$periodEndRaw =
			data_get($sub, 'current_period_end')
			?? data_get($sub, 'cancel_at');

		$periodEndTs = is_numeric($periodEndRaw) ? (int) $periodEndRaw : null;
		$periodEndAt = $periodEndTs ? now()->setTimestamp($periodEndTs) : null;

		$isEntitled = in_array($status, ['active', 'trialing'], true);

		$this->users->update([
			'stripe_subscription_id'      => data_get($sub, 'id'),
			'stripe_price_id'             => $priceId,
			'stripe_status'               => $status,
			'stripe_cancel_at_period_end' => $cancelAtPeriodEnd,
			'stripe_current_period_end'   => $periodEndAt,
			'plan'                        => $isEntitled ? 'pro' : 'free',

			// key line: store expiry when cancel-at-period-end is true
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