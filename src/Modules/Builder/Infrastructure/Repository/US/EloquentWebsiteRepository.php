<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\WebsiteRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Websites;

class EloquentWebsiteRepository extends EloquentBaseRepository implements WebsiteRepository
{
	public function findBy(string $field, string $value)
	{
		return parent::findBy($field, $value);
	}

	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->updateOrCreate($data);
	}

	public function makeModel(): string
	{
		return Websites::class;
	}
}