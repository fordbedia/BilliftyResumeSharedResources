<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\ResumePdfController;
use Illuminate\Support\Facades\Route;

Route::get('/resume.pdf', [ResumePdfController::class, 'download']);
Route::get('/resume.preview', [ResumePdfController::class, 'stream']);
Route::get('/resume/export', [ResumePdfController::class, 'exportJson']);
Route::get('/resume/build', [ResumePdfController::class, 'buildFromStrings']);
