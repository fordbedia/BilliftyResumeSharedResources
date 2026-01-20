<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Skills;

class EloquentSkillsRepository extends EloquentBaseRepository
{

	public function makeModel(): string
	{
		return Skills::class;
	}
}