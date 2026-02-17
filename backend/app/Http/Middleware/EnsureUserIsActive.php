<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Block access for deactivated users on existing sessions/tokens.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && isset($user->is_active) && !$user->is_active) {
            // Revoke the current API token if this is token-based auth.
            if (method_exists($user, 'currentAccessToken')) {
                $token = $user->currentAccessToken();
                if ($token) {
                    $token->delete();
                }
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Account is deactivated. Please contact support.',
                ], 403);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'loginIdentifier' => 'Account is deactivated. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
