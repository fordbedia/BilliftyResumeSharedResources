<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\UserEloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
	public function makeModel(): string
	{
		return User::class;
	}
}