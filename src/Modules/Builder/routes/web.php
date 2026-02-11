<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\PdfPreviewController;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\V2\TemplatePreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {

    Route::get('/dev/preview/pdf/{resume}', [PdfPreviewController::class, 'show'])
        ->name('dev.preview.pdf')
        ->middleware(['builder.dev-login', 'builder.admin']);

});

Route::get('/preview/pdf/{resume}', [PdfPreviewController::class, 'show'])
    ->name('preview.pdf')
    ->middleware(['signed:relative']);


Route::get('/preview/html/{resume}', [TemplatePreviewController::class, 'show'])
    ->name('resume.preview.html');