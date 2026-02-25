<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanUploadResumePhoto
{
	public function handle(Request $request, Closure $next): Response
	{
		$hasUploadImage = $request->hasFile('basics_image') || $request->has('basics_image');
		if (!$hasUploadImage) {
			return $next($request);
		}

		$plan = strtolower((string) data_get($request->user(), 'plan', 'free'));
		if ($plan === 'free') {
			throw ValidationException::withMessages([
				'basics_image' => 'Resume photo upload is available on Pro plan only.',
			]);
		}

		return $next($request);
	}
}
