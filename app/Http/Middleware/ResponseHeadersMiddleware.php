<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // Content Security Policy
        $csp = "default-src 'self'; script-src 'self'; style-src 'self';";
        $response->headers->set('Content-Security-Policy', $csp);
        // HTTP Strict Transport Security
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=()');

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Expect-CT
        $response->headers->set('Expect-CT', 'enforce, max-age=30');

        // Cache-Control
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');

        return $response;
    }
}
