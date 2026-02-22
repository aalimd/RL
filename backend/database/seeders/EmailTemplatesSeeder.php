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
                'subject' => 'We received your recommendation request',
                'body' => '<p>Dear {student_name},</p><p>We have received your request for a recommendation letter.</p><p>Tracking ID: <strong>{tracking_id}</strong></p><p>We will review it and notify you shortly.</p><p>Regards,<br>{university}</p>',
                'variables' => ['student_name', 'tracking_id', 'university']
            ],
            [
                'name' => 'request_submitted_admin',
                'subject' => 'New Recommendation Request: {student_name}',
                'body' => '<p>New request received from <strong>{student_name}</strong>.</p><p>Purpose: {purpose}</p><p><a href="{admin_link}">View Request</a></p>',
                'variables' => ['student_name', 'purpose', 'admin_link']
            ],
            [
                'name' => 'request_status_updated',
                'subject' => 'Update on your request: {status}',
                'body' => '<p>Dear {student_name},</p><p>Your request status has been updated to: <strong>{status}</strong>.</p><div style="background:#f3f4f6;padding:10px;margin:10px 0;">{admin_message}</div><p>You can track progress here: <a href="{tracking_link}">{tracking_link}</a></p>',
                'variables' => ['student_name', 'status', 'admin_message', 'tracking_link']
            ],
            [
                'name' => 'tracking_verification',
                'subject' => 'Verification Code - {tracking_id}',
                'body' => '<p>Hello <strong>{student_name}</strong>,</p><p>To access the details of your request <strong>#{tracking_id}</strong>, please use the verification code below:</p><div style="background: #ffffff; border: 2px dashed #007bff; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #007bff; margin: 20px 0; border-radius: 5px;">{otp}</div><p>This code will expire in 5 minutes. If you did not request this code, please ignore this email.</p>',
                'variables' => ['student_name', 'tracking_id', 'otp']
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
