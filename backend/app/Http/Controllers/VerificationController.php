<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Settings;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Get public settings for views (Helper)
     */
    private function getPublicSettings(): array
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
            'showBranding',
            'heroTitle1',
            'heroTitle2',
            'heroDescription',
            'heroPrimaryBtn',
            'heroSecondaryBtn',
            'feature1Icon',
            'feature1Title',
            'feature1Text',
            'feature2Icon',
            'feature2Title',
            'feature2Text',
            'feature3Icon',
            'feature3Title',
            'feature3Text',
            'footerText',
            'trackingFixedMessage',
            'trackingPendingMessage',
            'trackingReviewMessage',
            'trackingApprovedMessage',
            'trackingRejectedMessage',
            'trackingRevisionMessage'
        ];

        return Settings::whereIn('key', $publicKeys)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Verify a request by its token
     */
    public function verify($token)
    {
        $settings = $this->getPublicSettings();
        $request = RequestModel::where('verify_token', $token)->first();

        // If not found or not approved, show invalid page
        if (!$request || $request->status !== 'Approved') {
            return view('public.verify', [
                'status' => 'invalid',
                'settings' => $settings
            ]);
        }

        // Return valid view with request details
        return view('public.verify', [
            'status' => 'valid',
            'request' => $request,
            'settings' => $settings
        ]);
    }
}
