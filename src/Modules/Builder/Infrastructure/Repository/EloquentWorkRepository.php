<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Work;

class EloquentWorkRepository extends EloquentBaseRepository
{

	public function makeModel(): string
	{
		return Work::class;
	}
}