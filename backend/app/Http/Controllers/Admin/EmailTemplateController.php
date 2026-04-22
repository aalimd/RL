<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmailBrandingService;
use App\Services\LetterService;
use Illuminate\Http\Request;
use App\Models\Settings;

class EmailTemplateController extends Controller
{
    public function __construct(
        private LetterService $letterService,
        private EmailBrandingService $emailBrandingService
    )
    {
    }

    private function getSettings()
    {
        return Settings::all()->pluck('value', 'key')->toArray();
    }

    public function index()
    {
        $settings = $this->getSettings();
        $templates = \App\Models\EmailTemplate::all();
        return view('admin.email-templates.index', compact('templates', 'settings'));
    }

    public function edit($id)
    {
        $settings = $this->getSettings();
        $template = \App\Models\EmailTemplate::findOrFail($id);

        return view('admin.email-templates.edit', [
            'template' => $template,
            'settings' => $settings,
            'preview' => $this->buildPreviewPayload($template),
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = \App\Models\EmailTemplate::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $cleanBody = $this->normalizeTemplateBody(
            $this->letterService->sanitizeHtml($request->body)
        );

        $template->update([
            'subject' => $request->subject,
            'body' => $cleanBody,
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    private function buildPreviewPayload(\App\Models\EmailTemplate $template): array
    {
        $branding = $this->emailBrandingService->getBranding();
        $samples = $this->sampleVariables($template->name);
        $frame = $this->templateFrame($template->name, $samples);

        return [
            'branding' => $branding,
            'samples' => $samples,
            'guide' => $this->templateGuide($template->name),
            'frame' => $frame,
            'subject' => $this->emailBrandingService->replaceVariables($template->subject, $samples) ?? $template->subject,
            'body' => $this->emailBrandingService->replaceVariables($template->body, $samples) ?? $template->body,
        ];
    }

    private function templateFrame(string $templateName, array $samples): array
    {
        return match ($templateName) {
            'request_submitted_student' => [
                'badge' => 'Request received',
                'title' => 'Your request is in the queue',
                'summary' => 'We received your request and saved the tracking details you will need for future updates.',
                'cta_label' => 'Open tracking page',
                'cta_url' => $samples['tracking_link'] ?? '#',
                'closing_note' => 'Keep your tracking ID available whenever you need to review the latest status.',
            ],
            'request_submitted_admin' => [
                'badge' => 'Admin notification',
                'title' => 'A new request needs review',
                'summary' => 'A student submission is ready for review inside the admin panel.',
                'cta_label' => 'Review request',
                'cta_url' => $samples['admin_link'] ?? '#',
                'closing_note' => 'Open the request to review the submission, manage status, and continue the workflow.',
            ],
            'request_status_updated', 'request_status_update' => [
                'badge' => 'Status update',
                'title' => 'Your request status has changed',
                'summary' => 'Review the latest outcome or next step for your recommendation request.',
                'cta_label' => 'Review status',
                'cta_url' => $samples['tracking_link'] ?? '#',
                'closing_note' => 'Your tracking page will always show the current status and any related notes.',
            ],
            'tracking_verification' => [
                'badge' => 'Security code',
                'title' => 'Verify access to your request',
                'summary' => 'Enter this short-lived code on the tracking page to continue securely.',
                'cta_label' => null,
                'cta_url' => null,
                'closing_note' => 'For security, do not share this code with anyone.',
            ],
            'two_factor_code' => [
                'badge' => 'Account security',
                'title' => 'Your verification code',
                'summary' => 'Use this temporary code to continue your secure sign-in flow.',
                'cta_label' => null,
                'cta_url' => null,
                'closing_note' => 'If this request was not made by you, review your account security settings.',
            ],
            default => [
                'badge' => 'Notification',
                'title' => 'Application update',
                'summary' => 'Preview how this email will look when sent to a recipient.',
                'cta_label' => null,
                'cta_url' => null,
                'closing_note' => null,
            ],
        };
    }

    private function sampleVariables(string $templateName): array
    {
        return match ($templateName) {
            'request_submitted_student' => [
                'student_name' => 'Sara Ahmed',
                'tracking_id' => 'RQ-48291',
                'request_id' => '48291',
                'submitted_at' => now()->format('M d, Y h:i A'),
                'tracking_link' => url('/track/RQ-48291'),
                'purpose' => 'Graduate admission',
                'university' => 'King Saud University',
            ],
            'request_submitted_admin' => [
                'student_name' => 'Sara',
                'student_full_name' => 'Sara Ahmed',
                'student_email' => 'sara.ahmed@example.com',
                'tracking_id' => 'RQ-48291',
                'request_id' => '48291',
                'submitted_at' => now()->format('M d, Y h:i A'),
                'purpose' => 'Graduate admission',
                'university' => 'King Saud University',
                'admin_link' => url('/admin/requests/48291'),
            ],
            'request_status_updated', 'request_status_update' => [
                'student_name' => 'Sara Ahmed',
                'status' => 'Needs Revision',
                'student_message' => 'Please upload the corrected transcript and confirm your target program.',
                'rejection_reason' => 'The request could not be completed because required documents were missing.',
                'tracking_id' => 'RQ-48291',
                'request_id' => '48291',
                'tracking_link' => url('/track/RQ-48291'),
            ],
            'tracking_verification' => [
                'student_name' => 'Sara Ahmed',
                'tracking_id' => 'RQ-48291',
                'otp' => '582941',
                'expires_in_minutes' => '5',
            ],
            'two_factor_code' => [
                'user_name' => 'Sara Ahmed',
                'action_label' => 'complete sign-in',
                'code' => '582941',
                'expires_in_minutes' => '10',
            ],
            default => [
                'student_name' => 'Sara Ahmed',
                'tracking_id' => 'RQ-48291',
            ],
        };
    }

    private function templateGuide(string $templateName): array
    {
        return match ($templateName) {
            'request_submitted_student' => [
                'recipient' => 'Student',
                'goal' => 'Confirm that the request was received and explain how to track it.',
            ],
            'request_submitted_admin' => [
                'recipient' => 'Admin reviewer',
                'goal' => 'Alert the team that a new request is ready for review.',
            ],
            'request_status_updated', 'request_status_update' => [
                'recipient' => 'Student',
                'goal' => 'Explain the latest request status and direct the student to the tracking page.',
            ],
            'tracking_verification' => [
                'recipient' => 'Student',
                'goal' => 'Deliver a short-lived security code for tracking-page access.',
            ],
            'two_factor_code' => [
                'recipient' => 'Admin or staff user',
                'goal' => 'Deliver a short-lived security code for account sign-in or setup.',
            ],
            default => [
                'recipient' => 'User',
                'goal' => 'Send a clear transactional update related to the application.',
            ],
        };
    }

    private function normalizeTemplateBody(string $body): string
    {
        $body = trim($body);

        if (preg_match('/<body\b[^>]*>(.*)<\/body>/is', $body, $matches)) {
            $body = trim($matches[1]);
        }

        $body = preg_replace('/\A\s*<!DOCTYPE.+?>/is', '', $body) ?? $body;
        $body = preg_replace('/<\/?(html|head|body)\b[^>]*>/i', '', $body) ?? $body;

        return trim($body);
    }
}
