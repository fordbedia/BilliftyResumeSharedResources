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
		return $resumes->find($id)->loadMissing(ResumeModel::relationships());
	}

	public function update(int $id, ResumeFinalizeRequest $request, ResumeRepository $resumes)
	{
		$payload = $request->validated();
		if ($request->hasFile('basics_image')) {
			$file = $request->file('basics_image');
			$name = data_get($payload, 'basics.name', 'resume');

			$storedPath = ImageProcessor::make($file, $name)->store();
			$payload['basics']['image'] = $storedPath;
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
