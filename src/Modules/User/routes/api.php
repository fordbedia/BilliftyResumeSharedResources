<?php

use BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
	Route::prefix('user')->group(function () {
		Route::post('/authenticate', [UserController::class, 'authenticate']);
		Route::middleware(['auth:api'])->group(function () {
			Route::get('/me', [UserController::class, 'me']);
			Route::put('/update/{id}', [UserController::class, 'update']);
			Route::put('/update-password', [UserController::class, 'updatePassword']);
		});
		Route::apiResource('/', UserController::class);
	});
});