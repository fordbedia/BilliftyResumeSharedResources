<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeBasicController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeEducationController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeReferencesController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeSkillsController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeWorkController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumePdfController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\SubmitResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/resume.pdf', [ResumePdfController::class, 'download']);
Route::get('/resume.preview', [ResumePdfController::class, 'stream']);
Route::get('/resume/export', [ResumePdfController::class, 'exportJson']);
Route::get('/resume/build', [ResumePdfController::class, 'buildFromStrings']);

Route::prefix('v1')->group(function () {
	// Basic
	Route::post('resume/basics/{type}', [ResumeBasicController::class, 'handleSteps']);
	Route::post('resume/basics/index/{index}', [ResumeBasicController::class, 'handleIndex'])
		->name('resume.basic.index');
	// Skills
	Route::post('resume/skills/{type}', [ResumeSkillsController::class, 'handleSteps'])
		->name('resume.skills.type');
	Route::post('resume/skills/index/{index}', [ResumeSkillsController::class, 'handleIndex'])
		->name('resume.skills.index');
	// Work
	Route::post('resume/work/{type}', [ResumeWorkController::class, 'handleSteps'])
		->name('resume.work.type');
	Route::post('resume/work/index/{index}', [ResumeWorkController::class, 'handleIndex'])
		->name('resume.work.index');
	// Education
	Route::post('resume/education/{type}', [ResumeEducationController::class, 'handleSteps'])
		->name('resume.education.type');
	Route::post('resume/education/index/{index}', [ResumeEducationController::class, 'handleIndex'])
		->name('resume.education.index');
	// References
	Route::post('resume/references/{type}', [ResumeReferencesController::class, 'handleSteps'])
		->name('resume.references.type');
	Route::post('resume/references/index/{index}', [ResumeReferencesController::class, 'handleIndex'])
		->name('resume.references.index');

	// Submission
	Route::post('resume/submit', [SubmitResumeController::class, 'submit']);
});
