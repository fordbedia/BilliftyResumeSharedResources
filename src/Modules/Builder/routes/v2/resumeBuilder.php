<?php


use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\CreateController;
use Illuminate\Support\Facades\Route;

Route::prefix('resume')
	->middleware(['auth:api'])
	->group(function () {
		Route::post('create', [CreateController::class, 'create']);
});