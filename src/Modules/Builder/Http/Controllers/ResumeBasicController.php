<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeBasicRequest;
use Illuminate\Http\Request;

class ResumeBasicController extends Controller
{
    public function handleSteps(string $type, ResumeBasicRequest $request)
	{
		return ['type' => $type];
	}

	public function handleIndex(int $index, ResumeBasicRequest $request)
	{
		return ['type' => 'index', 'index' => $index];
	}
}
