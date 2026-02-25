<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Authorization;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Authorization\TemplatePlanAccess;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Templates;
use BilliftyResumeSDK\SharedResources\Modules\User\Domain\Authorization\UserEntitlementService;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use Illuminate\Validation\ValidationException;

class UserPlanTemplateAccessAdapter implements TemplatePlanAccess
{
	public function __construct(
		private readonly UserEntitlementService $entitlements
	)
	{
	}

	public function allowedPlansForUser(?User $user): array
	{
		return $this->entitlements->allowedTemplatePlans($user);
	}

	public function ensureCanUseTemplate(?User $user, int $templateId): void
	{
		$allowedPlans = $this->allowedPlansForUser($user);

		$allowed = Templates::query()
			->whereKey($templateId)
			->where('is_active', 1)
			->whereIn('plan', $allowedPlans)
			->exists();

		if (!$allowed) {
			throw ValidationException::withMessages([
				'template' => 'Selected template is not available for your current plan.',
			]);
		}
	}
}
