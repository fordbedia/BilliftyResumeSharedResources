<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\BaseResumeController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Contract\ResumeController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeSkillsRequest;
use Illuminate\Http\Request;

class ResumeSkillsController extends BaseResumeController
{

	public function handleSteps(string $type, Request $request)
	{
		$data = $this->validated($request);
		return ['type' => $type];
	}

	public function handleIndex(int $index, Request $request)
	{
		$data = $this->validated($request);
		return ['type' => 'index', 'index' => $index];
	}

	protected function requestClass(): string
	{
		return ResumeSkillsRequest::class;
	}
}
