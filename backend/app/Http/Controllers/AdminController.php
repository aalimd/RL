<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Template;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Mail\RequestStatusUpdated;
use App\Services\LetterService;
use App\Services\AiService;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected $letterService;
    protected $aiService;

    public function __construct(LetterService $letterService, AiService $aiService)
    {
        $this->letterService = $letterService;
        $this->aiService = $aiService;
    }

    // ... existing ...

    public function rewriteWithAi(Request $request, $id)
    {
        $req = RequestModel::findOrFail($id);

        // Combine custom content with any new notes from the request
        $notes = $request->input('notes', $req->custom_content);

        $result = $this->aiService->generateLetterContent($req, $notes);

        return response()->json($result);
    }
    /**
     * Sanitize HTML content
     */
    // Sanitize HTML content removed (Moved to LetterService)

    /**
     * Get all settings for admin views
     */
    private function getSettings(): array
    {
        return Settings::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $settings = $this->getSettings();

        // Stats
        $stats = [
            'totalRequests' => RequestModel::count(),
            'pendingRequests' => RequestModel::where('status', 'Submitted')->count(),
            'underReviewRequests' => RequestModel::where('status', 'Under Review')->count(),
            'approvedRequests' => RequestModel::where('status', 'Approved')->count(),
            'rejectedRequests' => RequestModel::where('status', 'Rejected')->count(),
        ];

        // Recent requests
        $recentRequests = RequestModel::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent Activities
        $recentActivities = AuditLog::with('user')->latest()->take(8)->get();

        // Chart Data (Last 30 Days Trend)
        // Using get() and collection grouping to be DB-driver agnostic (safer for dev/prod diffs)
        $dailyRequests = RequestModel::select('created_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->groupBy(function ($date) {
                return $date->created_at->format('Y-m-d');
            });

        $chartLabels = [];
        $chartValues = [];

        // Fill last 30 days including empty ones
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartValues[] = isset($dailyRequests[$date]) ? $dailyRequests[$date]->count() : 0;
        }

        return view('admin.dashboard', compact('settings', 'stats', 'recentRequests', 'recentActivities', 'chartLabels', 'chartValues'));
    }

    /**
     * Requests list
     */
    public function requests(Request $request)
    {
        $settings = $this->getSettings();

        $query = RequestModel::orderBy('created_at', 'desc');

        // Filter by status
        $status = $request->input('status', 'All');
        if ($status && $status !== 'All') {
            $query->where('status', $status);
        }

        // Search functionality
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_email', 'like', "%{$search}%")
                    ->orWhere('tracking_id', 'like', "%{$search}%")
                    ->orWhere('university', 'like', "%{$search}%");
            });
        }

        $university = $request->input('university');
        if ($university) {
            $query->where('university', 'like', "%{$university}%");
        }
        $dateFrom = $request->input('date_from');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        $dateTo = $request->input('date_to');
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $requests = $query->paginate(15)->appends($request->query());

        return view('admin.requests', compact('settings', 'requests'));
    }

    /**
     * Request details
     */
    public function requestDetails($id)
    {
        $settings = $this->getSettings();
        $request = RequestModel::findOrFail($id);

        return view('admin.request-details', compact('settings', 'request'));
    }

    /**
     * Update request status
     */
    public function updateRequestStatus(Request $request, $id)
    {
        $requestModel = RequestModel::findOrFail($id);

        $updateData = [
            'status' => $request->input('status'),
            'admin_message' => $request->input('admin_message'),
        ];

        // Generate verification token if approved and not exists
        if ($request->input('status') === 'Approved' && !$requestModel->verify_token) {
            $updateData['verify_token'] = Str::random(32);
        }

        $requestModel->update($updateData);

        // Send email notification to student about status update
        try {
            Mail::to($requestModel->student_email)->send(new RequestStatusUpdated($requestModel));
        } catch (\Exception $e) {
            Log::error('Status update email failed: ' . $e->getMessage());
        }

        // Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_request_status',
            'details' => "Changed status of Request #{$requestModel->tracking_id} to {$request->input('status')}"
        ]);

        return back()->with('success', 'Status updated successfully!');
    }

    /**
     * Update request data (admin editing)
     */
    public function updateRequest(Request $request, $id)
    {
        $requestModel = RequestModel::findOrFail($id);

        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'student_email' => 'required|email|max:255',
            'university' => 'nullable|string|max:255',
            'verification_token' => 'nullable|string|max:100',
            'purpose' => 'nullable|string|max:100',
            'deadline' => 'nullable|date',
            'training_period' => 'nullable|string',
            'custom_content' => 'nullable|string',
            'gender' => 'required|in:male,female',
        ]);

        // Extract gender before updating model (it goes in form_data, not directly)
        $gender = $validated['gender'];
        unset($validated['gender']);

        $requestModel->update($validated);

        // Update gender in form_data
        $formData = $requestModel->form_data ?? [];
        $formData['gender'] = $gender;
        $requestModel->form_data = $formData;
        $requestModel->save();

        // Log the change
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Request',
            'details' => json_encode([
                'request_id' => $id,
                'tracking_id' => $requestModel->tracking_id,
                'changes' => $requestModel->getChanges()
            ])
        ]);

        return redirect()->route('admin.requests.show', $id)->with('success', 'Request data updated successfully!');
    }

    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        $settings = $this->getSettings();

        $analytics = [
            'total' => RequestModel::count(),
            'byStatus' => RequestModel::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'byMonth' => RequestModel::selectRaw('MONTH(created_at) as month, count(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray(),
        ];

        return view('admin.analytics', compact('settings', 'analytics'));
    }

    /**
     * Users list
     */
    public function users()
    {
        $settings = $this->getSettings();
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users', compact('settings', 'users'));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can manage users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username|max:50',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()],
            'role' => 'required|in:admin,editor,viewer',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can manage users.');
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|string|unique:users,username,' . $id . '|max:50',
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()],
            'role' => 'required|in:admin,editor,viewer',
            'is_active' => 'boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->username = $validated['username'];
        $user->role = $validated['role'];
        $user->is_active = $request->has('is_active');

        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can delete users.');
        }

        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    /**
     * Templates management
     */
    public function templates()
    {
        $settings = $this->getSettings();
        $templates = Template::orderBy('name')->get();

        return view('admin.templates', compact('settings', 'templates'));
    }

    /**
     * Create template form
     */
    public function createTemplate()
    {
        $settings = $this->getSettings();
        return view('admin.template-editor', compact('settings'));
    }

    /**
     * Store new template
     */
    public function storeTemplate(Request $request)
    {
        if (Auth::user()->role === 'viewer') {
            abort(403, 'Viewers cannot create templates.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header_content' => 'nullable|string',
            'body_content' => 'nullable|string',
            'footer_content' => 'nullable|string',
            'signature_name' => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
            'signature_department' => 'nullable|string|max:255',
            'signature_institution' => 'nullable|string|max:255',
            'signature_email' => 'nullable|email|max:255',
            'signature_phone' => 'nullable|string|max:100',
            'signature_image' => [
                'nullable',
                'string',
                'max:10000',
                function ($attr, $val, $fail) {
                    if ($val && !preg_match('/^(https?:\/\/|data:image\/)/i', $val)) {
                        $fail('The signature image must be a valid URL or data URL.');
                    }
                }
            ],
            'stamp_image' => [
                'nullable',
                'string',
                'max:10000',
                function ($attr, $val, $fail) {
                    if ($val && !preg_match('/^(https?:\/\/|data:image\/)/i', $val)) {
                        $fail('The stamp image must be a valid URL or data URL.');
                    }
                }
            ],
            'language' => 'required|in:en,ar',
            'layout_settings' => 'nullable|array',
        ]);

        // Sanitize HTML Content
        // Sanitize HTML Content
        $validated['header_content'] = $this->letterService->sanitizeHtml($validated['header_content'] ?? '');
        $validated['body_content'] = $this->letterService->sanitizeHtml($validated['body_content'] ?? '');
        $validated['footer_content'] = $this->letterService->sanitizeHtml($validated['footer_content'] ?? '');

        $validated['is_active'] = $request->has('is_active');
        $validated['content'] = $validated['body_content'];
        // layout_settings is already an array and will be auto-encoded by the model's cast

        $template = Template::create($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_template',
            'details' => json_encode(['template_id' => $template->id, 'name' => $template->name])
        ]);

        return redirect()->route('admin.templates')->with('success', 'Template created successfully!');
    }

    /**
     * Edit template form
     */
    public function editTemplate($id)
    {
        $settings = $this->getSettings();
        $template = Template::findOrFail($id);

        return view('admin.template-editor', compact('settings', 'template'));
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, $id)
    {
        if (Auth::user()->role === 'viewer') {
            abort(403, 'Viewers cannot edit templates.');
        }

        $template = Template::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header_content' => 'nullable|string',
            'body_content' => 'nullable|string',
            'footer_content' => 'nullable|string',
            'signature_name' => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
            'signature_department' => 'nullable|string|max:255',
            'signature_institution' => 'nullable|string|max:255',
            'signature_email' => 'nullable|email|max:255',
            'signature_phone' => 'nullable|string|max:100',
            'signature_image' => [
                'nullable',
                'string',
                'max:10000',
                function ($attr, $val, $fail) {
                    if ($val && !preg_match('/^(https?:\/\/|data:image\/)/i', $val)) {
                        $fail('The signature image must be a valid URL or data URL.');
                    }
                }
            ],
            'stamp_image' => [
                'nullable',
                'string',
                'max:10000',
                function ($attr, $val, $fail) {
                    if ($val && !preg_match('/^(https?:\/\/|data:image\/)/i', $val)) {
                        $fail('The stamp image must be a valid URL or data URL.');
                    }
                }
            ],
            'language' => 'required|in:en,ar',
            'layout_settings' => 'nullable|array',
        ]);

        // Sanitize HTML Content
        $validated['header_content'] = $this->letterService->sanitizeHtml($validated['header_content'] ?? '');
        $validated['body_content'] = $this->letterService->sanitizeHtml($validated['body_content'] ?? '');
        $validated['footer_content'] = $this->letterService->sanitizeHtml($validated['footer_content'] ?? '');

        $validated['is_active'] = $request->has('is_active');
        $validated['content'] = $validated['body_content'];
        // layout_settings is already an array and will be auto-encoded by the model's cast

        $template->update($validated);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_template',
            'details' => json_encode(['template_id' => $template->id, 'changes' => $template->getChanges()])
        ]);

        return redirect()->route('admin.templates')->with('success', 'Template updated successfully!');
    }

    /**
     * Auto-save template draft
     */
    public function autoSaveTemplate(Request $request, $id)
    {
        if (Auth::user()->role === 'viewer') {
            abort(403, 'Viewers cannot edit templates.');
        }

        $template = Template::findOrFail($id);

        // We validate less strictly for drafts, mostly just that data exists
        $data = $request->validate([
            'header_content' => 'nullable|string',
            'body_content' => 'nullable|string',
            'footer_content' => 'nullable|string',
            'layout_settings' => 'nullable|array',
        ]);

        // Sanitize
        $data['header_content'] = $this->letterService->sanitizeHtml($data['header_content'] ?? '');
        $data['body_content'] = $this->letterService->sanitizeHtml($data['body_content'] ?? '');
        $data['footer_content'] = $this->letterService->sanitizeHtml($data['footer_content'] ?? '');

        $draftData = [
            'header_content' => $data['header_content'],
            'body_content' => $data['body_content'],
            'footer_content' => $data['footer_content'],
            'layout_settings' => $data['layout_settings'] ?? null,
            'signature_name' => $request->input('signature_name'),
            'signature_title' => $request->input('signature_title'),
            // ... capture other fields as needed for draft
        ];

        $template->update([
            'draft_data' => json_encode($draftData),
            'last_draft_saved_at' => now(),
        ]);

        return response()->json(['success' => true, 'saved_at' => now()->toDateTimeString()]);
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        if (Auth::user()->role === 'viewer') {
            abort(403, 'Viewers cannot delete templates.');
        }

        $template = Template::findOrFail($id);
        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete_template',
            'details' => json_encode(['template_id' => $id, 'name' => $template->name])
        ]);

        $template->delete();

        return redirect()->route('admin.templates')->with('success', 'Template deleted successfully!');
    }

    /**
     * Update request status
     */


    /**
     * Perform bulk actions on requests (approve, reject, delete)
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|json',
            'action' => 'required|in:approve,reject,delete'
        ]);

        $ids = json_decode($validated['ids'], true);
        if (empty($ids)) {
            return back()->with('error', 'No items selected');
        }

        $count = count($ids);

        if ($validated['action'] === 'delete') {
            RequestModel::whereIn('id', $ids)->delete();
            $logAction = "Bulk deleted $count requests";
        } else {
            $status = $validated['action'] === 'approve' ? 'Approved' : 'Rejected';

            // For approval, we need to generate tokens, so we loop
            if ($status === 'Approved') {
                $requests = RequestModel::whereIn('id', $ids)->get();
                foreach ($requests as $r) {
                    /** @var RequestModel $r */
                    $r->status = 'Approved';
                    if (!$r->verify_token) {
                        $r->verify_token = Str::random(32);
                    }
                    $r->save();
                }
            } else {
                // For rejection, bulk update is fine
                RequestModel::whereIn('id', $ids)->update(['status' => $status]);
            }

            $logAction = "Bulk updated $count requests to $status";
        }

        // Audit Log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_request_action',
            'details' => $logAction
        ]);

        return back()->with('success', "$logAction successfully");
    }

    /**
     * General settings
     */
    public function settings()
    {
        $settings = $this->getSettings();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Appearance settings
     */
    public function appearance()
    {
        $settings = $this->getSettings();

        return view('admin.appearance', compact('settings'));
    }

    /**
     * Audit logs
     */
    public function auditLogs()
    {
        $settings = $this->getSettings();
        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.audit-logs', compact('settings', 'logs'));
    }

    /**
     * Update general settings
     */
    public function updateSettings(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can change settings.');
        }

        // Detect which form was submitted based on unique fields
        if ($request->has('siteName')) {
            // Processing General Settings
            $keys = ['siteName', 'welcomeTitle', 'welcomeText', 'logoUrl', 'maintenanceMode', 'maintenanceMessage', 'geminiApiKey'];

            foreach ($keys as $key) {
                $value = $request->input($key, '');

                // Handle checkbox
                if ($key === 'maintenanceMode') {
                    $value = $request->has('maintenanceMode') ? 'true' : 'false';
                }

                Settings::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        } elseif ($request->has('telegram_bot_token') || $request->has('telegram_chat_id')) {
            // Processing Telegram Settings
            $keys = ['telegram_bot_token', 'telegram_chat_id'];

            foreach ($keys as $key) {
                $value = $request->input($key, '');

                // Don't overwrite with empty if field is password type and empty
                if ($key === 'telegram_bot_token' && empty($value)) {
                    continue;
                }

                Settings::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        } elseif ($request->has('smtpHost')) {
            // Processing Email Settings
            $keys = ['smtpHost', 'smtpPort', 'smtpUsername', 'smtpPassword', 'smtpEncryption', 'mailFromAddress', 'mailFromName'];

            foreach ($keys as $key) {
                $value = $request->input($key, '');

                // For password fields, if empty, keep the existing value (don't overwrite with empty string)
                if (($key === 'smtpPassword' || $key === 'smtpEncryption') && empty($value)) {
                    continue;
                }

                // Handle Hybrid File Upload (e.g., logoUrl_file)
                if ($request->hasFile($key . '_file')) {
                    $path = $request->file($key . '_file')->store('uploads/settings', 'public');
                    $value = '/storage/' . $path;
                }

                Settings::updateOrCreate(['key' => $key], ['value' => $value]);
            }

            // Clear mail settings cache
            cache()->forget('mail_settings');
        }

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully!');
    }

    /**
     * Send test email
     */
    public function sendTestEmail(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $email = $request->input('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email address'], 422);
        }

        try {
            Mail::raw('This is a test email from your Academic Recommendation System.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email - Connection Successful');
            });

            return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update appearance settings
     */
    public function updateAppearance(Request $request)
    {
        // Security: Validate file uploads
        $fileFields = ['logoUrl_file', 'backgroundImage_file', 'loginBackgroundImage_file'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $request->validate([
                    $field => 'file|mimes:png,jpg,jpeg,gif,webp|max:2048' // 2MB max, safe image types only
                ], [
                    $field . '.mimes' => 'Only PNG, JPG, JPEG, GIF, and WEBP images are allowed.',
                    $field . '.max' => 'Image size must not exceed 2MB.'
                ]);
            }
        }

        $section = $request->input('section');
        $settingsToUpdate = [];

        switch ($section) {
            case 'branding':
                $settingsToUpdate = [
                    'primaryColor',
                    'secondaryColor',
                    'fontFamily',
                    'loginTitle',
                    'loginSubtitle',
                    'loginBackgroundImage',
                    'showBranding',
                ];
                break;

            case 'landing':
                $settingsToUpdate = [
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
                ];
                break;

            case 'tracking':
                $settingsToUpdate = [
                    'trackingFixedMessage',
                    'trackingPendingMessage',
                    'trackingReviewMessage',
                    'trackingApprovedMessage',
                    'trackingRejectedMessage',
                    'trackingRevisionMessage',
                ];
                break;

            default:
                // Fallback for safety or legacy calls: verify what's present
                // If section is missing, we shouldn't wipe everything.
                return redirect()->route('admin.appearance')->with('error', 'Invalid form submission.');
        }

        foreach ($settingsToUpdate as $key) {
            // For checkboxes like showBranding, unchecked means not present.
            // But we only want to update checkboxes if we are in their section.

            if ($key === 'showBranding') {
                $value = $request->has($key) ? 'true' : 'false';
            } else {
                $value = $request->input($key, '');
            }

            // Handle Hybrid File Upload
            if ($request->hasFile($key . '_file')) {
                $path = $request->file($key . '_file')->store('uploads/appearance', 'public');
                $value = '/storage/' . $path;
            }

            Settings::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.appearance')->with('success', 'Appearance updated successfully!');
    }
    /**
     * Preview Letter
     */
    public function previewLetter($id)
    {
        $request = RequestModel::findOrFail($id);

        $content = $this->letterService->generateLetterContent($request);

        if (empty($content)) {
            return response()->json(['message' => 'No active template found'], 404);
        }

        return response()->json([
            'success' => true,
            'layout' => $content['layout'],
            'header' => $content['header'],
            'body' => $content['body'],
            'footer' => $content['footer'],
            'signature' => $content['signature'],
            'qrCode' => $content['qrCode'] ?? '',
        ]);
    }
    /**
     * Download secure document
     */
    public function downloadDocument($id)
    {
        // Security: Only admin/editor can download documents
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'editor'])) {
            abort(403, 'You do not have permission to download documents.');
        }

        $request = RequestModel::findOrFail($id);

        if (!$request->document_path) {
            abort(404, 'No document attached.');
        }

        // Check if file exists in local storage
        if (!\Illuminate\Support\Facades\Storage::exists($request->document_path)) {
            abort(404, 'File not found on server.');
        }

        return \Illuminate\Support\Facades\Storage::download($request->document_path);
    }

    /**
     * Form Settings page
     */
    public function formSettings()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can access form settings.');
        }

        $settings = $this->getSettings();
        $templates = Template::where('is_active', true)->orderBy('name')->get();

        $formSettings = [
            'templateSelectionMode' => $settings['templateSelectionMode'] ?? 'student_choice',
            'defaultTemplateId' => $settings['defaultTemplateId'] ?? '',
            'allowCustomContent' => $settings['allowCustomContent'] ?? 'true',
            'formFieldConfig' => $settings['formFieldConfig'] ?? '{}',
        ];

        return view('admin.form-settings', compact('settings', 'templates', 'formSettings'));
    }

    /**
     * Update form settings
     */
    public function updateFormSettings(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can change form settings.');
        }

        // Save template selection mode
        Settings::updateOrCreate(
            ['key' => 'templateSelectionMode'],
            ['value' => $request->input('templateSelectionMode', 'student_choice')]
        );

        // Save default template ID
        Settings::updateOrCreate(
            ['key' => 'defaultTemplateId'],
            ['value' => $request->input('defaultTemplateId', '')]
        );

        // Save allow custom content
        Settings::updateOrCreate(
            ['key' => 'allowCustomContent'],
            ['value' => $request->has('allowCustomContent') ? 'true' : 'false']
        );

        // Build and save field configuration
        // Define all known fields to ensure unchecked fields are saved as false
        $knownFields = [
            'student_name',
            'middle_name',
            'last_name',
            'gender',
            'student_email',
            'university',
            'verification_token',
            'training_period',
            'phone',
            'major',
            'purpose',
            'deadline',
            'notes'
        ];

        $submittedFields = $request->input('fields', []);
        $fieldConfig = [];

        foreach ($knownFields as $fieldKey) {
            $fieldConfig[$fieldKey] = [
                'visible' => isset($submittedFields[$fieldKey]['visible']),
                'required' => isset($submittedFields[$fieldKey]['required']),
            ];
        }

        Settings::updateOrCreate(
            ['key' => 'formFieldConfig'],
            ['value' => json_encode($fieldConfig)]
        );

        return redirect()->route('admin.form-settings')->with('success', 'Form settings updated successfully!');
    }

    /**
     * Reset Template to Stable Default (Table-Based)
     */
    public function resetTemplate($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $template = Template::findOrFail($id);

        // ROBUST TABLE-BASED HEADER
        $headerContent = '
<table style="width: 100%; border-collapse: collapse; border: none; font-family: \'Times New Roman\', serif;">
    <tr>
        <!-- Left Column: English Info -->
        <td style="width: 35%; vertical-align: top; text-align: left; padding: 0; line-height: 1.4; font-size: 11px; color: #000;">
            <div style="margin-bottom: 5px;">
                <strong>Kingdom of Saudi Arabia</strong><br>
                National Guard<br>
                Health Affairs<br>
                King Abdulaziz Medical City - Jeddah<br>
                King Khalid National Guard Hospital
            </div>
            <div style="color: #c00000; font-weight: bold; font-size: 11px;">Department of Emergency Medicine</div>
        </td>

        <!-- Center Column: Logo -->
        <td style="width: 30%; vertical-align: top; text-align: center; padding: 0;">
            <img src="https://i.ibb.co/JW3Q0t7Y/mnghalogo.png" alt="NGHA Logo" style="max-width: 80px; height: auto;">
        </td>

        <!-- Right Column: Arabic Info -->
        <td style="width: 35%; vertical-align: top; text-align: right; direction: rtl; padding: 0; line-height: 1.4; font-size: 12px; color: #000; font-family: \'DejaVu Sans\', sans-serif;">
            <strong>المملكة العربية السعودية</strong><br>
            وزارة الحرس الوطني<br>
            الشؤون الصحية<br>
            مدينة الملك عبدالعزيز الطبية بجدة<br>
            مستشفى الملك خالد الحرس الوطني
        </td>
    </tr>
</table>

<!-- Contact Info Bar -->
<table style="width: 100%; margin-top: 5px; border-collapse: collapse;">
    <tr>
        <td style="font-size: 10px; color: #000;">
            Tel +966+2+2266666 Ext:62790-62791 | E-mail: emerg-education@ngha.med.sa
        </td>
        <td style="text-align: right; font-size: 10px; color: #000;">
            Date: {{date}}
        </td>
    </tr>
</table>

<!-- Title Box -->
<div style="text-align: center; margin-top: 15px;">
    <span style="background-color: #2e5cb8; color: white; padding: 6px 20px; font-weight: bold; font-size: 12px; letter-spacing: 1px;">
        RECOMMENDATION LETTER
    </span>
</div>';

        // STANDARD BODY
        $bodyContent = '
<div style="font-family: \'Times New Roman\', serif; font-size: 11pt; color: #000;">
    <h2 style="text-align: center; font-weight: bold; margin: 25px 0 35px 0;">Dr. {{studentName}}</h2>

    <p style="margin-bottom: 15px; text-align: justify; line-height: 1.6;">
        To Whom It May Concern,
    </p>

    <p style="margin-bottom: 15px; text-align: justify; line-height: 1.6;">
        This letter is to certify that <strong>Dr. {{studentName}}</strong> completed a rotation in the Emergency Department at King Abdulaziz Medical City, Jeddah (MNGHA) during <strong>{{rotationMonth}}</strong> as part of his medical internship.
    </p>

    <p style="margin-bottom: 15px; text-align: justify; line-height: 1.6;">
        Throughout his rotation, Dr. {{studentName}} demonstrated solid medical knowledge and a consistently professional attitude. He was diligent, dependable, and showed a clear commitment to learning and patient care. He interacted effectively with patients, residents, consultants, nursing staff, and other members of the healthcare team.
    </p>

    <p style="margin-bottom: 15px; text-align: justify; line-height: 1.6;">
        Dr. {{studentName}} displayed particular interest in Emergency Medicine, with good situational awareness, appropriate prioritization, and the ability to work efficiently in a fast-paced environment. He was receptive to feedback and showed continuous improvement during his time in the department.
    </p>

    <p style="margin-bottom: 20px; text-align: justify; line-height: 1.6;">
        Based on his performance, work ethic, and interpersonal skills, I believe Dr. {{studentName}} would be a valuable addition to any training program or institution he joins. I recommend him without reservation for the specialty he chooses to pursue.
    </p>
</div>';

        // ROBUST TABLE-BASED FOOTER
        $footerContent = '
<div style="border-top: 3px solid #28a745; margin-top: 10px; padding-top: 10px;">
    <table style="width: 100%; border-collapse: collapse; font-family: \'Times New Roman\', serif; font-size: 9px; color: #000;">
        <tr>
            <td style="width: 40%; vertical-align: top; text-align: left;">
                <strong>P.O. BOX 9515</strong><br>
                JEDDAH 21423<br>
                KINGDOM OF SAUDI ARABIA
            </td>
            <td style="width: 20%; vertical-align: top; text-align: center;">
                <strong>FAX: 624 7444</strong>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right; direction: rtl; font-family: \'DejaVu Sans\', sans-serif;">
                <strong>ص.ب 9515</strong><br>
                جدة 21423<br>
                المملكة العربية السعودية
            </td>
        </tr>
    </table>
</div>';

        // Update Template
        $template->update([
            'header_content' => $headerContent,
            'body_content' => $bodyContent,
            'footer_content' => $footerContent,
            'signature_name' => 'Abdulrhman Al Zaharani MD, SBEM',
            'signature_title' => 'Associate Consultant of Emergency Medicine',
            'signature_institution' => 'King Abdulaziz Medical City',
            'signature_department' => 'Emergency Department',
            'signature_email' => 'zahraniab13@mngha.med.sa',
            'signature_phone' => null,
            'signature_image' => null,
            'stamp_image' => null,
            'layout_settings' => [
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 20, 'right' => 20],
                'fontSize' => 12,
                'fontFamily' => 'Times New Roman',
                'border' => ['enabled' => true, 'width' => 2, 'style' => 'solid', 'color' => '#057f3a']
            ]
        ]);

        return redirect()->back()->with('success', 'Template reset to stable default successfully!');
    }
}
