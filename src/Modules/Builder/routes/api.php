<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeBasicController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeEducationController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeReferencesController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeSkillsController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeTemplateController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\Resume\ResumeWorkController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumeBuilderController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumeController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumeExportController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumePdfController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\SubmitResumeController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/resume.pdf', [ResumePdfController::class, 'download']);
Route::get('/resume.preview', [ResumePdfController::class, 'stream']);
Route::get('/resume/export', [ResumePdfController::class, 'exportJson']);
Route::get('/resume/build', [ResumePdfController::class, 'buildFromStrings']);

Route::prefix('v1')->group(function () {
	Route::prefix('resume')
		->middleware(['auth:api'])
		->group(function () {
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
			Route::post('finalize/{type}', [ResumeTemplateController::class, 'handleSteps'])
				->name('finalize.type');
			Route::post('finalize/index/{index}', [ResumeTemplateController::class, 'handleIndex'])
				->name('finalize.index');

			// Submission
			Route::post('submit', [SubmitResumeController::class, 'submit']);

			Route::get('templates', [ResumeBuilderController::class, 'templates']);
			Route::get('recent', [ResumeController::class, 'recent']);

			Route::post('{id}/export', [ResumeController::class, 'startExport']);        // queues
			Route::get('{id}/export-status', [ResumeController::class, 'exportStatus']); // polling
			Route::get('{id}/export-download', [ResumeController::class, 'exportDownload']); // actual download

			Route::post('/{resume}/export/email', [ResumeExportController::class, 'sendEmail']);

			// Optional: check status (so your UI can show queued/processing/sent/failed)
			Route::get('/{resume}/export/email/status', [ResumeExportController::class, 'emailStatus']);
			Route::post('/{resume}/export/clean-up', [ResumeExportController::class, 'cleanUpDrive']);
			Route::get('/resume-preview', [ResumeBuilderController::class, 'resumePreview']);

			// ----------------------------------------------------------------------------
			// DO NOT ADD ANY ROUTES AFTER THIS LINE.
			// ----------------------------------------------------------------------------
			Route::get('/{slug}', [ResumeBuilderController::class, 'resume']);
			Route::put('/update/{id}', [ResumeBuilderController::class, 'update']);
			Route::apiResource('/', ResumeController::class);
	});

	// api.php
	Route::get('/resume/preview-link/{resume}', function (Request $request, $resume) {
		$template = $request->query('template');
		$colorScheme = $request->query('colorScheme');

		// Enforce ownership here (global scope applies because user is authenticated on API)
		$resumeModel = Resume::query()
			->whereKey($resume)
			->firstOrFail();

		$params = ['resume' => $resumeModel->id];
		if ($template) $params['template'] = $template;
		if ($colorScheme) $params['colorScheme'] = $colorScheme;

		$url = URL::temporarySignedRoute(
			'preview.pdf',
			now()->addMinutes(10),
			$params,
			absolute: false
		);

		return response()->json(['url' => $url]);
	})->middleware(['auth:api']);

});
