<?php

namespace App\Http\Controllers;

use App\Mail\TrackingVerificationCode;
use App\Models\Request as RequestModel;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\RequestSubmittedToStudent;
use App\Mail\RequestSubmittedToAdmin;


use App\Services\LetterService;
use App\Services\LetterPdfService;
use App\Services\PublicAssetUrlService;
use App\Services\WizardService;

class PageController extends Controller
{
    private const TRACKING_ID_PATTERN = '/^REC-\d{4}-[A-Z0-9]{8}$/';
    private const TRUSTED_TRACKING_COOKIE = 'trusted_tracking_browser';
    private const TRUSTED_TRACKING_DAYS = 30;
    private const TRACKING_VERIFIED_MINUTES = 30;

    protected $letterService;
    protected $wizardService;
    protected $telegramService;
    protected $publicAssetUrlService;
    protected $letterPdfService;

    public function __construct(LetterService $letterService, LetterPdfService $letterPdfService, WizardService $wizardService, \App\Services\TelegramService $telegramService, PublicAssetUrlService $publicAssetUrlService)
    {
        $this->letterService = $letterService;
        $this->letterPdfService = $letterPdfService;
        $this->wizardService = $wizardService;
        $this->telegramService = $telegramService;
        $this->publicAssetUrlService = $publicAssetUrlService;
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

        try {
            $settings = Settings::whereIn('key', $publicKeys)
                ->pluck('value', 'key')
                ->toArray();

            return $this->publicAssetUrlService->normalizeSettings($settings);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Public settings lookup failed. Using empty defaults.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Serve files stored on the public disk when a storage symlink is unavailable.
     */
    public function publicMedia(string $path)
    {
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $filePath = $disk->path($path);

        if (!is_file($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Clear temporary wizard edit session data.
     */
    private function clearEditWizardSession(): void
    {
        session()->forget([
            'wizard_data',
            'wizard_step',
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
     * Require a valid verified tracking session before exposing approved letters.
     */
    private function ensureApprovedLetterAccess(\App\Models\Request $requestModel): void
    {
        if (!$this->hasValidTrackingVerification($requestModel)) {
            abort(403, 'Please verify your request with OTP before viewing the letter.');
        }

        if ($requestModel->status !== 'Approved') {
            abort(403, 'This request has not been approved yet.');
        }
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
     * Normalize tracking IDs so users can paste lowercase or extra whitespace safely.
     */
    private function normalizeTrackingId(?string $trackingId): string
    {
        return strtoupper(trim((string) $trackingId));
    }

    /**
     * Sign the trusted-browser cookie against the student's verification identity.
     */
    private function buildTrackingTrustProof(RequestModel $requestModel, int $rememberUntil): string
    {
        $appKey = (string) config('app.key', '');
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded !== false) {
                $appKey = $decoded;
            }
        }

        return hash_hmac('sha256', implode('|', [
            trim((string) $requestModel->verification_token),
            strtolower(trim((string) $requestModel->student_email)),
            (string) $rememberUntil,
        ]), $appKey);
    }

    /**
     * Read and validate the trusted-browser cookie for the current student identity.
     */
    private function trustedTrackingBrowserUntil(RequestModel $requestModel, Request $request): ?Carbon
    {
        $rawPayload = $request->cookie(self::TRUSTED_TRACKING_COOKIE);
        if (!is_string($rawPayload) || trim($rawPayload) === '') {
            return null;
        }

        $payload = json_decode($rawPayload, true);
        if (!is_array($payload)) {
            return null;
        }

        $rememberUntil = (int) ($payload['remember_until'] ?? 0);
        $proof = (string) ($payload['proof'] ?? '');

        if ($rememberUntil < now()->timestamp || $proof === '') {
            return null;
        }

        $expectedProof = $this->buildTrackingTrustProof($requestModel, $rememberUntil);
        if (!hash_equals($expectedProof, $proof)) {
            return null;
        }

        return Carbon::createFromTimestamp($rememberUntil);
    }

    /**
     * Queue a trusted-browser cookie after a successful OTP verification.
     */
    private function rememberTrackingBrowser(RequestModel $requestModel): Carbon
    {
        $rememberUntil = now()->addDays(self::TRUSTED_TRACKING_DAYS);

        Cookie::queue(cookie(
            self::TRUSTED_TRACKING_COOKIE,
            json_encode([
                'remember_until' => $rememberUntil->timestamp,
                'proof' => $this->buildTrackingTrustProof($requestModel, $rememberUntil->timestamp),
            ], JSON_UNESCAPED_SLASHES),
            self::TRUSTED_TRACKING_DAYS * 24 * 60,
            '/',
            null,
            null,
            true,
            false,
            'lax'
        ));

        return $rememberUntil;
    }

    /**
     * Stop trusting the current browser for student tracking.
     */
    private function forgetTrustedTrackingBrowser(): void
    {
        Cookie::queue(Cookie::forget(self::TRUSTED_TRACKING_COOKIE, '/'));
    }

    /**
     * Create a verified tracking session used by the tracker and approved-letter pages.
     */
    private function issueVerifiedTrackingSession(RequestModel $requestModel): void
    {
        session([
            'tracking_verified_request_id' => $requestModel->id,
            'tracking_verified_tracking_id' => $requestModel->tracking_id,
            'tracking_verified_until' => now()->addMinutes(self::TRACKING_VERIFIED_MINUTES)->timestamp,
        ]);
    }

    /**
     * Clear public tracking verification session data.
     */
    private function clearTrackingVerificationSession(bool $keepRequestContext = false): void
    {
        $keys = ['2fa_otp', '2fa_expires'];

        if (!$keepRequestContext) {
            $keys = array_merge($keys, [
                '2fa_request_id',
                '2fa_tracking_id',
                '2fa_delivery_method',
                '2fa_delivery_hint',
            ]);
        }

        session()->forget($keys);
    }

    /**
     * Send a fresh public tracking OTP and store the verification session.
     */
    private function issueTrackingVerificationCode(RequestModel $requestModel): void
    {
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        Mail::to($requestModel->student_email)->send(new TrackingVerificationCode($requestModel, $otp));

        session([
            '2fa_otp' => $otp,
            '2fa_expires' => $expiresAt,
            '2fa_request_id' => $requestModel->id,
            '2fa_tracking_id' => $requestModel->tracking_id,
            '2fa_delivery_method' => 'email',
            '2fa_delivery_hint' => $this->maskEmail((string) $requestModel->student_email),
        ]);

        if (!$requestModel->telegram_chat_id) {
            return;
        }

        try {
            $this->telegramService->sendMessageToChat(
                $requestModel->telegram_chat_id,
                "🔐 <b>Verification Code</b>\n\nYour code to access request details is: <code>{$otp}</code>\n\nDo not share this code with anyone."
            );
        } catch (\Exception $e) {
            \Log::warning('Tracking OTP Telegram send failed: ' . $e->getMessage(), [
                'tracking_id' => $requestModel->tracking_id,
                'request_id' => $requestModel->id,
            ]);
        }
    }

    /**
     * Build wizard form data from a stored request.
     */
    private function buildWizardDataFromRequest(\App\Models\Request $requestModel): array
    {
        $storedFormData = is_array($requestModel->form_data) ? $requestModel->form_data : [];
        $knownPurposes = [
            "Master's Application",
            'PhD Application',
            'Job Application',
            'Internship',
            'Scholarship',
            'Residency',
            'Other',
        ];
        $purpose = $requestModel->purpose;

        if (!empty($purpose) && !in_array($purpose, $knownPurposes, true)) {
            $storedFormData['purpose_other'] = $storedFormData['purpose_other'] ?? $purpose;
            $purpose = 'Other';
        }

        return array_merge($storedFormData, [
            'student_name' => $requestModel->student_name,
            'middle_name' => $requestModel->middle_name,
            'last_name' => $requestModel->last_name,
            'student_email' => $requestModel->student_email,
            'phone' => $requestModel->phone,
            'university' => $requestModel->university,
            'verification_token' => $requestModel->verification_token,
            'training_period' => $requestModel->training_period,
            'purpose' => $purpose,
            'deadline' => ($requestModel->deadline instanceof \DateTimeInterface) ? $requestModel->deadline->format('Y-m-d') : null,
            'content_option' => $requestModel->content_option ?? (!empty($requestModel->custom_content) ? 'custom' : 'template'),
            'custom_content' => $requestModel->custom_content,
            'template_id' => $requestModel->template_id,
            'admin_message' => $requestModel->admin_message,
        ]);
    }

    /**
     * Normalize request-purpose data for wizard navigation and persistence.
     */
    private function normalizeWizardPurpose(array $formData): array
    {
        $purpose = trim((string) ($formData['purpose'] ?? ''));
        $purposeOther = trim((string) ($formData['purpose_other'] ?? ''));

        $formData['purpose'] = $purpose === '' ? null : $purpose;
        $formData['purpose_other'] = $purpose === 'Other' && $purposeOther !== '' ? $purposeOther : null;

        return $formData;
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
        $step = (int) $request->query('step', session('wizard_step', 1));
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
        if (empty($formData) && !$request->has('step')) {
            $step = 1;
        }
        if ($step > 1 && empty($formData)) {
            $step = 1;
        }

        session(['wizard_step' => $step]);

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
        $formData = $this->normalizeWizardPurpose($formData);

        // Handle back action
        if ($action === 'back') {
            $step = max(1, $currentStep - 1);
            $sessionPayload = [
                'wizard_data' => $formData,
                'wizard_step' => $step,
            ];
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
            $this->wizardService->validateStep3($formData, $formConfig);
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
                $requiredIdentityErrors['verification_token'] = 'Student / National ID is required.';
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
                            'university' => $formData['university'] ?? $requestModel->university ?? '',
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
                    foreach ($admins as $admin) {
                        try {
                            Mail::to($admin->email)
                                ->send(new RequestSubmittedToAdmin($updatedRequest));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Revision resubmission email failed for admin ' . $admin->email . ': ' . $e->getMessage());
                        }
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
                        'university' => $formData['university'] ?? '',
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

            $studentEmailSent = false;

            // Send email notifications
            try {
                // Send confirmation to student
                Mail::to($newRequest->student_email)->send(new RequestSubmittedToStudent($newRequest));
                $studentEmailSent = true;

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
            session()->forget(['wizard_data', 'wizard_step']);

            return redirect()->route('public.request')->with([
                'success' => true,
                'tracking_id' => $trackingId,
                'telegram_bot_username' => $this->telegramService->getBotUsername(),
                'confirmation_email_hint' => $this->maskEmail((string) $newRequest->student_email),
                'confirmation_email_sent' => $studentEmailSent,
            ]);
        }

        // Proceed to next step
        $step = min(3, $currentStep + 1);
        $sessionPayload = [
            'wizard_data' => $formData,
            'wizard_step' => $step,
        ];
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
            'id' => $id ? $this->normalizeTrackingId($id) : null,  // Pre-fill tracking ID if provided
        ]);
    }

    /**
     * Handle tracking form submission
     */
    public function doTracking(Request $request)
    {
        $trackingId = $this->normalizeTrackingId($request->input('trackingId'));
        $verificationToken = trim((string) $request->input('verificationToken'));

        $request->merge([
            'trackingId' => $trackingId,
            'verificationToken' => $verificationToken,
        ]);

        $request->validate([
            'trackingId' => ['required', 'string', 'regex:' . self::TRACKING_ID_PATTERN],
            'verificationToken' => 'required|string|max:100',
        ], [
            'trackingId.required' => 'Tracking ID is required.',
            'trackingId.regex' => 'Tracking ID must be in the format REC-2026-AB12CD34.',
            'verificationToken.required' => 'Student / National ID is required.',
        ]);

        $this->clearEditWizardSession();
        session()->forget([
            'tracking_verified_request_id',
            'tracking_verified_tracking_id',
            'tracking_verified_until',
        ]);
        $this->clearTrackingVerificationSession();

        $result = \App\Models\Request::where('tracking_id', $trackingId)
            ->where('verification_token', $verificationToken)
            ->first();

        if (!$result) {
            return back()
                ->withInput([
                    'trackingId' => $trackingId,
                    'verificationToken' => $verificationToken,
                ])
                ->with('error', 'We could not find a request matching this Tracking ID and Student / National ID. Please check both values and try again.');
        }

        if ($result->status === 'Archived') {
            return back()
                ->withInput([
                    'trackingId' => $trackingId,
                    'verificationToken' => $verificationToken,
                ])
                ->with('error', 'This request is archived and is no longer available in the student tracker. Please contact administration if you still need help.');
        }

        $trustedDeviceUntil = $this->trustedTrackingBrowserUntil($result, $request);
        if ($trustedDeviceUntil instanceof Carbon) {
            $this->issueVerifiedTrackingSession($result);

            return view('public.tracking', [
                'settings' => $this->getPublicSettings(),
                'request' => $result,
                'id' => $result->tracking_id,
                'trustedDeviceActive' => true,
                'trustedDeviceUntil' => $trustedDeviceUntil,
            ]);
        }

        try {
            $this->issueTrackingVerificationCode($result);
        } catch (\Exception $e) {
            \Log::error('Tracking OTP email failed: ' . $e->getMessage(), [
                'tracking_id' => $result->tracking_id,
                'request_id' => $result->id,
            ]);
            return back()
                ->withInput([
                    'trackingId' => $trackingId,
                    'verificationToken' => $verificationToken,
                ])
                ->with('error', 'We found your request, but we could not send the 6-digit verification code to your email address. Please try again or contact support.');
        }

        // Redirect to Verify Page
        return redirect()->route('public.tracking.verify')
            ->with('success', 'Request found. We sent a 6-digit verification code to ' . $this->maskEmail((string) $result->student_email) . '.');
    }

    /**
     * Show 2FA Verification Form
     */
    public function show2FAVerify()
    {
        if (!session('2fa_request_id')) {
            return redirect()->route('public.tracking');
        }

        $settings = $this->getPublicSettings();
        $deliveryMethod = session('2fa_delivery_method', 'email');
        $deliveryHint = session('2fa_delivery_hint');
        $trackingId = session('2fa_tracking_id');
        $expiresAt = session('2fa_expires');

        try {
            $expiresAt = $expiresAt ? \Illuminate\Support\Carbon::parse($expiresAt) : null;
        } catch (\Throwable $e) {
            $expiresAt = null;
        }

        $hasActiveCode = session()->has('2fa_otp')
            && $expiresAt instanceof \Illuminate\Support\Carbon
            && now()->lessThan($expiresAt);

        return view('public.2fa_verify', compact('settings', 'deliveryMethod', 'deliveryHint', 'trackingId', 'expiresAt', 'hasActiveCode'));
    }

    /**
     * Handle 2FA Submission
     */
    public function handle2FAVerify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
            'remember_browser' => 'nullable|boolean',
        ]);

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
            if ($requestId) {
                $this->clearTrackingVerificationSession(true);
                return back()->with('error', 'This verification code has expired. Request a new code below.');
            }

            $this->clearTrackingVerificationSession();
            return redirect()->route('public.tracking')
                ->with('error', 'Your verification session expired. Please track your request again.');
        }

        if ($request->otp != $sessionOtp) {
            return back()->with('error', 'Invalid verification code. Please check the 6-digit code and try again.');
        }

        // OTP Valid - Clear session 2FA data but keep request context? 
        // Or just load the view directly.

        $result = \App\Models\Request::find($requestId);
        if (!$result) {
            $this->clearTrackingVerificationSession();
            return redirect()->route('public.tracking')
                ->with('error', 'Request not found. Please track again.');
        }

        $this->issueVerifiedTrackingSession($result);

        $trustedDeviceActive = false;
        $trustedDeviceUntil = null;
        if ($request->boolean('remember_browser')) {
            $trustedDeviceActive = true;
            $trustedDeviceUntil = $this->rememberTrackingBrowser($result);
        }

        // Clear OTP session to prevent replay (optional, or keep for refresh?)
        // Better to clear
        $this->clearTrackingVerificationSession();

        return view('public.tracking', [
            'settings' => $this->getPublicSettings(),
            'request' => $result,
            'id' => $result->tracking_id,
            'trustedDeviceActive' => $trustedDeviceActive,
            'trustedDeviceUntil' => $trustedDeviceUntil,
        ]);
    }

    /**
     * Resend the public tracking verification code inside an active tracking session.
     */
    public function resendTrackingVerification(Request $request)
    {
        $requestId = session('2fa_request_id');
        $trackingId = session('2fa_tracking_id');

        if (!$requestId || !$trackingId) {
            $this->clearTrackingVerificationSession();

            return redirect()->route('public.tracking')
                ->with('error', 'Your verification session expired. Please track your request again.');
        }

        $result = RequestModel::find($requestId);

        if (!$result || $result->tracking_id !== $trackingId) {
            $this->clearTrackingVerificationSession();

            return redirect()->route('public.tracking')
                ->with('error', 'We could not restore your verification session. Please track your request again.');
        }

        if ($result->status === 'Archived') {
            $this->clearTrackingVerificationSession();

            return redirect()->route('public.tracking')
                ->with('error', 'This request is archived and is no longer available in the student tracker. Please contact administration if you still need help.');
        }

        try {
            $this->issueTrackingVerificationCode($result);
        } catch (\Exception $e) {
            \Log::error('Tracking OTP resend email failed: ' . $e->getMessage(), [
                'tracking_id' => $result->tracking_id,
                'request_id' => $result->id,
            ]);

            return back()->with('error', 'We could not send a new verification code right now. Please try again in a moment or contact support.');
        }

        return redirect()->route('public.tracking.verify')
            ->with('success', 'We sent a new 6-digit verification code to ' . $this->maskEmail((string) $result->student_email) . '.');
    }

    /**
     * Forget a previously trusted tracking browser.
     */
    public function forgetTrackingTrustedBrowser(Request $request)
    {
        $trackingId = $this->normalizeTrackingId($request->input('tracking_id'));

        $this->forgetTrustedTrackingBrowser();
        $this->clearTrackingVerificationSession();

        return redirect()->route('public.tracking', ['id' => $trackingId ?: null])
            ->with('success', 'This browser will require a verification code the next time you track your request.');
    }
    /**
     * View approved letter public page
     */
    /**
     * View approved letter public page
     */
    public function viewLetter($tracking_id, Request $httpRequest)
    {
        $request = \App\Models\Request::where('tracking_id', $tracking_id)->firstOrFail();
        $this->ensureApprovedLetterAccess($request);

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
        $request = \App\Models\Request::where('tracking_id', $tracking_id)->firstOrFail();
        $this->ensureApprovedLetterAccess($request);

        try {
            $compiled = $this->letterPdfService->compile($request);

            if (($compiled['fit']['status'] ?? null) === 'too_long') {
                return response('This recommendation letter is being adjusted to fit one official A4 page. Please contact administration.', 409);
            }

            $filename = 'Recommendation_Letter_' . $request->tracking_id . '.pdf';
            $disposition = $httpRequest->boolean('download') ? 'attachment' : 'inline';

            return response($compiled['pdf_binary'], 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            ]);
        } catch (\RuntimeException $e) {
            abort(404, 'Template not found');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PDF Generation Failed: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
            ]);
            abort(500, 'Failed to generate PDF. Please try again later.');
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
