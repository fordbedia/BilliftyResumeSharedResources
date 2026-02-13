<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\VolunteeringRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Volunteering;
use Illuminate\Database\Eloquent\Model;

class EloquentVolunteeringRepository extends EloquentBaseRepository implements VolunteeringRepository
{
	public function findBy(string $field, string $value)
	{
		return parent::findBy($field, $value);
	}

	public function create(array $data): Model|array
	{
		return parent::create($data);
	}

	public function makeModel(): string
	{
		return Volunteering::class;
	}
}