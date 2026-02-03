<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ColorSchemeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\ColorScheme;

class EloquentColorSchemeRepository extends EloquentBaseRepository implements ColorSchemeRepository
{
	public function getPrimary(int $id): string
	{
		return $this->model->whereId($id)->pluck('primary')->first();
	}

	public function makeModel(): string
	{
		return ColorScheme::class;
	}
}