<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Authorization\TemplatePlanAccess;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\ResumeStrengthService;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeFinalizeRequest;
use Illuminate\Support\Facades\Auth;

class FinalizeController extends Controller
{
	public function handleSteps(
		int $resumeId,
		string $type,
		ResumeFinalizeRequest $request,
		TemplatePlanAccess $templatePlanAccess,
		ResumeRepository $resumes
	)
	{
		$payload = $request->validated();
		$templatePlanAccess->ensureCanUseTemplate(Auth::user(), (int) data_get($payload, 'finalize.template'));

		Resume::make()->upsert('finalize', Auth::user()->id ??  1, $payload, $resumeId);
		$resume = $resumes->find($resumeId);
		$strength = ResumeStrengthService::make()->forResume($resume, null, true);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'resumeStrength' => $strength,
			'type' => $type,
			'step' => 'finalize'
		]);
	}

	public function handleIndex(int $index, ResumeFinalizeRequest $request)
	{
		$data = $request->validated();
		return ['type' => 'index', 'index' => $index];
	}
}
