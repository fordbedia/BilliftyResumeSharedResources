<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Education;

class EloquentEducationRepository extends EloquentBaseRepository implements EducationRepository
{
	public function save(array $search, array $data)
	{
		return $this->model->updateOrCreate($search, $data);
	}

	public function makeModel(): string
	{
		return Education::class;
	}
}