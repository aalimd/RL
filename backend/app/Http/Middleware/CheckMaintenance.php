<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Always allow Admin Panel routes and Login/Logout
        if ($request->is('admin/*') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        // 2. Check if Maintenance Mode is enabled in DB
        // We use cache to avoid hitting DB on every single request if possible, 
        // but for simplicity & reliability in this setup, direct DB query is fine for now 
        // or we trust the Settings model might cache internally if optimized later.
        $maintenanceMode = cache()->remember('maintenance_mode', 60, function () {
            return Settings::where('key', 'maintenanceMode')->value('value');
        });

        if ($maintenanceMode === 'true') {
            // 3. Allow Authenticated Admins/Editors to bypass
            $user = Auth::user();
            if ($user && in_array($user->role, ['admin', 'editor'], true)) {
                return $next($request);
            }

            // 4. Fetch Custom Message
            $message = Settings::where('key', 'maintenanceMessage')->value('value');
            if (empty($message)) {
                $message = 'We are currently performing scheduled maintenance. Please check back soon.';
            }

            // 5. Return 503 Service Unavailable with Maintenance View
            return response()->view('maintenance', compact('message'), 503);
        }

        return $next($request);
    }
}
