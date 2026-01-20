<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\BaseResumeController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeWorkRequest;
use Illuminate\Http\Request;

class ResumeWorkController extends BaseResumeController
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
		return ResumeWorkRequest::class;
	}
}
