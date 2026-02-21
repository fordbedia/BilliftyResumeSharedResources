<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\DTO;

class SocialProviderUser
{
	public function __construct(
		public readonly string $provider,
		public readonly ?string $providerId,
		public readonly ?string $email,
		public readonly ?string $name,
		public readonly ?string $avatar,
	) {
	}
}
