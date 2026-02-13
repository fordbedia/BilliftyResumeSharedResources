<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage\ImageFileUploadProcessor;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\ResumeBasicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasicController extends Controller
{
	public function handleSteps(int $resumeId, string $type, ResumeBasicRequest $request, BasicRepository $basic)
	{
		$payload = $request->validated();
		$basicModel = $basic->findBy('resume_id', $resumeId);
		if ($request->hasFile('basics_image')) {
			$file = $request->file('basics_image');
			$name = data_get($payload, 'basics.name', 'resume');

			$imageProcessor = ImageFileUploadProcessor::make($file, $name);
			$storedPath = $imageProcessor->store();
			$imageProcessor->deleteLastFile('image', $basicModel);
			$payload['basics']['image'] = $storedPath;
		} else {
			$payload['basics']['image'] = $basicModel->image ?? null;
		}
		$resume = Resume::make()->upsert('basics', Auth::user()->id ??  1, $payload, $resumeId);

		return response()->json([
			'success' => true,
			'data' => $resume,
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