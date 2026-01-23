<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Reference;

class EloquentReferenceRepository extends EloquentBaseRepository implements ReferenceRepository
{
	public function save(array $search, array $data)
	{
		return $this->model->updateOrCreate($search, $data);
	}

	public function makeModel(): string
	{
		return Reference::class;
	}
}