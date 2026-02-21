<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\Social;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\DTO\SocialProviderUser;
use Illuminate\Http\RedirectResponse;

interface SocialAuthProvider
{
	public function key(): string;

	public function redirect(): RedirectResponse;

	public function userFromCallback(): SocialProviderUser;
}
