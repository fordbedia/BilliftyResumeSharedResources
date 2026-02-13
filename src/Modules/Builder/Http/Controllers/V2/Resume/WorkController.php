<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\WorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeWorkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkController extends Controller
{
	public function handleSteps(int $resumeId, string $type, ResumeWorkRequest $request)
	{
		$payload = $request->validated();

		$resume = Resume::make()->upsert('work', Auth::user()->id ??  1, $payload, $resumeId);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'type' => $type
		]);
	}

	public function handleIndex(int $index, ResumeWorkRequest $request)
	{
		$data = $request->validated();
		return ['type' => 'index', 'index' => $index];
	}
}