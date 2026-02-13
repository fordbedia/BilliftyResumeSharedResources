<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\V2\CreateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateController extends Controller
{
	public function create(CreateRequest $request)
	{
		$data = $request->validated();

		$resume = Resume::make()->upsert('create', Auth::user()->id ??  1, $data);

		return response()->json([
			'success' => true,
			'data' => $resume,
			'step' => 'create',
			'type' => 'create'
		]);
	}
}
