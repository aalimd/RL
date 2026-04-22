<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $templates = [
            [
                'name' => 'two_factor_code',
                'subject' => 'Your verification code for {site_name}',
                'body' => '<p>Hello {user_name},</p><p>Use the verification code below to {action_label}.</p><p><strong>Code:</strong> {code}</p><p>This code expires in {expires_in_minutes} minutes. If you did not request it, you can ignore this email.</p>',
                'variables' => json_encode(['user_name', 'action_label', 'code', 'expires_in_minutes', 'site_name', 'support_email']),
            ],
            [
                'name' => 'request_submitted_student',
                'subject' => 'Request received - {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>We received your recommendation request and added it to the review queue.</p><p><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}<br><strong>Submitted:</strong> {submitted_at}</p><p>You can check the latest status at any time from your tracking page: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => json_encode(['student_name', 'tracking_id', 'request_id', 'submitted_at', 'tracking_link', 'purpose', 'university', 'site_name', 'support_email']),
            ],
            [
                'name' => 'request_submitted_admin',
                'subject' => 'New request received - {student_name} ({tracking_id})',
                'body' => '<p>A new recommendation request has been submitted and is ready for review.</p><p><strong>Student:</strong> {student_full_name}<br><strong>Email:</strong> {student_email}<br><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}<br><strong>Submitted:</strong> {submitted_at}</p><p><a href="{admin_link}">Open the request in the admin panel</a></p>',
                'variables' => json_encode(['student_name', 'student_full_name', 'student_email', 'tracking_id', 'request_id', 'submitted_at', 'purpose', 'university', 'admin_link', 'site_name']),
            ],
            [
                'name' => 'request_status_updated',
                'subject' => 'Request status updated: {status} - {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>Your recommendation request now has the following status: <strong>{status}</strong>.</p><p><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}</p><p>{student_message}</p><p>Review the latest details here: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => json_encode(['student_name', 'status', 'student_message', 'rejection_reason', 'tracking_id', 'request_id', 'tracking_link', 'site_name', 'support_email']),
            ],
            [
                'name' => 'request_status_update',
                'subject' => 'Request status updated: {status} - {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>Your recommendation request now has the following status: <strong>{status}</strong>.</p><p><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}</p><p>{student_message}</p><p>Review the latest details here: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => json_encode(['student_name', 'status', 'student_message', 'rejection_reason', 'tracking_id', 'request_id', 'tracking_link', 'site_name', 'support_email']),
            ],
            [
                'name' => 'tracking_verification',
                'subject' => 'Your access code for request {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>Use the code below to securely access request <strong>{tracking_id}</strong>.</p><p><strong>Code:</strong> {otp}</p><p>This code expires in {expires_in_minutes} minutes. If you did not request it, you can ignore this email.</p>',
                'variables' => json_encode(['student_name', 'tracking_id', 'otp', 'expires_in_minutes', 'site_name', 'support_email']),
            ],
        ];

        foreach ($templates as $template) {
            $existing = DB::table('email_templates')->where('name', $template['name'])->exists();

            $payload = [
                'subject' => $template['subject'],
                'body' => $template['body'],
                'variables' => $template['variables'],
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('email_templates')
                    ->where('name', $template['name'])
                    ->update($payload);
            } else {
                DB::table('email_templates')->insert(array_merge(
                    ['name' => $template['name']],
                    $payload,
                    ['created_at' => now()]
                ));
            }
        }
    }

    public function down(): void
    {
        // Keeping the latest transactional template content is safer than rolling back email copy.
    }
};
