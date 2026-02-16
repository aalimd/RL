<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\RequestSubmittedToStudent;
use App\Mail\RequestSubmittedToAdmin;

class RequestController extends Controller
{
    protected $telegramService;

    public function __construct(\App\Services\TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    // GET /api/requests
    public function index(Request $request)
    {
        $query = RequestModel::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search; // Laravel uses parameter binding - no manual escaping needed
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_email', 'like', "%{$search}%")
                    ->orWhere('tracking_id', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->has('status') && $request->status && $request->status !== 'All') {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->input('limit', 10);
        $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Required Response Format for matching Frontend
        return response()->json([
            'requests' => $this->mapBackendToFrontend($requests->items()),
            'total' => $requests->total(),
            'page' => $requests->currentPage(),
            'totalPages' => $requests->lastPage(),
        ]);
    }

    // POST /api/requests
    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'studentName' => 'required|string|max:255',
                'middleName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'studentEmail' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'university' => 'required|string|max:255',
                'purpose' => 'required|string|max:100',
                'gpa' => 'nullable|numeric|min:0|max:4',
                'deadline' => 'nullable|date',
                'trainingPeriod' => 'nullable|string|max:100',
                'templateId' => 'nullable|integer|exists:templates,id',
                'customContent' => 'nullable|string',
                'contentOption' => 'nullable|in:template,custom',
                'formData' => 'nullable|array',
                'verificationToken' => 'nullable|string|max:100',
                'verifyToken' => 'nullable|string|max:64',
                // File validation
                'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);


            $trackingId = 'REC-' . date('Y') . '-' . strtoupper(Str::random(8));

            $formData = $validated['formData'] ?? [];
            if (!array_key_exists('middle_name', $formData) && !empty($validated['middleName'])) {
                $formData['middle_name'] = $validated['middleName'];
            }
            if (!array_key_exists('last_name', $formData) && !empty($validated['lastName'])) {
                $formData['last_name'] = $validated['lastName'];
            }
            if (!array_key_exists('phone', $formData) && !empty($validated['phone'])) {
                $formData['phone'] = $validated['phone'];
            }
            if (!array_key_exists('training_period', $formData) && !empty($validated['trainingPeriod'])) {
                $formData['training_period'] = $validated['trainingPeriod'];
            }
            if (!array_key_exists('template_id', $formData) && !empty($validated['templateId'])) {
                $formData['template_id'] = $validated['templateId'];
            }
            if (!array_key_exists('custom_content', $formData) && !empty($validated['customContent'])) {
                $formData['custom_content'] = $validated['customContent'];
            }
            if (!array_key_exists('content_option', $formData) && !empty($validated['contentOption'])) {
                $formData['content_option'] = $validated['contentOption'];
            }

            $data = [
                'tracking_id' => $trackingId,
                'student_name' => $validated['studentName'],
                'middle_name' => $validated['middleName'] ?? null,
                'last_name' => $validated['lastName'] ?? null,
                'student_email' => $validated['studentEmail'],
                'phone' => $validated['phone'] ?? null,
                'university' => $validated['university'],
                'purpose' => $validated['purpose'],
                'gpa' => $validated['gpa'] ?? null,
                'deadline' => $validated['deadline'] ?? null,
                'training_period' => $validated['trainingPeriod'] ?? null,
                'template_id' => $validated['templateId'] ?? null,
                'custom_content' => $validated['customContent'] ?? null,
                'content_option' => $validated['contentOption'] ?? null,
                'form_data' => !empty($formData) ? $formData : null,
                'verify_token' => $validated['verifyToken'] ?? Str::random(32), // For QR code verification
                'verification_token' => $validated['verificationToken'] ?? Str::random(60), // For Student Tracking
                'status' => 'Submitted'
            ];

            // Handle File Upload
            if ($request->hasFile('document')) {
                if (!$request->file('document')->isValid()) {
                    return response()->json(['error' => 'Invalid file upload.'], 400);
                }

                // Strict MIME Type Check
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($request->file('document')->getPathname());
                $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                if (!in_array($mimeType, $allowedMimes)) {
                    return response()->json(['error' => 'Invalid file type (MIME mismatch).'], 400);
                }

                // Store locally (private)
                $path = $request->file('document')->store('uploads');
                $data['document_path'] = $path;
            }

            $newRequest = RequestModel::create($data);

            // Audit Log
            AuditLog::create([
                'action' => 'CREATE_REQUEST',
                'details' => "New request created: {$newRequest->tracking_id} (ID: {$newRequest->id})",
                'ip_address' => $request->ip(),
            ]);

            // Send Email (Notification)
            try {
                // Send confirmation to student
                Mail::to($newRequest->student_email)->send(new RequestSubmittedToStudent($newRequest));

                // Send notification to admin(s) only
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new RequestSubmittedToAdmin($newRequest));
                }

                // Send Telegram Notification
                $this->telegramService->sendRequestNotification($newRequest);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Illuminate\Support\Facades\Log::error('API Request Email notification failed: ' . $e->getMessage());
            }

