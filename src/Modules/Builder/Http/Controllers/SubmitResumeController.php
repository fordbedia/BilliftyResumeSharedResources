<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Resume\SaveResume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeFinalizeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmitResumeController extends Controller
{
	public function submit(
		ResumeFinalizeRequest $request
	) {
		SaveResume::make()->upsert(Auth::user()->id ??  1, $request->all());
	}
}
