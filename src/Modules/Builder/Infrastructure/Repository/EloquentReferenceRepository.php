<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Reference;

class EloquentReferenceRepository extends EloquentBaseRepository
{

	public function makeModel(): string
	{
		return Reference::class;
	}
}