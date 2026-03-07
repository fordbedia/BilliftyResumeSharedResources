<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository;

interface ColorSchemeRepository
{
	public function getPrimary(?int $id, ?int $resumeColorSchemeId = null): ?string;
}
