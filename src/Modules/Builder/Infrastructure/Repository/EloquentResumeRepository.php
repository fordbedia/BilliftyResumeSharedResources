<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;

class EloquentResumeRepository extends EloquentBaseRepository
{
	public function makeModel(): string
	{
		return Resume::class;
	}
}