<?php


use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\BasicController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\CreateController;
use Illuminate\Support\Facades\Route;

Route::prefix('resume')->middleware(['auth:api'])
	->group(function () {
	Route::post('create', [CreateController::class, 'create']);

	Route::post('/{resumeId}/basics/{type}', [BasicController::class, 'handleSteps']);
	Route::post('/{resumeId}/basics/index/{index}', [BasicController::class, 'handleIndex'])
		->name('basic.index');
});