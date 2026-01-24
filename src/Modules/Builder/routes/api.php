<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeBasicController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeEducationController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeReferencesController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeSkillsController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeTemplateController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeWorkController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumeBuilderController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumePdfController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\SubmitResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/resume.pdf', [ResumePdfController::class, 'download']);
Route::get('/resume.preview', [ResumePdfController::class, 'stream']);
Route::get('/resume/export', [ResumePdfController::class, 'exportJson']);
Route::get('/resume/build', [ResumePdfController::class, 'buildFromStrings']);

Route::prefix('v1')->group(function () {
	Route::prefix('resume')->group(function () {
		// Basic
		Route::post('basics/{type}', [ResumeBasicController::class, 'handleSteps']);
		Route::post('basics/index/{index}', [ResumeBasicController::class, 'handleIndex'])
			->name('basic.index');
		// Skills
		Route::post('skills/{type}', [ResumeSkillsController::class, 'handleSteps'])
			->name('skills.type');
		Route::post('skills/index/{index}', [ResumeSkillsController::class, 'handleIndex'])
			->name('skills.index');
		// Work
		Route::post('work/{type}', [ResumeWorkController::class, 'handleSteps'])
			->name('work.type');
		Route::post('work/index/{index}', [ResumeWorkController::class, 'handleIndex'])
			->name('work.index');
		// Education
		Route::post('education/{type}', [ResumeEducationController::class, 'handleSteps'])
			->name('education.type');
		Route::post('education/index/{index}', [ResumeEducationController::class, 'handleIndex'])
			->name('education.index');
		// References
		Route::post('references/{type}', [ResumeReferencesController::class, 'handleSteps'])
			->name('references.type');
		Route::post('references/index/{index}', [ResumeReferencesController::class, 'handleIndex'])
			->name('references.index');
		// Template
		Route::post('template/{type}', [ResumeTemplateController::class, 'handleSteps'])
			->name('template.type');
		Route::post('template/index/{index}', [ResumeTemplateController::class, 'handleIndex'])
			->name('template.index');

		// Submission
		Route::post('submit', [SubmitResumeController::class, 'submit']);

		Route::get('templates', [ResumeBuilderController::class, 'templates']);

		Route::get('/{slug}', [ResumeBuilderController::class, 'resume']);
		Route::put('/update/{id}', [ResumeBuilderController::class, 'update']);
	});
});
