<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage\ImageProcessor;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeFinalizeRequest;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume as ResumeModel;
use Illuminate\Support\Facades\Auth;

class ResumeBuilderController extends Controller
{
	public function resume(int $id, ResumeRepository $resumes)
	{
		return $resumes->find($id);
	}

	public function update(int $id, ResumeFinalizeRequest $request, ResumeRepository $resumes)
	{
		$payload = $request->validated();
		$resume = $resumes->find($id);
		$payload['basics']['image'] = $resume->basic->image;
		if ($request->hasFile('basics_image')) {
			$file = $request->file('basics_image');
			$name = data_get($payload, 'basics.name', 'resume');

			$imageProcessor = ImageProcessor::make($file, $name);
			$storedPath = $imageProcessor->store();
			$payload['basics']['image'] = $storedPath;
			$imageProcessor->deleteLastFile('image', $resume->basic);
		}
		$resume = Resume::make()->upsert(Auth::user()->id ??  1, $payload, $id);

		return response()->json([
			'success' => true,
			'data' => $resume
		]);
	}

	public function templates(TemplatesRepository $templates)
	{
		return $templates->all();
	}
}
