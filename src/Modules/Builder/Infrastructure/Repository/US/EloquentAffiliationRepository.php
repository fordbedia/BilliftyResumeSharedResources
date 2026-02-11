<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\AffiliationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Affiliation;

class EloquentAffiliationRepository extends EloquentBaseRepository implements AffiliationRepository
{

	public function makeModel(): string
	{
		return Affiliation::class;
	}
}