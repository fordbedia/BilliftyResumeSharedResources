<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\AccomplishmentRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Accomplishment;

class EloquentAccomplishmentRepository extends EloquentBaseRepository implements AccomplishmentRepository
{

	public function makeModel(): string
	{
		return Accomplishment::class;
	}
}