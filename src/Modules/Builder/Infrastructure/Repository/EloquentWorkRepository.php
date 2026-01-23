<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\WorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Work;

class EloquentWorkRepository extends EloquentBaseRepository implements WorkRepository
{
	public function save(array $search, array $data)
	{
		return $this->model->updateOrCreate($search, $data);
	}

	public function makeModel(): string
	{
		return Work::class;
	}
}