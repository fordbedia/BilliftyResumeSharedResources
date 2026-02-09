<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\Contracts\StripeBilling;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases\CreateCheckoutSession;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases\CreatePortalSession;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Services\StripeSubscriptionInfoService;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function createCheckoutSession(Request $request, CreateCheckoutSession $useCase)
	{
		$interval = $request->input('interval'); // default monthly

		$url = $useCase->handle(
			userId: $request->user()->id,
			interval: $interval
		);

		return response()->json(['url' => $url]);
}

    public function createPortalSession(Request $request, CreatePortalSession $useCase)
    {
        $url = $useCase->handle($request->user()->id);

        return response()->json(['url' => $url]);
    }

	public function subscriptionInfo(Request $request, StripeSubscriptionInfoService $svc)
	{
		$user = $request->user();

		if (!$user->stripe_subscription_id) {
			return response()->json([
				'next_billing_date' => null,
				'payment_method' => null,
			]);
		}

		return response()->json($svc->getSubscriptionInfo($user->stripe_subscription_id));
	}
}