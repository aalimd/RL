<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'request_submitted_student',
                'subject' => 'Request received - {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>We received your recommendation request and added it to the review queue.</p><p><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}<br><strong>Submitted:</strong> {submitted_at}</p><p>You can check the latest status at any time from your tracking page: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => ['student_name', 'tracking_id', 'request_id', 'submitted_at', 'tracking_link', 'purpose', 'university', 'site_name', 'support_email']
            ],
            [
                'name' => 'request_submitted_admin',
                'subject' => 'New request received - {student_name} ({tracking_id})',
                'body' => '<p>A new recommendation request has been submitted and is ready for review.</p><p><strong>Student:</strong> {student_full_name}<br><strong>Email:</strong> {student_email}<br><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}<br><strong>Submitted:</strong> {submitted_at}</p><p><a href="{admin_link}">Open the request in the admin panel</a></p>',
                'variables' => ['student_name', 'student_full_name', 'student_email', 'tracking_id', 'request_id', 'submitted_at', 'purpose', 'university', 'admin_link', 'site_name']
            ],
            [
                'name' => 'request_status_updated',
                'subject' => 'Request status updated: {status} - {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>Your recommendation request now has the following status: <strong>{status}</strong>.</p><p><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}</p><p>{student_message}</p><p>Review the latest details here: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => ['student_name', 'status', 'student_message', 'rejection_reason', 'tracking_id', 'request_id', 'tracking_link', 'site_name', 'support_email']
            ],
            [
                'name' => 'request_status_update',
                'subject' => 'Request status updated: {status} - {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>Your recommendation request now has the following status: <strong>{status}</strong>.</p><p><strong>Tracking ID:</strong> {tracking_id}<br><strong>Request ID:</strong> #{request_id}</p><p>{student_message}</p><p>Review the latest details here: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => ['student_name', 'status', 'student_message', 'rejection_reason', 'tracking_id', 'request_id', 'tracking_link', 'site_name', 'support_email']
            ],
            [
                'name' => 'tracking_verification',
                'subject' => 'Your access code for request {tracking_id}',
                'body' => '<p>Hello {student_name},</p><p>Use the code below to securely access request <strong>{tracking_id}</strong>.</p><p><strong>Code:</strong> {otp}</p><p>This code expires in {expires_in_minutes} minutes. If you did not request it, you can ignore this email.</p>',
                'variables' => ['student_name', 'tracking_id', 'otp', 'expires_in_minutes', 'site_name', 'support_email']
            ],
            [
                'name' => 'two_factor_code',
                'subject' => 'Your verification code for {site_name}',
                'body' => '<p>Hello {user_name},</p><p>Use the verification code below to {action_label}.</p><p><strong>Code:</strong> {code}</p><p>This code expires in {expires_in_minutes} minutes. If you did not request it, you can ignore this email.</p>',
                'variables' => ['user_name', 'action_label', 'code', 'expires_in_minutes', 'site_name', 'support_email']
            ]
        ];

        foreach ($templates as $tmpl) {
            \App\Models\EmailTemplate::updateOrCreate(
                ['name' => $tmpl['name']],
                $tmpl
            );
        }
    }
}
