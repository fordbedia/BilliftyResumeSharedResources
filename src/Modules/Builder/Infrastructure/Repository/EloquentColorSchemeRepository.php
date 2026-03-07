<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ColorSchemeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\ColorScheme;

class EloquentColorSchemeRepository extends EloquentBaseRepository implements ColorSchemeRepository
{
	public function getPrimary(?int $id, ?int $resumeColorSchemeId = null): ?string
	{
		$targetColorSchemeId = $id ?? $resumeColorSchemeId;
		if (!$targetColorSchemeId) {
			return null;
		}

		return $this->model
			->newQuery()
			->whereId($targetColorSchemeId)
			->value('primary');
	}

	public function makeModel(): string
	{
		return ColorScheme::class;
	}
}
