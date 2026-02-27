<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\ResumeStrengthService;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage\ImageFileUploadProcessor;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeBasicRequest;
use BilliftyResumeSDK\SharedResources\Modules\User\Domain\Authorization\UserAbility;
use BilliftyResumeSDK\SharedResources\Modules\User\Domain\Authorization\UserEntitlementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BasicController extends Controller
{
	public function handleSteps(
		int $resumeId,
		string $type,
		ResumeBasicRequest $request,
		BasicRepository $basic,
		ResumeRepository $resumes,
		UserEntitlementService $entitlements
	)
	{
		$payload = $request->validated();
		$basicModel = $basic->findBy('resume_id', $resumeId);
		if ($request->hasFile('basics_image')) {
			if (!$entitlements->userCan($request->user(), UserAbility::UPLOAD_RESUME_PHOTO)) {
				throw ValidationException::withMessages([
					'basics_image' => 'Resume photo upload is available on Pro plan only.',
				]);
			}

			$file = $request->file('basics_image');
			$name = data_get($payload, 'basics.name', 'resume');

			$imageProcessor = ImageFileUploadProcessor::make($file, $name);
			$storedPath = $imageProcessor->store(400, 400);
			$imageProcessor->deleteLastFile('image', $basicModel);
			$payload['basics']['image'] = $storedPath;
		} else {
			$basicImage = $basicModel->image ?? null;
			if (!$entitlements->userCan($request->user(), UserAbility::UPLOAD_RESUME_PHOTO) && $basicImage) {
				ImageFileUploadProcessor::deleteFile($basicImage, $basicModel->image_disk);
				$basicImage = null;
			}
			$payload['basics']['image'] = $basicImage;
		}
		Resume::make()->upsert('basics', Auth::user()->id ??  1, $payload, $resumeId);
		$resume = $resumes->find($resumeId);
		$strength = ResumeStrengthService::make()->forResume($resume, null, true);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'resumeStrength' => $strength,
			'type' => $type,
			'step'	=> 'basics'
		]);
	}

	public function handleIndex(int $index, ResumeBasicRequest $request)
	{
		$data = $request->validated();
		return ['type' => 'index', 'index' => $index];
	}
}