            return response()->json($this->mapBackendToFrontend([$newRequest])[0], 201);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Request Creation Failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // GET /api/requests/{id}
    public function show($id)
    {
        $req = RequestModel::findOrFail($id);
        return response()->json($this->mapBackendToFrontend([$req])[0]);
    }

    // PUT /api/requests/{id}
    public function update(Request $request, $id)
    {
        $req = RequestModel::findOrFail($id);

        $validated = $request->validate([
            'studentName' => 'sometimes|string|max:255',
            'middleName' => 'sometimes|nullable|string|max:255',
            'lastName' => 'sometimes|nullable|string|max:255',
            'studentEmail' => 'sometimes|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'university' => 'sometimes|string|max:255',
            'purpose' => 'sometimes|string|max:100',
            'gpa' => 'sometimes|nullable|numeric|min:0|max:4',
            'deadline' => 'sometimes|nullable|date',
            'trainingPeriod' => 'sometimes|nullable|string|max:100',
            'templateId' => 'sometimes|nullable|integer|exists:templates,id',
            'customContent' => 'sometimes|nullable|string',
            'contentOption' => 'sometimes|nullable|in:template,custom',
            'verificationToken' => 'sometimes|nullable|string|max:100',
            'verifyToken' => 'sometimes|nullable|string|max:64',
            'formData' => 'sometimes|nullable|array',
        ]);

        $data = [];
        if (array_key_exists('studentName', $validated))
            $data['student_name'] = $validated['studentName'];
        if (array_key_exists('middleName', $validated))
            $data['middle_name'] = $validated['middleName'];
        if (array_key_exists('lastName', $validated))
            $data['last_name'] = $validated['lastName'];
        if (array_key_exists('studentEmail', $validated))
            $data['student_email'] = $validated['studentEmail'];
        if (array_key_exists('phone', $validated))
            $data['phone'] = $validated['phone'];
        if (array_key_exists('university', $validated))
            $data['university'] = $validated['university'];
        if (array_key_exists('purpose', $validated))
            $data['purpose'] = $validated['purpose'];
        if (array_key_exists('gpa', $validated))
            $data['gpa'] = $validated['gpa'];
        if (array_key_exists('deadline', $validated))
            $data['deadline'] = $validated['deadline'];
        if (array_key_exists('trainingPeriod', $validated))
            $data['training_period'] = $validated['trainingPeriod'];
        if (array_key_exists('templateId', $validated))
            $data['template_id'] = $validated['templateId'];
        if (array_key_exists('customContent', $validated))
            $data['custom_content'] = $validated['customContent'];
        if (array_key_exists('contentOption', $validated))
            $data['content_option'] = $validated['contentOption'];
        if (array_key_exists('verificationToken', $validated))
            $data['verification_token'] = $validated['verificationToken'];
        if (array_key_exists('verifyToken', $validated))
            $data['verify_token'] = $validated['verifyToken'];

        $syncFormData = array_key_exists('formData', $validated)
            || array_key_exists('middleName', $validated)
            || array_key_exists('lastName', $validated)
            || array_key_exists('phone', $validated)
            || array_key_exists('trainingPeriod', $validated)
            || array_key_exists('templateId', $validated)
            || array_key_exists('customContent', $validated)
            || array_key_exists('contentOption', $validated);

        if ($syncFormData) {
            $formData = array_key_exists('formData', $validated) ? ($validated['formData'] ?? []) : ($req->form_data ?? []);
            if (!is_array($formData)) {
                $formData = [];
            }

            if (array_key_exists('middleName', $validated)) {
                $formData['middle_name'] = $validated['middleName'];
            }
            if (array_key_exists('lastName', $validated)) {
                $formData['last_name'] = $validated['lastName'];
            }
            if (array_key_exists('phone', $validated)) {
                $formData['phone'] = $validated['phone'];
            }
            if (array_key_exists('trainingPeriod', $validated)) {
                $formData['training_period'] = $validated['trainingPeriod'];
            }
            if (array_key_exists('templateId', $validated)) {
                $formData['template_id'] = $validated['templateId'];
            }
            if (array_key_exists('customContent', $validated)) {
                $formData['custom_content'] = $validated['customContent'];
            }
            if (array_key_exists('contentOption', $validated)) {
                $formData['content_option'] = $validated['contentOption'];
            }

            $data['form_data'] = $formData;
        }

        $req->update($data);
        return response()->json($this->mapBackendToFrontend([$req])[0]);
    }

