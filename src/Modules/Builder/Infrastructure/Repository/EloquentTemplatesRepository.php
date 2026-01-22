<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Templates;

class EloquentTemplatesRepository extends EloquentBaseRepository implements TemplatesRepository
{
	public function all()
	{
		return $this->model->whereIsActive(1)->get();
	}

	public function makeModel(): string
	{
		return Templates::class;
	}
}