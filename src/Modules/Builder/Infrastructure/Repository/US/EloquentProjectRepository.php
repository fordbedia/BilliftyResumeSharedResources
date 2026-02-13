<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\ProjectRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Project;
use Illuminate\Database\Eloquent\Model;

class EloquentProjectRepository extends EloquentBaseRepository implements ProjectRepository
{

	public function findBy(string $field, string $value)
	{
		return parent::findBy($field, $value);
	}

	public function create(array $data): Model|array
	{
		return parent::create($data);
	}

	public function makeModel(): string
	{
		return Project::class;
	}
}