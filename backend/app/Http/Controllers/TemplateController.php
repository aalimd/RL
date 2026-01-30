<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use App\Services\LetterService;

class TemplateController extends Controller
{
    protected $letterService;

    public function __construct(LetterService $letterService)
    {
        $this->letterService = $letterService;
    }

    // GET /api/templates
    public function index()
    {
        try {
            return response()->json(Template::orderBy('created_at', 'desc')->get());
        } catch (\Exception $e) {
            \Log::error('Template index failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch templates'], 500);
        }
    }

    // GET /api/templates/{id}
    public function show($id)
    {
        try {
            $template = Template::findOrFail($id);
            return response()->json($template);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Template not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Template show failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch template'], 500);
        }
    }

    // POST /api/templates
    public function store(Request $request)
    {
        // Security: Only admin and editor can create templates
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'editor'])) {
            return response()->json(['error' => 'Unauthorized. Admin or Editor role required.'], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'language' => 'required|in:en,ar',
                'headerContent' => 'nullable|string',
                'bodyContent' => 'nullable|string',
                'footerContent' => 'nullable|string',
                'signatureName' => 'nullable|string|max:255',
                'signatureTitle' => 'nullable|string|max:255',
                'signatureDepartment' => 'nullable|string|max:255',
                'signatureInstitution' => 'nullable|string|max:255',
                'signatureEmail' => 'nullable|email|max:255',
                'signaturePhone' => 'nullable|string|max:100',
                'signatureImage' => ['nullable', 'string', 'max:10000'],
                'stampImage' => ['nullable', 'string', 'max:10000'],
                'isActive' => 'nullable|boolean',
                'layoutSettings' => 'nullable|array',
            ]);

            $data = $this->mapFrontendToBackend($request->all());

            // Sanitize Content
            $data['header_content'] = $this->letterService->sanitizeHtml($data['header_content'] ?? '');
            $data['body_content'] = $this->letterService->sanitizeHtml($data['body_content'] ?? '');
            $data['footer_content'] = $this->letterService->sanitizeHtml($data['footer_content'] ?? '');

            $template = Template::create($data);

            // Audit Log
            try {
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'api_create_template',
                    'details' => "Created template: " . $template->name
                ]);
            } catch (\Exception $e) {
                \Log::error('Audit log failed during template creation: ' . $e->getMessage());
            }

            return response()->json($template, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Template creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create template'], 500);
        }
    }

    // PUT /api/templates/{id}
    public function update(Request $request, $id)
    {
        // Security: Only admin and editor can update templates
        $user = $request->user();
        if (!$user || !in_array($user->role, ['admin', 'editor'])) {
            return response()->json(['error' => 'Unauthorized. Admin or Editor role required.'], 403);
        }

        try {
            $template = Template::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'language' => 'required|in:en,ar',
                'headerContent' => 'nullable|string',
                'bodyContent' => 'nullable|string',
                'footerContent' => 'nullable|string',
                'signatureName' => 'nullable|string|max:255',
                'signatureTitle' => 'nullable|string|max:255',
                'signatureDepartment' => 'nullable|string|max:255',
                'signatureInstitution' => 'nullable|string|max:255',
                'signatureEmail' => 'nullable|email|max:255',
                'signaturePhone' => 'nullable|string|max:100',
                'signatureImage' => ['nullable', 'string', 'max:10000'],
                'stampImage' => ['nullable', 'string', 'max:10000'],
                'isActive' => 'nullable|boolean',
                'layoutSettings' => 'nullable|array',
            ]);

            $data = $this->mapFrontendToBackend($request->all());

            // Sanitize Content
            $data['header_content'] = $this->letterService->sanitizeHtml($data['header_content'] ?? '');
            $data['body_content'] = $this->letterService->sanitizeHtml($data['body_content'] ?? '');
            $data['footer_content'] = $this->letterService->sanitizeHtml($data['footer_content'] ?? '');

            $template->update($data);

            // Audit Log
            try {
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'api_update_template',
                    'details' => "Updated template: " . $template->name
                ]);
            } catch (\Exception $e) {
                \Log::error('Audit log failed during template update: ' . $e->getMessage());
            }

            return response()->json($template);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Template not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Template update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update template'], 500);
        }
    }

    // DELETE /api/templates/{id}
    public function destroy(Request $request, $id)
    {
        // Security: Only admin can delete templates
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin role required.'], 403);
        }

        try {
            $template = Template::findOrFail($id);

            // Audit Log
            try {
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'api_delete_template',
                    'details' => "Deleted template: " . $template->name
                ]);
            } catch (\Exception $e) {
                \Log::error('Audit log failed during template deletion: ' . $e->getMessage());
            }

            $template->delete();
            return response()->json(['message' => 'Template removed']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Template not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Template deletion failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete template'], 500);
        }
    }

    private function mapFrontendToBackend($input)
    {
        $map = [
            'name' => 'name',
            'content' => 'content',
            'headerContent' => 'header_content',
            'bodyContent' => 'body_content',
            'footerContent' => 'footer_content',
            'signatureName' => 'signature_name',
            'signatureTitle' => 'signature_title',
            'signatureImage' => 'signature_image',
            'stampImage' => 'stamp_image',
            'signatureInstitution' => 'signature_institution',
            'signatureDepartment' => 'signature_department',
            'signatureEmail' => 'signature_email',
            'signaturePhone' => 'signature_phone',
            'layoutSettings' => 'layout_settings',
            'language' => 'language',
            'isActive' => 'is_active',
        ];

        $output = [];
        foreach ($map as $front => $back) {
            if (isset($input[$front])) {
                $output[$back] = $input[$front];
            }
        }
        return $output;
    }
}
