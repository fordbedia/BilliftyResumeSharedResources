<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumeBasicController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumePdfController;
use Illuminate\Support\Facades\Route;

Route::get('/resume.pdf', [ResumePdfController::class, 'download']);
Route::get('/resume.preview', [ResumePdfController::class, 'stream']);
Route::get('/resume/export', [ResumePdfController::class, 'exportJson']);
Route::get('/resume/build', [ResumePdfController::class, 'buildFromStrings']);

Route::prefix('v1')->group(function () {
	Route::post('resume/basic/{type}', [ResumeBasicController::class, 'handleSteps'])
		->name('resume.basic.type');
	Route::post('resume/basic/index/{index}', [ResumeBasicController::class, 'handleIndex'])
		->name('resume.basic.index');
});
