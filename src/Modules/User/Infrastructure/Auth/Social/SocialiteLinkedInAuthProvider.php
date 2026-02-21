<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\Social;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\DTO\SocialProviderUser;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\LinkedInAuthProvider;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use SocialiteProviders\LinkedInOpenId\Provider as LinkedInOpenIdProvider;

class SocialiteLinkedInAuthProvider implements LinkedInAuthProvider
{
	public function key(): string
	{
		return 'linkedin';
	}

	public function redirect(?string $state = null): RedirectResponse
	{
		$scopes = (array) config('services.linkedin.scopes', ['openid', 'profile', 'email']);
		$client = $this->resolveClient()->scopes($scopes)->stateless();
		if ($state) {
			$client = $client->with(['state' => $state]);
		}

		return $client->redirect();
	}

	public function userFromCallback(): SocialProviderUser
	{
		$user = $this->resolveClient()->stateless()->user();

		return new SocialProviderUser(
			provider: $this->key(),
			providerId: $user->getId(),
			email: $user->getEmail(),
			name: $user->getName() ?: $user->getNickname(),
			avatar: $user->getAvatar(),
		);
	}

	protected function resolveDriverName(): string
	{
		$driver = trim((string) config('services.linkedin.driver', 'linkedin-openid'));
		return $driver !== '' ? $driver : 'linkedin-openid';
	}

	protected function resolveClient(): mixed
	{
		$driver = $this->resolveDriverName();
		if ($driver === 'linkedin-openid') {
			if (!class_exists(LinkedInOpenIdProvider::class)) {
				throw new RuntimeException(
					'linkedin-openid provider is not installed. Run: composer require socialiteproviders/linkedin-openid'
				);
			}

			return Socialite::buildProvider(LinkedInOpenIdProvider::class, [
				'client_id' => config('services.linkedin.client_id'),
				'client_secret' => config('services.linkedin.client_secret'),
				'redirect' => config('services.linkedin.redirect'),
			]);
		}

		return Socialite::driver($driver);
	}
}
