<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Authorization;

use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;

interface TemplatePlanAccess
{
	public function allowedPlansForUser(?User $user): array;

	public function ensureCanUseTemplate(?User $user, int $templateId): void;
}
