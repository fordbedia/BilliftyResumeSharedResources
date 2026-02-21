<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\Social;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\GoogleAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\LinkedInAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social\SocialAuthProvider;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\PassportUserAuthentication;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HandleSocialAuthCallback
{
	public function __construct(
		private readonly GoogleAuthProvider $google,
		private readonly LinkedInAuthProvider $linkedIn,
		private readonly UserRepository $users,
		private readonly PassportUserAuthentication $authenticator,
	) {
	}

	public function execute(string $provider): array
	{
		$social = $this->resolveProvider($provider)->userFromCallback();
		$email = trim((string) $social->email);

		if ($email === '') {
			throw new InvalidArgumentException('Unable to authenticate: provider did not return an email address.');
		}

		$name = trim((string) $social->name);
		if ($name === '') {
			$name = Str::before($email, '@') ?: 'User';
		}

		/** @var User $user */
		$user = DB::transaction(function () use ($email, $name): User {
			$existing = $this->users->findByEmail($email);

			if (! $existing) {
				$existing = $this->users->createSocialUser($name, $email);
			} elseif (trim((string) $existing->name) === '') {
				$existing->forceFill(['name' => $name]);
				$existing->save();
			}

			$this->users->ensureUserInfo($existing->id);

			return $existing;
		});

		return $this->authenticator->handleUser($user);
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
