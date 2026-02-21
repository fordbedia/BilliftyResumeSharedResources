<?php

use BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\BillingController;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\Social\SocialAuthController;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\StripeWebhookController;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\UserController;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers\UserDataExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
	Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect']);
	Route::get('/{provider}/callback', [SocialAuthController::class, 'callback']);
});

Route::prefix('v1')->group(function () {
	Route::prefix('user')->group(function () {
		Route::post('/authenticate', [UserController::class, 'authenticate']);
		Route::middleware(['auth.cookie', 'auth:api'])->group(function () {
			Route::get('/me', [UserController::class, 'me']);
			Route::post('/logout', [UserController::class, 'logout']);
			Route::put('/update/{id}', [UserController::class, 'update']);
			Route::put('/update-password', [UserController::class, 'updatePassword']);

			Route::post('/billing/checkout', [BillingController::class, 'createCheckoutSession']);
    		Route::post('/billing/portal', [BillingController::class, 'createPortalSession']);
			Route::get('/billing/info', [BillingController::class, 'subscriptionInfo']);

			Route::post('/settings/data-export', [UserDataExportController::class, 'requestExport']);
			Route::get('/settings/data-export/latest', [UserDataExportController::class, 'latest']);
			Route::get('/settings/data-export/{export}/download', [UserDataExportController::class, 'download'])
				->name('data-export.download')
				->middleware('signed'); // important
		});
		Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

		// ----------------------------------------------------------------------------
		// No routes after this line.
		// ----------------------------------------------------------------------------
		Route::apiResource('/', UserController::class);
	});
});
