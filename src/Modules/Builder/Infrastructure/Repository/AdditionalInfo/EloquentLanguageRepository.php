<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\LanguageRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\AdditionalInfo\Languages;
use Illuminate\Support\Arr;

class EloquentLanguageRepository extends EloquentBaseRepository implements LanguageRepository
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
		return Languages::class;
	}
}