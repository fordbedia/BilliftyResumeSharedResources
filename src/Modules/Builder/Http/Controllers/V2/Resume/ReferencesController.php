<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeReferenceRequest;
use Illuminate\Support\Facades\Auth;

class ReferencesController extends Controller
{
	public function handleSteps(int $resumeId, string $type, ResumeReferenceRequest $request)
	{
		$payload = $request->validated();

		$resume = Resume::make()->upsert('references', Auth::user()->id ??  1, $payload, $resumeId);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'type' => $type
		]);
	}

	public function handleIndex(int $index, ResumeReferenceRequest $request)
	{
		$data = $request->validated();
		return ['type' => 'index', 'index' => $index];
	}
}