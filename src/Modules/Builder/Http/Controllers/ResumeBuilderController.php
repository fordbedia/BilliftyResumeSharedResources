<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;

class ResumeBuilderController extends Controller
{
	public function templates(TemplatesRepository $templates)
	{
		return $templates->all();
	}
}
