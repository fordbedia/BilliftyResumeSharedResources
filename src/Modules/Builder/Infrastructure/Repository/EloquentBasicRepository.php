<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Basic;

class EloquentBasicRepository extends EloquentBaseRepository implements BasicRepository
{
	public function save(array $search, array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->updateOrCreate($search, $data);
	}

	public function makeModel(): string
	{
		return Basic::class;
	}
}