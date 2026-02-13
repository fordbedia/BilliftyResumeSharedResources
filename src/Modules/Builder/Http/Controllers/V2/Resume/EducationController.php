<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeEducationRequest;
use Illuminate\Support\Facades\Auth;

class EducationController extends Controller
{
	public function handleSteps(int $resumeId, string $type, ResumeEducationRequest $request)
	{
		$payload = $request->validated();

		$resume = Resume::make()->upsert('education', Auth::user()->id ??  1, $payload, $resumeId);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'type' => $type,
			'step' => 'education'
		]);
	}

	public function handleIndex(int $index, ResumeEducationRequest $request)
	{
		$data = $request->validated();
		return ['type' => 'index', 'index' => $index];
	}
}