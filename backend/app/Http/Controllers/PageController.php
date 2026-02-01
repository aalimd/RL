<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestSubmittedToStudent;
use App\Mail\RequestSubmittedToAdmin;


use App\Services\LetterService;
use App\Services\WizardService;

class PageController extends Controller
{
    protected $letterService;
    protected $wizardService;
    protected $telegramService;

    public function __construct(LetterService $letterService, WizardService $wizardService, \App\Services\TelegramService $telegramService)
    {
        $this->letterService = $letterService;
        $this->wizardService = $wizardService;
        $this->telegramService = $telegramService;
    }
    /**
     * Get public settings for views
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
            // Landing Page
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
            // Tracking Page
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
     * Landing page
     */
    public function landing()
    {
        $settings = $this->getPublicSettings();
        return view('landing', compact('settings'));
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        $settings = $this->getPublicSettings();
        return view('auth.login', compact('settings'));
    }

    /**
     * Handle login submission
     */
    public function login(Request $request)
    {
        $request->validate([
            'loginIdentifier' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email or username
        $user = User::where('email', $request->loginIdentifier)
            ->orWhere('username', $request->loginIdentifier)
            ->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['loginIdentifier' => 'Invalid credentials'])->withInput();
        }

        // Log user in
        auth()->login($user);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect('/admin/dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Public request form - Multi-step wizard
     */
    public function publicRequest(Request $request)
    {
        $settings = $this->getPublicSettings();
        $formConfig = $this->wizardService->getFormConfig();
        $templates = $this->wizardService->getTemplates($formConfig);

        // Get current step (default: 1)
        $step = (int) $request->query('step', 1);
        if ($step < 1 || $step > 3)
            $step = 1;

        // Get existing form data from session
        $formData = session('wizard_data', []);

        return view('public.request', compact('settings', 'templates', 'step', 'formData', 'formConfig'));
    }

    /**
     * Handle wizard form submission
     */
    /**
     * Handle wizard form submission
     */
    public function handleWizard(Request $request)
    {
        $settings = $this->getPublicSettings();
        $formConfig = $this->wizardService->getFormConfig();
        $templates = $this->wizardService->getTemplates($formConfig);

        $currentStep = (int) $request->input('step', 1);
        $action = $request->input('action', 'next');

        // Get form data from request
        $formData = session('wizard_data', []);
        $newData = $request->input('data', []);
        $formData = array_merge($formData, $newData);

        // Handle back action
        if ($action === 'back') {
            $step = max(1, $currentStep - 1);
            session(['wizard_data' => $formData]);
            return view('public.request', compact('settings', 'templates', 'step', 'formData', 'formConfig'));
        }

        // Validation based on current step
        if ($currentStep == 1) {
            $this->wizardService->validateStep1($request, $formConfig);
        } elseif ($currentStep == 2) {
            $errors = $this->wizardService->validateStep2($formData);
            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }
        }

        // Handle submit action
        if ($action === 'submit' && $currentStep == 3) {
            $this->wizardService->validateStep3($request, $formConfig);

            // Generate tracking ID
            $trackingId = 'REC-' . date('Y') . '-' . strtoupper(\Str::random(8));

            try {
                // Create request with transaction
                $newRequest = \Illuminate\Support\Facades\DB::transaction(function () use ($formData, $trackingId) {
                    return \App\Models\Request::create([
                        'tracking_id' => $trackingId,
                        'student_name' => $formData['student_name'] ?? '',
                        'middle_name' => $formData['middle_name'] ?? null,
                        'last_name' => $formData['last_name'] ?? null,
                        'student_email' => $formData['student_email'] ?? '',
                        'phone' => $formData['phone'] ?? null,
                        'verification_token' => $formData['verification_token'] ?? '',
                        'verify_token' => \Str::random(32), // For QR code verification
                        'university' => $formData['university'] ?? null,
                        'purpose' => $formData['purpose'] ?? null,
                        'deadline' => $formData['deadline'] ?? null,
                        'training_period' => $formData['training_period'] ?? null,
                        'custom_content' => $formData['custom_content'] ?? null,
                        'template_id' => $formData['template_id'] ?? null,
                        'status' => 'Submitted',
                        'form_data' => $formData,
                    ]);
                });

            } catch (\Exception $e) {
                \Log::error('Failed to create request: ' . $e->getMessage());
                return back()->with('error', 'Failed to submit request. Please try again or contact support.');
            }

            // Send email notifications
            try {
                // Send confirmation to student
                Mail::to($newRequest->student_email)->send(new RequestSubmittedToStudent($newRequest));

                // Send notification to admin(s) only
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new RequestSubmittedToAdmin($newRequest));
                }
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Email notification failed: ' . $e->getMessage());
            }

            // Send Telegram Notification
            try {
                $this->telegramService->sendRequestNotification($newRequest);
            } catch (\Exception $e) {
                \Log::error('Telegram notification failed: ' . $e->getMessage());
            }

            // Clear wizard session
            session()->forget('wizard_data');

            return redirect()->route('public.request')->with([
                'success' => true,
                'tracking_id' => $trackingId,
                'telegram_bot_username' => $this->telegramService->getBotUsername()
            ]);
        }

