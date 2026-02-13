<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\InterestRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Interest;
use Illuminate\Database\Eloquent\Model;

class EloquentInterestRepository extends EloquentBaseRepository implements InterestRepository
{
	public function create(array $data): Model|array
	{
		return parent::create($data);
	}

	public function findBy(string $field, string $value)
	{
		return parent::findBy($field, $value);
	}

	public function makeModel(): string
	{
		return Interest::class;
	}
}