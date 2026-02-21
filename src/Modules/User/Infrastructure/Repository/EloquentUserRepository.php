<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\UserEloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EloquentUserRepository extends UserEloquentBaseRepository implements UserRepository
{
	public function find($id): ?\Illuminate\Database\Eloquent\Model
	{
		return $this->model->find($id)->loadMissing(User::relationships());
	}

	public function findStripeCustomer(string $stripeCustomerId)
	{
		return $this->model->where('stripe_customer_id', $stripeCustomerId)->first();
	}

	public function save(array $data, Model|null $userModel = null)
	{
		$user = $userModel ?? $this->model->newQuery()->find($data['id']);
		$userData = Arr::except($data, ['info']);
		$userInfo = Arr::only($data, ['info']);

		if (!empty($userData)) {
			$user->forceFill($userData);
			$user->save();
		}
		if (!empty($userInfo['info'])) {
			$user->info()->updateOrCreate(['user_id' => $user->id], $userInfo['info']);
		}
		return $user->refresh()->loadMissing(User::relationships());
	}

	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->create(array_merge($data, [
			'password' => bcrypt($data['password'])
		]));
	}

	public function findByEmail(string $email): ?User
	{
		/** @var User|null $user */
		$user = $this->model->newQuery()
			->where('email', $email)
			->first();

		return $user;
	}

	public function createSocialUser(string $name, string $email): User
	{
		/** @var User $user */
		$user = $this->model->newQuery()->create([
			'name' => $name,
			'email' => $email,
			// Social users don't use password sign-in by default.
			'password' => bcrypt(Str::random(40)),
		]);

		return $user;
	}

	public function ensureUserInfo(int $userId): void
	{
		UserInfo::query()->firstOrCreate(['user_id' => $userId], []);
	}

	public function makeModel(): string
	{
		return User::class;
	}
}
