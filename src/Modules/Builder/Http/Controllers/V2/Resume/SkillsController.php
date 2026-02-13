<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeSkillsRequest;
use Illuminate\Support\Facades\Auth;

class SkillsController extends Controller
{
	public function handleSteps(int $resumeId, string $type, ResumeSkillsRequest $request)
	{
		$payload = $request->validated();

		$resume = Resume::make()->upsert('skills', Auth::user()->id ??  1, $payload, $resumeId);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'type' => $type
		]);
	}

	public function handleIndex(int $index, ResumeSkillsRequest $request)
	{
		$data = $request->validated();
		return ['type' => 'index', 'index' => $index];
	}
}