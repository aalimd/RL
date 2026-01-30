<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // HSTS (Strict-Transport-Security) for Production
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy - Balanced for functionality and security
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://cdn.tiny.cloud https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tiny.cloud",
            "font-src 'self' https://fonts.gstatic.com data: https://cdn.tiny.cloud",
            "img-src 'self' data: https: blob: https://cdn.tiny.cloud",
            "connect-src 'self' https://cdn.tiny.cloud https://unpkg.com https://cdn.jsdelivr.net",
            "frame-ancestors 'self'",
            "object-src 'none'",  // Prevent Flash/Java plugins
            "base-uri 'self'",   // Prevent base tag injection
            "form-action 'self' https://api.telegram.org", // Limit form submissions
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
