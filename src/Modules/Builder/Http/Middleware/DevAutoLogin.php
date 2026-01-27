<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevAutoLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
		if (!app()->isLocal()) {
            abort(404); // or 403 — never expose in prod
        }

        if (!auth()->check()) {
            auth()->loginUsingId(1); // DEV ADMIN USER
        }
        return $next($request);
    }
}
