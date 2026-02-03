<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage\ImageFileUploadProcessor;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeFinalizeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SubmitResumeController extends Controller
{
	/**
	 * @param ResumeFinalizeRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function submit(
		ResumeFinalizeRequest $request
	): JsonResponse {
		$payload = $request->validated();
		if ($request->hasFile('basics_image')) {
			$file = $request->file('basics_image');
			$name = data_get($payload, 'basics.name', 'resume');

			$storedPath = ImageFileUploadProcessor::make($file, $name)->store();
			$payload['basics']['image'] = $storedPath;
		}
		$resume = Resume::make()->upsert(Auth::user()->id ??  1, $payload);

		return response()->json([
			'success' => true,
			'data' => $resume
		]);
	}
}