    // PUT /api/requests/{id}/status
    public function updateStatus(Request $request, $id)
    {
        // Security: Only admin and editor can update status
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'editor'])) {
            return response()->json(['error' => 'Unauthorized. Admin or Editor role required.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Submitted,Under Review,Approved,Rejected,Archived,Needs Revision',
            'rejectionReason' => 'required_if:status,Rejected|nullable|string|max:2000',
            'adminMessage' => 'required_if:status,Needs Revision|nullable|string|max:2000',
        ]);

        $req = RequestModel::findOrFail($id);
        $req->status = $validated['status'];
        if ($validated['status'] === 'Rejected') {
            $req->rejection_reason = $validated['rejectionReason'] ?? null;
        } else {
            $req->rejection_reason = null;
        }
        if (array_key_exists('adminMessage', $validated)) {
            $req->admin_message = $validated['adminMessage'];
        }
        $req->save();

        AuditLog::create([
            'action' => 'UPDATE_STATUS',
            'details' => "Status changed to {$validated['status']} by {$user->name} (Request ID: {$req->id})",
            'user_id' => $user->id,
            'ip_address' => $request->ip()
        ]);

        return response()->json($this->mapBackendToFrontend([$req])[0]);
    }

    // PUT /api/requests/{id}/archive
    public function archive(Request $request, $id)
    {
        // Security: Only admin can archive
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin role required.'], 403);
        }

        $req = RequestModel::findOrFail($id);
        $req->status = 'Archived';
        $req->save();

        AuditLog::create([
            'action' => 'ARCHIVE_REQUEST',
            'details' => "Request archived by {$user->name} (Request ID: {$req->id})",
            'user_id' => $user->id,
            'ip_address' => $request->ip()
        ]);

        return response()->json(['message' => 'Archived']);
    }

    // Mapping Helper
    private function mapBackendToFrontend($items)
    {
        return array_map(function ($item) {
            // If item is array, cast to object if needed, or just array access
            // Eloquent models are objects.
            return [
                'id' => $item['id'],
                'trackingId' => $item['tracking_id'],
                'studentName' => $item['student_name'],
                'middleName' => $item['middle_name'],
                'lastName' => $item['last_name'],
                'studentEmail' => $item['student_email'],
                'phone' => $item['phone'],
                'university' => $item['university'],
                'gpa' => $item['gpa'],
                'purpose' => $item['purpose'],
                'status' => $item['status'],
                'deadline' => $item['deadline'],
                'trainingPeriod' => $item['training_period'],
                'templateId' => $item['template_id'],
                'contentOption' => $item['content_option'],
                'customContent' => $item['custom_content'],
                'verifyToken' => $item['verify_token'],
                'verificationToken' => $item['verification_token'],
                'adminMessage' => $item['admin_message'],
                'formData' => $item['form_data'],
                'documentPath' => $item['document_path'],
                'rejectionReason' => $item['rejection_reason'],
                'createdAt' => $item['created_at'],
                'updatedAt' => $item['updated_at'],
                // Add more as needed
            ];
        }, $items);
    }
}
