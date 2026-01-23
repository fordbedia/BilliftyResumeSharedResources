<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\SkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Skills;

class EloquentSkillsRepository extends EloquentBaseRepository implements SkillsRepository
{
	public function save(array $search, array $data)
	{
		return $this->model->updateOrCreate($search, $data);
	}

	public function makeModel(): string
	{
		return Skills::class;
	}
}