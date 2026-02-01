<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->two_factor_confirmed_at) {
            // Check if 2FA session is verified
            if (!$request->session()->has('2fa_verified')) {
                // Allow access to 2FA verification routes and logout
                if (!$request->is('admin/2fa/*') && !$request->is('logout')) {
                    return redirect()->route('admin.2fa.verify');
                }
            }
        }

        return $next($request);
    }
}
