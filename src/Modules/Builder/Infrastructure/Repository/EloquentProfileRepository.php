<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Profile;

class EloquentProfileRepository extends EloquentBaseRepository
{
	public function makeModel(): string
	{
		return Profile::class;
	}
}