<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\TemplatesRepository;
use Illuminate\Http\Request;

class ResumeBuilderController extends Controller
{
	public function templates(TemplatesRepository $templates)
	{
		return $templates->all();
	}
}
