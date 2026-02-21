<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository;

use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;

interface UserRepository
{
	public function find($id): ?Model;

	public function create(array $data): Model|array;

	public function save(array $data, Model|null $userModel = null);

	public function update(array $data, ?int $id = null): Model|bool;

	public function findStripeCustomer(string $stripeCustomerId);

	public function findByEmail(string $email): ?User;

	public function createSocialUser(string $name, string $email): User;

	public function ensureUserInfo(int $userId): void;
}
