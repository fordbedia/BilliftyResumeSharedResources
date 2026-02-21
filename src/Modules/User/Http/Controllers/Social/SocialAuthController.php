<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Billing\UseCases\CreateCheckoutSession;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\Social\HandleSocialAuthCallback;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\Social\ResolveSocialAuthRedirect;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Responses\AuthCookieResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class SocialAuthController extends Controller
{
	public function redirect(string $provider, Request $request, ResolveSocialAuthRedirect $useCase): RedirectResponse
	{
		return $useCase->execute($provider, $this->buildCheckoutState($request));
	}

		public function callback(
			string $provider,
			Request $request,
			HandleSocialAuthCallback $useCase,
			AuthCookieResponseFactory $cookieAuth,
			CreateCheckoutSession $checkout
		): RedirectResponse
	{
		$frontend = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');

		try {
			$payload = $useCase->execute($provider);
			$targetUrl = "{$frontend}/dashboard";
			$context = $this->parseCheckoutState((string) $request->query('state', ''));

			if ($context !== null && (($payload['user']['plan'] ?? 'free') === 'free')) {
				try {
					$targetUrl = $checkout->handle(
						userId: (int) ($payload['user']['id'] ?? 0),
						interval: $context['period']
					);
				} catch (Throwable $billingError) {
					report($billingError);
				}
			}

			return $cookieAuth->redirectWithAuthCookie($targetUrl, $payload);
		} catch (Throwable $e) {
			$message = rawurlencode($e->getMessage() ?: 'Social sign-in failed.');
			return redirect()->away("{$frontend}/auth?social_error={$message}");
		}
	}

	private function buildCheckoutState(Request $request): ?string
	{
		$plan = strtolower(trim((string) $request->query('plan', '')));
		$period = strtolower(trim((string) $request->query('period', '')));
		$proceed = filter_var($request->query('proceed', false), FILTER_VALIDATE_BOOL);

		if (!($plan === 'pro' && in_array($period, ['monthly', 'yearly'], true) && $proceed)) {
			return null;
		}

		$payload = json_encode([
			'plan' => $plan,
			'period' => $period,
			'proceed' => true,
		], JSON_UNESCAPED_SLASHES);

		if ($payload === false) {
			return null;
		}

		return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
	}

	private function parseCheckoutState(string $encoded): ?array
	{
		if ($encoded === '') {
			return null;
		}

		$padded = strtr($encoded, '-_', '+/');
		$remainder = strlen($padded) % 4;
		if ($remainder > 0) {
			$padded .= str_repeat('=', 4 - $remainder);
		}

		$decoded = base64_decode($padded, true);
		if ($decoded === false) {
			return null;
		}

		$data = json_decode($decoded, true);
		if (!is_array($data)) {
			return null;
		}

		$plan = strtolower(trim((string) ($data['plan'] ?? '')));
		$period = strtolower(trim((string) ($data['period'] ?? '')));
		$proceed = (bool) ($data['proceed'] ?? false);

		if (!($plan === 'pro' && in_array($period, ['monthly', 'yearly'], true) && $proceed)) {
			return null;
		}

		return [
			'plan' => $plan,
			'period' => $period,
			'proceed' => $proceed,
		];
	}
}
