<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachBearerFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookiePresent = false;
        $bearerInjected = false;
        $hadAuthHeader = $request->headers->has('Authorization');
        $cookieName = (string) config('auth_cookie.cookie_name', 'billifty_at');

        $token = $request->cookie($cookieName);
        // Fallback to raw Cookie header parsing to handle duplicate cookie keys across domains.
        if (!$token) {
            $token = $this->extractCookieFromHeader((string) $request->headers->get('Cookie', ''), $cookieName);
        }
        if ($token) {
            $cookiePresent = true;
            $token = urldecode(trim((string) $token));
            $token = trim($token, "\"'");
            // Some environments decode '+' as spaces when reading cookie values.
            $token = str_replace(' ', '+', $token);
            $bearer = 'Bearer ' . $token;

            // Always prefer cookie token to avoid stale Authorization headers.
            $request->headers->set('Authorization', $bearer);
            $request->server->set('HTTP_AUTHORIZATION', $bearer);
            $request->server->set('REDIRECT_HTTP_AUTHORIZATION', $bearer);
            $_SERVER['HTTP_AUTHORIZATION'] = $bearer;
            $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = $bearer;
            $bearerInjected = true;
        }

        $response = $next($request);

        if ((bool) config('auth_cookie.debug_headers', false)) {
            $response->headers->set('X-Auth-Cookie-Present', $cookiePresent ? '1' : '0');
            $response->headers->set('X-Auth-Bearer-Injected', $bearerInjected ? '1' : '0');
            $response->headers->set('X-Auth-Had-Header', $hadAuthHeader ? '1' : '0');
            $response->headers->set('X-Auth-Bearer-Length', $bearerInjected ? (string) strlen((string) $token) : '0');
        }

        return $response;
    }

    protected function extractCookieFromHeader(string $cookieHeader, string $cookieName): ?string
    {
        if ($cookieHeader === '' || $cookieName === '') {
            return null;
        }

        $pattern = '/(?:^|;\s*)' . preg_quote($cookieName, '/') . '=([^;]*)/';
        if (!preg_match_all($pattern, $cookieHeader, $matches) || empty($matches[1])) {
            return null;
        }

        // Prefer the last value when multiple cookies with same key are present.
        $value = end($matches[1]);
        return $value !== false ? (string) $value : null;
    }
}
