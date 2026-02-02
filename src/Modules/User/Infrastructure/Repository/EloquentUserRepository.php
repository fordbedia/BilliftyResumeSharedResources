<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\UserEloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;

class EloquentUserRepository extends UserEloquentBaseRepository implements UserRepository
{
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