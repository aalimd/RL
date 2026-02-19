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
            'borderRadius',
            'shadowIntensity',
            'buttonGradient',
            'glassEffect',
            'fontFamily',
            'headingFont', // Added Heading Font
            'welcomeTitle',
            // 'welcomeText',
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
            'trackingRevisionMessage',
            'trackingTitle',
            'trackingSubtitle',
            'trackingSearchBtn',
            // Request Page
            'requestTitle',
            'requestSubtitle',
            'requestSubmitBtn',
        ];

        return Settings::whereIn('key', $publicKeys)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Clear temporary wizard edit session data.
     */
    private function clearEditWizardSession(): void
    {
        session()->forget([
            'wizard_data',
            'wizard_mode',
            'wizard_tracking_id',
            'wizard_edit_expires_at',
            'wizard_edit_original_updated_at',
        ]);
    }

    /**
     * Check whether the student has a valid verified tracking session.
     */
    private function hasValidTrackingVerification(?\App\Models\Request $requestModel = null): bool
    {
        $verifiedRequestId = session('tracking_verified_request_id');
        $verifiedTrackingId = session('tracking_verified_tracking_id');
        $verifiedUntil = (int) session('tracking_verified_until', 0);

        if (!$verifiedRequestId || !$verifiedTrackingId || !$verifiedUntil || $verifiedUntil < now()->timestamp) {
            session()->forget(['tracking_verified_request_id', 'tracking_verified_tracking_id', 'tracking_verified_until']);
            return false;
        }

        if (!$requestModel) {
            return true;
        }

        return (int) $verifiedRequestId === (int) $requestModel->id
            && (string) $verifiedTrackingId === (string) $requestModel->tracking_id;
    }

    /**
     * Check whether the temporary edit wizard session is active.
     */
    private function hasActiveEditSession(): bool
    {
        $editExpiresAt = (int) session('wizard_edit_expires_at', 0);
        return $editExpiresAt >= now()->timestamp;
    }

    /**
     * Mask email for UI display (e.g. al***@gm***.com).
     */
    private function maskEmail(string $email): string
    {
        $email = trim($email);
        if ($email === '' || !str_contains($email, '@')) {
            return 'your email address';
        }

        [$localPart, $domainPart] = explode('@', $email, 2);
        $localVisible = substr($localPart, 0, min(2, strlen($localPart)));
        $localMasked = $localVisible . str_repeat('*', max(strlen($localPart) - strlen($localVisible), 1));

        $domainSegments = explode('.', $domainPart);
        $baseDomain = array_shift($domainSegments) ?? '';
        $baseVisible = substr($baseDomain, 0, min(2, strlen($baseDomain)));
        $baseMasked = $baseVisible . str_repeat('*', max(strlen($baseDomain) - strlen($baseVisible), 1));
        $suffix = !empty($domainSegments) ? '.' . implode('.', $domainSegments) : '';

        return $localMasked . '@' . $baseMasked . $suffix;
    }

    /**
     * Build wizard form data from a stored request.
     */
    private function buildWizardDataFromRequest(\App\Models\Request $requestModel): array
    {
        $storedFormData = is_array($requestModel->form_data) ? $requestModel->form_data : [];

        return array_merge($storedFormData, [
            'student_name' => $requestModel->student_name,
            'middle_name' => $requestModel->middle_name,
            'last_name' => $requestModel->last_name,
            'student_email' => $requestModel->student_email,
            'phone' => $requestModel->phone,
            'university' => $requestModel->university,
            'verification_token' => $requestModel->verification_token,
            'training_period' => $requestModel->training_period,
            'purpose' => $requestModel->purpose,
            'deadline' => ($requestModel->deadline instanceof \DateTimeInterface) ? $requestModel->deadline->format('Y-m-d') : null,
            'content_option' => $requestModel->content_option ?? (!empty($requestModel->custom_content) ? 'custom' : 'template'),
            'custom_content' => $requestModel->custom_content,
            'template_id' => $requestModel->template_id,
            'admin_message' => $requestModel->admin_message,
        ]);
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

        if (!$user->is_active) {
            return back()->withErrors(['loginIdentifier' => 'Account is deactivated. Please contact support.'])->withInput();
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

        $isEditMode = session('wizard_mode') === 'edit';
        if ($isEditMode) {
            $editTrackingId = session('wizard_tracking_id');
            $editRequest = $editTrackingId ? \App\Models\Request::where('tracking_id', $editTrackingId)->first() : null;

            if (
                !$editRequest ||
                !$this->hasActiveEditSession() ||
                !$this->hasValidTrackingVerification($editRequest) ||
                $editRequest->status !== 'Needs Revision'
            ) {
                $this->clearEditWizardSession();
                $routeParams = $editTrackingId ? ['id' => $editTrackingId] : [];
                return redirect()->route('public.tracking', $routeParams)
                    ->with('error', 'Your edit session is no longer valid. Please verify your request again.');
            }
        }

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
        $isEditMode = session('wizard_mode') === 'edit';
        $editTrackingId = session('wizard_tracking_id');
        $editingRequest = null;

        if ($isEditMode) {
            $editingRequest = $editTrackingId ? \App\Models\Request::where('tracking_id', $editTrackingId)->first() : null;
            if (
                !$editingRequest ||
                !$this->hasActiveEditSession() ||
                !$this->hasValidTrackingVerification($editingRequest) ||
                $editingRequest->status !== 'Needs Revision'
            ) {
                $this->clearEditWizardSession();
                $routeParams = $editTrackingId ? ['id' => $editTrackingId] : [];
                return redirect()->route('public.tracking', $routeParams)
                    ->with('error', 'Your edit session expired or this request is no longer editable.');
            }
        }

        // Get form data from request
        $formData = session('wizard_data', []);
        $newData = $request->input('data', []);
        $formData = array_merge($formData, $newData);

        // Handle back action
        if ($action === 'back') {
            $step = max(1, $currentStep - 1);
            $sessionPayload = ['wizard_data' => $formData];
            if ($isEditMode) {
                $sessionPayload['wizard_edit_expires_at'] = now()->addMinutes(30)->timestamp;
            }
            session($sessionPayload);
            return view('public.request', compact('settings', 'templates', 'step', 'formData', 'formConfig'));
        }

        // Validation based on current step
        if ($currentStep == 1) {
            $this->wizardService->validateStep1($request, $formConfig);
        } elseif ($currentStep == 2) {
            $errors = $this->wizardService->validateStep2($formData, $formConfig);
            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }

            // Normalize content selection against admin form settings.
            $formData = array_merge($formData, $this->wizardService->resolveContentData($formData, $formConfig));
        }

        // Handle submit action
        if ($action === 'submit' && $currentStep == 3) {
            $this->wizardService->validateStep3($request, $formConfig);
            $formData = array_merge($formData, $this->wizardService->resolveContentData($formData, $formConfig));

            // Re-check step 2 constraints in case user jumps directly to submit.
            $step2Errors = $this->wizardService->validateStep2($formData, $formConfig);
            if (!empty($step2Errors)) {
                return back()->withErrors($step2Errors)->withInput();
            }

            $requiredIdentityErrors = [];
            if (empty(trim((string) ($formData['student_email'] ?? '')))) {
                $requiredIdentityErrors['student_email'] = 'Email is required.';
            }
            if (empty(trim((string) ($formData['verification_token'] ?? '')))) {
                $requiredIdentityErrors['verification_token'] = 'ID number is required.';
            }
            if (!empty($requiredIdentityErrors)) {
                return back()->withErrors($requiredIdentityErrors)->withInput();
            }

            if ($isEditMode) {
                $originalUpdatedAt = session('wizard_edit_original_updated_at');

                try {
                    $updatedRequest = \Illuminate\Support\Facades\DB::transaction(function () use ($editTrackingId, $formData, $originalUpdatedAt) {
                        $requestModel = \App\Models\Request::where('tracking_id', $editTrackingId)->lockForUpdate()->first();

                        if (!$requestModel) {
                            throw new \RuntimeException('REQUEST_NOT_FOUND');
                        }

                        if ($requestModel->status !== 'Needs Revision') {
                            throw new \RuntimeException('REQUEST_NOT_EDITABLE');
                        }

                        if (
                            $originalUpdatedAt &&
                            $requestModel->updated_at &&
                            $requestModel->updated_at->toDateTimeString() !== $originalUpdatedAt
                        ) {
                            throw new \RuntimeException('REQUEST_STALE');
                        }

                        $existingFormData = is_array($requestModel->form_data) ? $requestModel->form_data : [];
                        $contentOption = $formData['content_option'] ?? 'template';
                        $templateId = $contentOption === 'template'
                            ? (!empty($formData['template_id']) ? (int) $formData['template_id'] : null)
                            : null;
                        $customContent = $contentOption === 'custom' ? ($formData['custom_content'] ?? null) : null;
                        $deadline = !empty($formData['deadline']) ? $formData['deadline'] : null;
                        $trainingPeriod = !empty($formData['training_period']) ? $formData['training_period'] : null;

                        $mergedFormData = array_merge($existingFormData, $formData);
                        $mergedFormData['content_option'] = $contentOption;
                        $mergedFormData['template_id'] = $templateId;
                        $mergedFormData['custom_content'] = $customContent;
                        $mergedFormData['deadline'] = $deadline;
                        $mergedFormData['training_period'] = $trainingPeriod;

                        $requestModel->update([
                            'student_name' => $formData['student_name'] ?? $requestModel->student_name,
                            'middle_name' => $formData['middle_name'] ?? null,
                            'last_name' => $formData['last_name'] ?? null,
                            'student_email' => $formData['student_email'] ?? $requestModel->student_email,
                            'phone' => $formData['phone'] ?? null,
                            'verification_token' => $formData['verification_token'] ?? $requestModel->verification_token,
                            'university' => $formData['university'] ?? null,
                            'purpose' => $formData['purpose'] ?? null,
                            'deadline' => $deadline,
                            'training_period' => $trainingPeriod,
                            'content_option' => $contentOption,
                            'template_id' => $templateId,
                            'custom_content' => $customContent,
                            'status' => 'Under Review',
                            'admin_message' => null,
                            'rejection_reason' => null,
                            'form_data' => $mergedFormData,
                        ]);

                        return $requestModel->fresh();
                    });
                } catch (\RuntimeException $e) {
                    $this->clearEditWizardSession();
                    $routeParams = $editTrackingId ? ['id' => $editTrackingId] : [];

                    if (in_array($e->getMessage(), ['REQUEST_NOT_FOUND', 'REQUEST_NOT_EDITABLE', 'REQUEST_STALE'])) {
                        $message = $e->getMessage() === 'REQUEST_STALE'
                            ? 'This request was updated by admin while you were editing. Please verify and try again.'
                            : 'This request is no longer available for editing.';
                        return redirect()->route('public.tracking', $routeParams)->with('error', $message);
                    }

                    \Log::error('Failed to update request: ' . $e->getMessage());
                    return back()->with('error', 'Failed to update request. Please try again or contact support.');
                } catch (\Exception $e) {
                    \Log::error('Failed to update request: ' . $e->getMessage());
                    return back()->with('error', 'Failed to update request. Please try again or contact support.');
                }

                try {
                    \App\Models\AuditLog::create([
                        'action' => 'STUDENT_REQUEST_RESUBMITTED',
                        'details' => "Student resubmitted request {$updatedRequest->tracking_id} for review",
                        'request_id' => $updatedRequest->id,
                        'ip_address' => $request->ip(),
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Audit log write failed for student resubmission: ' . $e->getMessage());
                }

                try {
                    $admins = User::where('role', 'admin')->get();
                    $adminLink = url('/admin/requests/' . $updatedRequest->id);
                    foreach ($admins as $admin) {
                        Mail::raw(
                            "Request {$updatedRequest->tracking_id} has been revised by the student and is ready for review.\n\nOpen: {$adminLink}",
                            function ($message) use ($admin, $updatedRequest) {
                                $message->to($admin->email)->subject("Revised Request Submitted: {$updatedRequest->tracking_id}");
                            }
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error('Revision resubmission email notification failed: ' . $e->getMessage());
                }

                $this->clearEditWizardSession();

                return redirect()->route('public.request')->with([
                    'success' => true,
                    'success_title' => 'Request Updated Successfully!',
                    'success_subtitle' => 'Your revisions were submitted and are now under review.',
                    'tracking_id' => $updatedRequest->tracking_id,
                    'telegram_bot_username' => $this->telegramService->getBotUsername(),
                ]);
            }

            // Generate tracking ID
            $trackingId = 'REC-' . date('Y') . '-' . strtoupper(\Str::random(8));

            try {
                // Create request with transaction
                $newRequest = \Illuminate\Support\Facades\DB::transaction(function () use ($formData, $trackingId) {
                    $contentOption = $formData['content_option'] ?? 'template';
                    $templateId = $contentOption === 'template'
                        ? (!empty($formData['template_id']) ? (int) $formData['template_id'] : null)
                        : null;
                    $customContent = $contentOption === 'custom' ? ($formData['custom_content'] ?? null) : null;

                    $storedFormData = array_merge($formData, [
                        'content_option' => $contentOption,
                        'template_id' => $templateId,
                        'custom_content' => $customContent,
                    ]);

                    return \App\Models\Request::create([
                        'tracking_id' => $trackingId,
                        'student_name' => $formData['student_name'] ?? '',
                        'middle_name' => $formData['middle_name'] ?? null,
                        'last_name' => $formData['last_name'] ?? null,
                        'student_email' => $formData['student_email'] ?? '',
                        'phone' => $formData['phone'] ?? null,
                        'verification_token' => $formData['verification_token'] ?? \Str::random(60),
                        'verify_token' => \Str::random(32), // For QR code verification
                        'university' => $formData['university'] ?? null,
                        'purpose' => $formData['purpose'] ?? null,
                        'deadline' => $formData['deadline'] ?? null,
                        'training_period' => $formData['training_period'] ?? null,
                        'content_option' => $contentOption,
                        'custom_content' => $customContent,
                        'template_id' => $templateId,
                        'status' => 'Submitted',
                        'form_data' => $storedFormData,
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
        $sessionPayload = ['wizard_data' => $formData];
        if ($isEditMode) {
            $sessionPayload['wizard_edit_expires_at'] = now()->addMinutes(30)->timestamp;
        }
        session($sessionPayload);

        return view('public.request', compact('settings', 'templates', 'step', 'formData', 'formConfig'));
    }

    /**
     * Initialize secure student edit mode after tracking verification.
     */
    public function initializeEdit(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|string',
        ]);

        $trackingId = trim((string) $request->input('tracking_id'));
        $requestModel = \App\Models\Request::where('tracking_id', $trackingId)->first();

        if (!$requestModel) {
            return redirect()->route('public.tracking', ['id' => $trackingId])
                ->with('error', 'Request not found.');
        }

        if (!$this->hasValidTrackingVerification($requestModel)) {
            return redirect()->route('public.tracking', ['id' => $trackingId])
                ->with('error', 'Please verify your request with OTP before editing.');
        }

        if ($requestModel->status !== 'Needs Revision') {
            return redirect()->route('public.tracking', ['id' => $trackingId])
                ->with('error', 'This request is not currently available for editing.');
        }

        $this->clearEditWizardSession();
        session([
            'wizard_data' => $this->buildWizardDataFromRequest($requestModel),
            'wizard_mode' => 'edit',
            'wizard_tracking_id' => $requestModel->tracking_id,
            'wizard_edit_expires_at' => now()->addMinutes(30)->timestamp,
            'wizard_edit_original_updated_at' => optional($requestModel->updated_at)->toDateTimeString(),
        ]);

        return redirect()->route('public.request', ['step' => 1])
            ->with('info', 'Update the requested fields and submit your revisions.');
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

        $this->clearEditWizardSession();
        session()->forget([
            'tracking_verified_request_id',
            'tracking_verified_tracking_id',
            'tracking_verified_until',
            '2fa_otp',
            '2fa_expires',
            '2fa_request_id',
            '2fa_tracking_id',
            '2fa_delivery_method',
            '2fa_delivery_hint',
        ]);

        $result = \App\Models\Request::where('tracking_id', $request->trackingId)
            ->where('verification_token', $request->verificationToken)
            ->first();

        if (!$result) {
            return back()->with('error', 'Tracking details not found. Please check your inputs.');
        }

        // Always Enforce 2FA/OTP for Security
        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);

        // Store in session with expiry (5 mins)
        session([
            '2fa_otp' => $otp,
            '2fa_expires' => now()->addMinutes(5),
            '2fa_request_id' => $result->id,
            '2fa_tracking_id' => $result->tracking_id, // For display/context
            '2fa_delivery_method' => 'email',
            '2fa_delivery_hint' => $this->maskEmail((string) $result->student_email),
        ]);

        // Email is the primary OTP channel for student access.
        try {
            Mail::to($result->student_email)->send(new \App\Mail\TrackingVerificationCode($result, $otp));
        } catch (\Exception $e) {
            \Log::error('Tracking OTP email failed: ' . $e->getMessage(), [
                'tracking_id' => $result->tracking_id,
                'request_id' => $result->id,
            ]);
            return back()->with('error', 'Failed to send verification code to your email. Please try again or contact support.');
        }

        // Telegram is optional and non-blocking if linked.
        if ($result->telegram_chat_id) {
            try {
                $this->telegramService->sendMessageToChat(
                    $result->telegram_chat_id,
                    "🔐 <b>Verification Code</b>\n\nYour code to access request details is: <code>$otp</code>\n\nDo not share this code with anyone."
                );
            } catch (\Exception $e) {
                \Log::warning('Tracking OTP Telegram send failed: ' . $e->getMessage(), [
                    'tracking_id' => $result->tracking_id,
                    'request_id' => $result->id,
                ]);
            }
        }

        // Redirect to Verify Page
        return redirect()->route('public.tracking.verify');
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
        $deliveryMethod = session('2fa_delivery_method', 'email');
        $deliveryHint = session('2fa_delivery_hint');

        return view('public.2fa_verify', compact('settings', 'deliveryMethod', 'deliveryHint'));
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

        $isExpired = true;
        if ($expires) {
            try {
                $isExpired = now()->greaterThan(\Illuminate\Support\Carbon::parse($expires));
            } catch (\Throwable $e) {
                $isExpired = true;
            }
        }

        if (!$sessionOtp || !$requestId || $isExpired) {
            session()->forget(['2fa_otp', '2fa_expires', '2fa_request_id', '2fa_tracking_id', '2fa_delivery_method', '2fa_delivery_hint']);
            return back()->with('error', 'Session expired. Please try tracking again.');
        }

        if ($request->otp != $sessionOtp) {
            return back()->with('error', 'Invalid verification code.');
        }

        // OTP Valid - Clear session 2FA data but keep request context? 
        // Or just load the view directly.

        $result = \App\Models\Request::find($requestId);
        if (!$result) {
            session()->forget(['2fa_otp', '2fa_expires', '2fa_request_id', '2fa_tracking_id', '2fa_delivery_method', '2fa_delivery_hint']);
            return redirect()->route('public.tracking')
                ->with('error', 'Request not found. Please track again.');
        }

        session([
            'tracking_verified_request_id' => $result->id,
            'tracking_verified_tracking_id' => $result->tracking_id,
            'tracking_verified_until' => now()->addMinutes(30)->timestamp,
        ]);

        // Clear OTP session to prevent replay (optional, or keep for refresh?)
        // Better to clear
        session()->forget(['2fa_otp', '2fa_expires', '2fa_request_id', '2fa_tracking_id', '2fa_delivery_method', '2fa_delivery_hint']);

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

        $template = null;
        if ($templateId) {
            $template = \App\Models\Template::find($templateId);
        }

        if (!$template) {
            $template = $this->resolveTemplate($request);
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
        $formData = $request->form_data ?? [];
        $templateId = $formData['template_id'] ?? null;

        $template = null;
        if ($templateId) {
            $template = \App\Models\Template::find($templateId);
        }

        if (!$template) {
            $template = $this->resolveTemplate($request);
        }

        if (!$template) {
            abort(404, 'Template not found');
        }

        $content = $this->letterService->generateLetterContent($request, $template);

        $data = [
            'request' => $request,
            'layout' => $content['layout'],
            'header' => $this->letterService->sanitizeHtml($content['header']),
            'body' => $this->letterService->sanitizeHtml($content['body']),
            'footer' => $this->letterService->sanitizeHtml($content['footer']),
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

    /**
     * Helper to resolve template from request or defaults
     */
    private function resolveTemplate(\App\Models\Request $request)
    {
        $template = null;
        if ($request->template_id) {
            $template = \App\Models\Template::find($request->template_id);
        }

        if (!$template) {
            $template = \App\Models\Template::where('is_active', true)->first();
        }

        return $template;
    }
}
