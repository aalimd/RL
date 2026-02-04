<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
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
                'studentName' => 'required|string',
                'studentEmail' => 'required|email',
                'university' => 'required|string',
                'purpose' => 'required|string',
                // File validation
                'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);


            $trackingId = 'REC-' . date('Y') . '-' . strtoupper(Str::random(8));

            $data = [
                'tracking_id' => $trackingId,
                'student_name' => $request->studentName,
                'student_email' => $request->studentEmail,
                'university' => $request->university,
                'purpose' => $request->purpose,
                'gpa' => $request->gpa ?? null,
                'deadline' => $request->deadline ?? null,
                'verify_token' => Str::random(32), // For QR code verification
                'verification_token' => Str::random(60), // For Student Tracking
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
            'studentEmail' => 'sometimes|email|max:255',
            'university' => 'sometimes|string|max:255',
            'purpose' => 'sometimes|string|max:100',
            'gpa' => 'sometimes|nullable|numeric|min:0|max:4',
            'deadline' => 'sometimes|nullable|date',
        ]);

        $data = [];
        if (isset($validated['studentName']))
            $data['student_name'] = $validated['studentName'];
        if (isset($validated['studentEmail']))
            $data['student_email'] = $validated['studentEmail'];
        if (isset($validated['university']))
            $data['university'] = $validated['university'];
        if (isset($validated['purpose']))
            $data['purpose'] = $validated['purpose'];
        if (isset($validated['gpa']))
            $data['gpa'] = $validated['gpa'];
        if (isset($validated['deadline']))
            $data['deadline'] = $validated['deadline'];

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

        $req = RequestModel::findOrFail($id);
        $req->status = $request->status;
        if ($request->status === 'Rejected') {
            $req->rejection_reason = $request->rejectionReason;
        }
        $req->save();

        AuditLog::create([
            'action' => 'UPDATE_STATUS',
            'details' => "Status changed to {$request->status} by {$user->name} (Request ID: {$req->id})",
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
                'studentEmail' => $item['student_email'],
                'university' => $item['university'],
                'gpa' => $item['gpa'],
                'purpose' => $item['purpose'],
                'status' => $item['status'],
                'deadline' => $item['deadline'],
                'documentPath' => $item['document_path'],
                'rejectionReason' => $item['rejection_reason'],
                'createdAt' => $item['created_at'],
                'updatedAt' => $item['updated_at'],
                // Add more as needed
            ];
        }, $items);
    }
}
