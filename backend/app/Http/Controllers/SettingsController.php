<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // GET /api/settings
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $settings = Settings::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    // PUT /api/settings
    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        // Whitelist of allowed setting keys
        $allowedKeys = [
            'siteName',
            'primaryColor',
            'secondaryColor',
            'maintenanceMode',
            'logoUrl',
            'welcomeTitle',
            'welcomeText',
            'loginBackgroundImage',
            'loginTitle',
            'loginSubtitle',
            'showBranding',
            'smtpHost',
            'smtpPort',
            'smtpUsername',
            'smtpPassword',
            'smtpEncryption',
            'mailFromAddress',
            'mailFromName',
            // Tracking page messages
            'trackingFixedMessage',
            'trackingApprovedMessage',
            'trackingRejectedMessage',
            'trackingPendingMessage',
            'trackingRevisionMessage',
            'trackingReviewMessage'
        ];

        $data = $request->except(['_token']);

        foreach ($data as $key => $value) {
            // Only allow whitelisted keys
            if (!in_array($key, $allowedKeys)) {
                continue;
            }
            Settings::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
            $updatedKeys[] = $key;
        }

        if (!empty($updatedKeys)) {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_settings',
                'details' => json_encode(['updated_keys' => $updatedKeys, 'ip' => $request->ip()]),
            ]);
        }

        return response()->json(['message' => 'Settings updated', 'settings' => $data]);
    }

    // GET /api/settings/public
    public function publicSettings()
    {
        $publicKeys = [
            'siteName',
            'primaryColor',
            'secondaryColor',
            'maintenanceMode',
            'logoUrl',
            'welcomeTitle',
            'welcomeText',
            'loginBackgroundImage',
            'loginTitle',
            'loginSubtitle',
            'showBranding' // Added logic keys
        ];

        $settings = Settings::whereIn('key', $publicKeys)->pluck('value', 'key');
        return response()->json($settings);
    }
}
