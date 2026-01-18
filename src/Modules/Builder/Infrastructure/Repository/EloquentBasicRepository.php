<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Basic;

class EloquentBasicRepository extends EloquentBaseRepository
{
	public function makeModel(): string
	{
		return Basic::class;
	}
}