        // Proceed to next step
        $step = min(3, $currentStep + 1);
        session(['wizard_data' => $formData]);

        return view('public.request', compact('settings', 'templates', 'step', 'formData', 'formConfig'));
    }



    /**
     * Track request status - shows form only, no data exposed without verification
     */
    public function tracking($id = null)
    {
        $settings = $this->getPublicSettings();

        // Security: Don't expose request data directly, only show the form
        // User must submit tracking ID + verification_token to see details
        return view('public.tracking', [
            'settings' => $settings,
            'request' => null,  // Never show data without verification
            'id' => $id  // Pre-fill tracking ID if provided
        ]);
    }

    /**
     * Handle tracking form submission
     */
    public function doTracking(Request $request)
    {
        $request->validate([
            'trackingId' => 'required|string',
            'verificationToken' => 'required|string',
        ]);

        $settings = $this->getPublicSettings();

        $result = \App\Models\Request::where('tracking_id', $request->trackingId)
            ->where('verification_token', $request->verificationToken)
            ->first();

        if (!$result) {
            return back()->with('error', 'Tracking details not found. Please check your inputs.');
        }

        // 2FA Check: If Telegram is linked, enforce OTP
        if ($result->telegram_chat_id) {
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);

            // Store in session with expiry (5 mins)
            session([
                '2fa_otp' => $otp,
                '2fa_expires' => now()->addMinutes(5),
                '2fa_request_id' => $result->id,
                '2fa_tracking_id' => $result->tracking_id // For display/context
            ]);

            // Send via Telegram
            $this->telegramService->sendMessageToChat(
                $result->telegram_chat_id,
                "🔐 <b>Verification Code</b>\n\nYour code to access request details is: <code>$otp</code>\n\nDo not share this code with anyone."
            );

            // Redirect to Verify Page
            return redirect()->route('public.tracking.verify');
        }

        // No 2FA -> Show details directly
        return view('public.tracking', [
            'settings' => $settings,
            'request' => $result,
            'id' => $request->trackingId,
            'telegramBotUsername' => $this->telegramService->getBotUsername()
        ]);
    }

    /**
     * Show 2FA Verification Form
     */
    public function show2FAVerify()
    {
        if (!session('2fa_otp')) {
            return redirect()->route('public.tracking');
        }

        $settings = $this->getPublicSettings();
        return view('public.2fa_verify', compact('settings'));
    }

    /**
     * Handle 2FA Submission
     */
    public function handle2FAVerify(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        $sessionOtp = session('2fa_otp');
        $expires = session('2fa_expires');
        $requestId = session('2fa_request_id');

        if (!$sessionOtp || !$requestId || now()->greaterThan($expires)) {
            return back()->with('error', 'Session expired. Please try tracking again.');
        }

        if ($request->otp != $sessionOtp) {
            return back()->with('error', 'Invalid verification code.');
        }

        // OTP Valid - Clear session 2FA data but keep request context? 
        // Or just load the view directly.

        $result = \App\Models\Request::find($requestId);

        // Clear OTP session to prevent replay (optional, or keep for refresh?)
        // Better to clear
        session()->forget(['2fa_otp', '2fa_expires']);

        return view('public.tracking', [
            'settings' => $this->getPublicSettings(),
            'request' => $result,
            'id' => $result->tracking_id,
            'telegramBotUsername' => $this->telegramService->getBotUsername()
        ]);
    }
    /**
     * View approved letter public page
     */
    /**
     * View approved letter public page
     */
    public function viewLetter($tracking_id, Request $httpRequest)
    {
        // Fix 7: IDOR Protection
        // Search by tracking_id (Random String) instead of Auto-Increment ID
        $request = \App\Models\Request::where('tracking_id', $tracking_id)->firstOrFail();

        // Secondary Check: Verify Token (Student ID) matches
        $token = $httpRequest->query('token');

        // Strict Comparison
        if (!$token || $token !== $request->verification_token) {
            abort(403, 'Invalid access token. Please ensure you are using the correct link.');
        }

        if ($request->status !== 'Approved') {
            abort(403, 'This request has not been approved yet.');
        }

        // Get Template - with try/catch for encrypted form_data
        $templateId = null;
        try {
            $formData = $request->form_data ?? [];
            $templateId = $formData['template_id'] ?? null;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            \Illuminate\Support\Facades\Log::error('Decryption failed for request ' . $request->id . ': ' . $e->getMessage());
            $formData = [];
        }

        if ($templateId) {
            $template = \App\Models\Template::find($templateId);
        }

        // Fallback to template_id column or active template
        if (!isset($template) || !$template) {
            if ($request->template_id) {
                $template = \App\Models\Template::find($request->template_id);
            }
        }
        if (!isset($template) || !$template) {
            $template = \App\Models\Template::where('is_active', true)->first();
        }

        if (!$template) {
            abort(404, 'Template not found');
        }

        $content = $this->letterService->generateLetterContent($request, $template);

        // Prepare View Data - use Purifier for proper XSS sanitization
        $data = [
            'request' => $request,
            'layout' => $content['layout'],
            'header' => $this->letterService->sanitizeHtml($content['header']),
            'body' => $this->letterService->sanitizeHtml($content['body']),
            'footer' => $this->letterService->sanitizeHtml($content['footer']),
            'signature' => $content['signature'],
            'qrCode' => $content['qrCode'] ?? '',
        ];

        return view('public.letter', $data);
    }

    /**
     * Download letter as PDF
     */
    /**
     * Download letter as PDF
     */
    public function downloadPdf($tracking_id, Request $httpRequest)
    {
        // Fix 7: Search by tracking_id
        $request = \App\Models\Request::where('tracking_id', $tracking_id)->firstOrFail();

        // Secondary Check: Verify Token
        $token = $httpRequest->query('token');

        if (!$token || $token !== $request->verification_token) {
            abort(403, 'Invalid access token.');
        }

        if ($request->status !== 'Approved') {
            abort(403, 'This request has not been approved yet.');
        }

        // Get Template
        $templateId = null;
        try {
            $formData = $request->form_data ?? [];
            $templateId = $formData['template_id'] ?? null;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $formData = [];
        }

        if ($templateId) {
            $template = \App\Models\Template::find($templateId);
        }

        if (!isset($template) || !$template) {
            if ($request->template_id) {
                $template = \App\Models\Template::find($request->template_id);
            }
        }
        if (!isset($template) || !$template) {
            $template = \App\Models\Template::where('is_active', true)->first();
        }

        if (!$template) {
            abort(404, 'Template not found');
        }

        $content = $this->letterService->generateLetterContent($request, $template);
        $allowedTags = '<p><br><b><i><u><strong><em><table><thead><tbody><tr><th><td><ul><ol><li><img><span><div><h1><h2><h3><h4><h5><h6><font><center><blockquote><hr><a>';

        $data = [
            'request' => $request,
            'layout' => $content['layout'],
            'header' => strip_tags($content['header'], $allowedTags),
            'body' => strip_tags($content['body'], $allowedTags),
            'footer' => strip_tags($content['footer'], $allowedTags),
            'signature' => $content['signature'],
            'qrCode' => $content['qrCode'] ?? '',
        ];

        // PDF default font override if needed or ensure defaults
        if (!isset($data['layout']['fontFamily']))
            $data['layout']['fontFamily'] = 'DejaVu Sans';

        if (!isset($data['layout']['direction']))
            $data['layout']['direction'] = 'ltr';

        try {
            $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('pdf.letter', $data);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'Recommendation_Letter_' . $request->tracking_id . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF Generation Failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }
}
