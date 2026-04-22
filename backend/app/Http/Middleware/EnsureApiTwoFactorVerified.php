<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTwoFactorVerified
{
    /**
     * Require a 2FA-verified API token for privileged users who enabled 2FA.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            !$user
            || !$user->two_factor_confirmed_at
            || !in_array($user->role, ['admin', 'editor'], true)
        ) {
            return $next($request);
        }

        if ($request->hasSession() && $request->session()->get('2fa_verified')) {
            return $next($request);
        }

        $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
        if ($token && $token->can('two-factor-authenticated')) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Two-factor verification is required for this API access.',
            'requires_two_factor' => true,
            'two_factor_method' => $user->two_factor_method,
        ], 403);
    }
}
