<?php


use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\BasicController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\CreateController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\EducationController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\FinalizeController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\ReferencesController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\SkillsController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\Resume\WorkController;
use Illuminate\Support\Facades\Route;

Route::prefix('resume')->middleware(['auth:api'])
	->group(function () {
	Route::post('create', [CreateController::class, 'create']);

	Route::post('/{resumeId}/basics/{type}', [BasicController::class, 'handleSteps']);
	Route::post('/{resumeId}/basics/index/{index}', [BasicController::class, 'handleIndex'])
		->name('basic.index');

	Route::post('/{resumeId}/work/{type}', [WorkController::class, 'handleSteps']);
	Route::post('/{resumeId}/work/index/{index}', [WorkController::class, 'handleIndex'])
		->name('basic.index');

	Route::post('/{resumeId}/education/{type}', [EducationController::class, 'handleSteps']);
	Route::post('/{resumeId}/education/index/{index}', [EducationController::class, 'handleIndex'])
		->name('basic.index');

	Route::post('/{resumeId}/skills/{type}', [SkillsController::class, 'handleSteps']);
	Route::post('/{resumeId}/skills/index/{index}', [SkillsController::class, 'handleIndex'])
		->name('basic.index');

	Route::post('/{resumeId}/references/{type}', [ReferencesController::class, 'handleSteps']);
	Route::post('/{resumeId}/references/index/{index}', [ReferencesController::class, 'handleIndex'])
		->name('basic.index');

	Route::post('/{resumeId}/finalize/{type}', [FinalizeController::class, 'handleSteps']);
	Route::post('/{resumeId}/finalize/index/{index}', [FinalizeController::class, 'handleIndex'])
		->name('basic.index');
});