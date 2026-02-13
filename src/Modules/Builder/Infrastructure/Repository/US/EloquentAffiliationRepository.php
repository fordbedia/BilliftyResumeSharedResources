<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\AffiliationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Affiliation;

class EloquentAffiliationRepository extends EloquentBaseRepository implements AffiliationRepository
{
	public function findBy(string $field, string $value)
	{
		return parent::findBy($field, $value);
	}

	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return Affiliation::create($data);
	}

	public function makeModel(): string
	{
		return Affiliation::class;
	}
}