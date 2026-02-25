<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Domain\Authorization;

use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;

class UserEntitlementService
{
	public const PLAN_FREE = 'free';
	public const PLAN_PRO = 'pro';

	/**
	 * Capabilities currently unlocked by Pro.
	 * AI is mapped here so free users are explicitly blocked even before AI endpoints exist.
	 */
	private const PRO_ABILITIES = [
		UserAbility::EXPORT_CLEAN_PDF,
		UserAbility::UPLOAD_RESUME_PHOTO,
		UserAbility::ACCESS_PRO_TEMPLATES,
		UserAbility::ACCESS_AI_FEATURES,
		UserAbility::REMOVE_WATERMARK,
		UserAbility::RESUME_VERSION_HISTORY,
	];

	public function userCan(?User $user, string $ability): bool
	{
		return $this->planCan($this->resolvePlan($user), $ability);
	}

	public function planCan(string $plan, string $ability): bool
	{
		$plan = $this->normalizePlan($plan);

		if (!in_array($ability, UserAbility::all(), true)) {
			return false;
		}

		if ($plan === self::PLAN_PRO) {
			return in_array($ability, self::PRO_ABILITIES, true);
		}

		return false;
	}

	public function resolvePlan(?User $user): string
	{
		return $this->normalizePlan((string) ($user?->plan ?? self::PLAN_FREE));
	}

	public function normalizePlan(?string $plan): string
	{
		return strtolower(trim((string) $plan)) === self::PLAN_PRO
			? self::PLAN_PRO
			: self::PLAN_FREE;
	}

	public function allowedTemplatePlans(?User $user): array
	{
		if ($this->userCan($user, UserAbility::ACCESS_PRO_TEMPLATES)) {
			return [self::PLAN_FREE, self::PLAN_PRO];
		}

		return [self::PLAN_FREE];
	}

	public function abilityMapForUser(?User $user): array
	{
		$abilities = [];

		foreach (UserAbility::all() as $ability) {
			$abilities[$ability] = $this->userCan($user, $ability);
		}

		return $abilities;
	}
}
