<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\Social;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\DTO\SocialProviderUser;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\GoogleAuthProvider;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialiteGoogleAuthProvider implements GoogleAuthProvider
{
	public function key(): string
	{
		return 'google';
	}

	public function redirect(): RedirectResponse
	{
		$scopes = (array) config('services.google.scopes', ['openid', 'profile', 'email']);
		return Socialite::driver('google')->scopes($scopes)->stateless()->redirect();
	}

	public function userFromCallback(): SocialProviderUser
	{
		$user = Socialite::driver('google')->stateless()->user();

		return new SocialProviderUser(
			provider: $this->key(),
			providerId: $user->getId(),
			email: $user->getEmail(),
			name: $user->getName() ?: $user->getNickname(),
			avatar: $user->getAvatar(),
		);
	}
}
