<?php

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Controllers\PdfPreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {

    Route::get('/dev/preview/pdf/{resume}', [PdfPreviewController::class, 'show'])
        ->name('dev.preview.pdf')
        ->middleware(['builder.dev-login', 'builder.admin']);

});

Route::get('/preview/pdf/{resume}', [PdfPreviewController::class, 'show'])
    ->name('preview.pdf')
    ->middleware(['signed']);