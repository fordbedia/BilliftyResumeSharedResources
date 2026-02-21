<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\Social;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\GoogleAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\LinkedInAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\SocialAuthProvider;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class ResolveSocialAuthRedirect
{
	public function __construct(
		private readonly GoogleAuthProvider $google,
		private readonly LinkedInAuthProvider $linkedIn,
	) {
	}

	public function execute(string $provider): RedirectResponse
	{
		return $this->resolveProvider($provider)->redirect();
	}

	protected function resolveProvider(string $provider): SocialAuthProvider
	{
		return match (strtolower(trim($provider))) {
			'google' => $this->google,
			'linkedin' => $this->linkedIn,
			default => throw new InvalidArgumentException('Unsupported social provider.'),
		};
	}
}
