<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Authorization\TemplatePlanAccess;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Templates;
use Illuminate\Support\Facades\Auth;

class EloquentTemplatesRepository extends EloquentBaseRepository implements TemplatesRepository
{
	public function __construct(
		private readonly TemplatePlanAccess $templatePlanAccess
	)
	{
		parent::__construct();
	}

	public function all()
	{
		$allowedPlans = $this->templatePlanAccess->allowedPlansForUser(Auth::user());

		return $this->model
			->newQuery()
			->whereIsActive(1)
			->allowedPlans($allowedPlans)
			->get();
	}

	public function makeModel(): string
	{
		return Templates::class;
	}
}
