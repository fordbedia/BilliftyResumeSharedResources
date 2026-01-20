<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Requests\ResumeFinalizeRequest;
use Illuminate\Http\Request;

class SubmitResumeController extends Controller
{
	public function submit(
		ResumeFinalizeRequest $request
	) {
		dd($request->all());
	}
}
