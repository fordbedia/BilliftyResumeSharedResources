<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Reference;

class EloquentReferenceRepository extends EloquentBaseRepository implements ReferenceRepository
{

	public function makeModel(): string
	{
		return Reference::class;
	}
}