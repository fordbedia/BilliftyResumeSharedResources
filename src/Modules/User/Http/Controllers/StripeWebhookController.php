<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases\HandleStripeWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController
{
    public function handle(Request $request, HandleStripeWebhook $useCase)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook invalid signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        }

        try {
            $useCase->handle($event);
        } catch (\Throwable $e) {
            // IMPORTANT: In production you might want to return 500 so Stripe retries.
            // In local dev, logging + returning 200 can be less noisy.
            Log::error('Stripe webhook failed', [
                'type' => $event->type ?? null,
                'id' => $event->id ?? null,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['received' => true, 'error' => true], 200);
        }

        return response()->json(['received' => true]);
    }
}
