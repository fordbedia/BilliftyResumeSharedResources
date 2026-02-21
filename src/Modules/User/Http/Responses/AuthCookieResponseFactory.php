<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class AuthCookieResponseFactory
{
	public function jsonWithAuthCookie(array $tokenPayload, ?array $publicPayload = null, int $status = 200): JsonResponse
	{
		$minutes = $this->resolveLifetimeMinutes($tokenPayload);
		$token = (string) ($tokenPayload['access_token'] ?? '');
		$responsePayload = $publicPayload ?? $tokenPayload;

		return response()
			->json($responsePayload, $status)
			->withCookie($this->tokenCookie($token, $minutes))
			->withCookie($this->stateCookie('1', $minutes));
	}

	public function redirectWithAuthCookie(string $url, array $payload): RedirectResponse
	{
		$minutes = $this->resolveLifetimeMinutes($payload);
		$token = (string) ($payload['access_token'] ?? '');

		return redirect()
			->away($url)
			->withCookie($this->tokenCookie($token, $minutes))
			->withCookie($this->stateCookie('1', $minutes));
	}

	public function jsonLoggedOut(int $status = 200): JsonResponse
	{
		return response()
			->json(['ok' => true], $status)
			->withCookie($this->forgetTokenCookie())
			->withCookie($this->forgetStateCookie());
	}

	protected function resolveLifetimeMinutes(array $payload): int
	{
		$expiresAt = (string) ($payload['expires_at'] ?? '');
		if ($expiresAt !== '') {
			$exp = Carbon::parse($expiresAt);
			return max(now()->diffInMinutes($exp, false), 1);
		}

		$tokenDays = max((int) config('auth_cookie.access_token_days', 60), 1);
		return $tokenDays * 24 * 60;
	}

	protected function tokenCookie(string $token, int $minutes)
	{
		return cookie(
			name: (string) config('auth_cookie.cookie_name', 'billifty_at'),
			value: $token,
			minutes: $minutes,
			path: '/',
			domain: $this->cookieDomain(),
			secure: $this->cookieSecure(),
			httpOnly: true,
			raw: false,
			sameSite: $this->cookieSameSite(),
		);
	}

	protected function forgetTokenCookie()
	{
		return cookie(
			name: (string) config('auth_cookie.cookie_name', 'billifty_at'),
			value: '',
			minutes: -1,
			path: '/',
			domain: $this->cookieDomain(),
			secure: $this->cookieSecure(),
			httpOnly: true,
			raw: false,
			sameSite: $this->cookieSameSite(),
		);
	}

	protected function stateCookie(string $value, int $minutes)
	{
		return cookie(
			name: (string) config('auth_cookie.state_cookie_name', 'billifty_auth'),
			value: $value,
			minutes: $minutes,
			path: '/',
			domain: $this->cookieDomain(),
			secure: $this->cookieSecure(),
			httpOnly: false,
			raw: false,
			sameSite: $this->cookieSameSite(),
		);
	}

	protected function forgetStateCookie()
	{
		return cookie(
			name: (string) config('auth_cookie.state_cookie_name', 'billifty_auth'),
			value: '',
			minutes: -1,
			path: '/',
			domain: $this->cookieDomain(),
			secure: $this->cookieSecure(),
			httpOnly: false,
			raw: false,
			sameSite: $this->cookieSameSite(),
		);
	}

	protected function cookieDomain(): ?string
	{
		$domain = trim((string) config('auth_cookie.cookie_domain', ''));
		return $domain !== '' ? $domain : null;
	}

	protected function cookieSecure(): bool
	{
		return (bool) config('auth_cookie.cookie_secure', true);
	}

	protected function cookieSameSite(): string
	{
		$sameSite = strtolower(trim((string) config('auth_cookie.cookie_samesite', 'lax')));
		return in_array($sameSite, ['lax', 'strict', 'none'], true) ? $sameSite : 'lax';
	}
}
