<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Services;

use Carbon\CarbonImmutable;
use Stripe\StripeClient;

class StripeSubscriptionInfoService
{
    public function __construct(private readonly StripeClient $stripe) {}

    /**
     * Returns next billing date + payment method summary for a subscription.
     *
     * @return array{
     *   subscription_id: string,
     *   status: string|null,
     *   cancel_at_period_end: bool|null,
     *   next_billing_date: string|null,
     *   current_period_end_ts: int|null,
     *   payment_method: array|null
     * }
     */
    public function getSubscriptionInfo(string $subscriptionId): array
    {
        $sub = $this->stripe->subscriptions->retrieve($subscriptionId, [
            'expand' => [
                'default_payment_method',
                'latest_invoice.payment_intent.payment_method',
                'customer',
            ],
        ]);

        // Stripe moved billing periods to items.data[].current_period_end
        $itemPeriodEnds = [];
        foreach (($sub->items->data ?? []) as $item) {
            if (!empty($item->current_period_end) && is_numeric($item->current_period_end)) {
                $itemPeriodEnds[] = (int) $item->current_period_end;
            }
        }

        $nextTs = !empty($itemPeriodEnds) ? min($itemPeriodEnds) : null;

        $nextBillingDate = $nextTs
            ? CarbonImmutable::createFromTimestampUTC($nextTs)->toIso8601String()
            : null;

        // Payment method fallback chain (same as before)
        $pm =
            $sub->default_payment_method
            ?? ($sub->latest_invoice->payment_intent->payment_method ?? null)
            ?? ($sub->customer->invoice_settings->default_payment_method ?? null);

        $paymentMethodSummary = null;

        if ($pm && isset($pm->card)) {
            $paymentMethodSummary = [
                'id' => $pm->id ?? null,
                'type' => $pm->type ?? 'card',
                'brand' => $pm->card->brand ?? null,
                'last4' => $pm->card->last4 ?? null,
                'exp_month' => $pm->card->exp_month ?? null,
                'exp_year' => $pm->card->exp_year ?? null,
            ];
        } elseif ($pm) {
            $paymentMethodSummary = [
                'id' => $pm->id ?? null,
                'type' => $pm->type ?? null,
            ];
        }

        return [
            'subscription_id' => $sub->id,
            'status' => $sub->status ?? null,
            'cancel_at_period_end' => $sub->cancel_at_period_end ?? null,

            // ✅ now derived from items
            'next_billing_date' => $nextBillingDate,
            'next_billing_ts' => $nextTs,

            'payment_method' => $paymentMethodSummary,
        ];
    }
}