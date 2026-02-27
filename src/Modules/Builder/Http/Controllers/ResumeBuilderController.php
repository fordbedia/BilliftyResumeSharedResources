<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Authorization\TemplatePlanAccess;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\ResumeStrengthService;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage\ImageFileUploadProcessor;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeFinalizeRequest;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume as ResumeModel;
use Illuminate\Support\Facades\Auth;

class ResumeBuilderController extends Controller
{
	public function resume(int $id, ResumeRepository $resumes)
	{
		$resume = $resumes->find($id);
		$strength = ResumeStrengthService::make()->forResume($resume, null, true);

		return response()->json([
			...$resume->toArray(),
			'resumeStrength' => $strength,
		]);
	}

	public function update(
		int $id,
		ResumeFinalizeRequest $request,
		ResumeRepository $resumes,
		TemplatePlanAccess $templatePlanAccess
	)
	{
		$payload = $request->validated();
		$user = Auth::user();

		$templateId = (int) data_get($payload, 'finalize.template', 0);
		if ($templateId > 0) {
			$templatePlanAccess->ensureCanUseTemplate($user, $templateId);
		}

		$resume = $resumes->find($id);
		if ($request->hasFile('basics_image')) {
			$file = $request->file('basics_image');
			$name = data_get($payload, 'basics.name', 'resume');

			$imageProcessor = ImageFileUploadProcessor::make($file, $name);
			$storedPath = $imageProcessor->store();
			$payload['basics']['image'] = $storedPath;
			$imageProcessor->deleteLastFile('image', $resume->basic);
		} else {
			// Check if $payload['basics']['image'] is empty
			// If empty then it was removed
			if (empty($payload['basics']['image'])) {
				if ($resume->basic->image) ImageFileUploadProcessor::deleteFile($resume->basic->image, $resume->basic->image_disk);
			}
		}
		$resume = Resume::make()->upsert(Auth::user()->id ??  1, $payload, $id);
		$strength = ResumeStrengthService::make()->forResume($resume, null, true);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'resumeStrength' => $strength,
		]);
	}

	public function templates(TemplatesRepository $templates)
	{
		return $templates->all();
	}

	public function resumePreview(ResumeRepository $resume)
	{
		return $resume->getByKey(1);
	}
}
