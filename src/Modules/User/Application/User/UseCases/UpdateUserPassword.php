<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Services\PasswordHasher;

class UpdateUserPassword
{
	public function __construct(
		protected UserRepository $user,
		protected PasswordHasher $hasher
	){}

	public function execute(
		int $userId,
		string $currentPassword,
		string $newPassword
	): void {
		$user = $this->user->find($userId);
		if (! $this->hasher->verify($currentPassword, $user->password)) {
			throw new \DomainException('Current password is incorrect.');
		}

		$user->password = $this->hasher->hash($newPassword);
		$user->save();
	}
}