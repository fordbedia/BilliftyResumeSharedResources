<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\Social\HandleSocialAuthCallback;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\Social\ResolveSocialAuthRedirect;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Responses\AuthCookieResponseFactory;
use Illuminate\Http\RedirectResponse;
use Throwable;

class SocialAuthController extends Controller
{
	public function redirect(string $provider, ResolveSocialAuthRedirect $useCase): RedirectResponse
	{
		return $useCase->execute($provider);
	}

	public function callback(
		string $provider,
		HandleSocialAuthCallback $useCase,
		AuthCookieResponseFactory $cookieAuth
	): RedirectResponse
	{
		$frontend = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');

		try {
			$payload = $useCase->execute($provider);
			return $cookieAuth->redirectWithAuthCookie("{$frontend}/dashboard", $payload);
		} catch (Throwable $e) {
			$message = rawurlencode($e->getMessage() ?: 'Social sign-in failed.');
			return redirect()->away("{$frontend}/auth?social_error={$message}");
		}
	}
}
