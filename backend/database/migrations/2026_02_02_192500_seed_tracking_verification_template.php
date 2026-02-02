<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Common Styles (Matching existing professional templates)
        $styleBody = "font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: 100%;";
        $styleContainer = "max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); margin-top: 40px; margin-bottom: 40px;";
        $styleHeader = "background-color: #4F46E5; padding: 30px; text-align: center;";
        $styleHeaderTitle = "color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;";
        $styleContent = "padding: 40px 30px; color: #374151; font-size: 16px; line-height: 1.6;";
        $styleFooter = "background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb;";
        $styleCode = "font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #4F46E5; background: #EEF2FF; padding: 20px; border-radius: 8px; display: inline-block; margin: 20px 0;";

        $body = "
<div style=\"$styleBody\">
    <div style=\"$styleContainer\">
        <div style=\"$styleHeader\">
            <h1 style=\"$styleHeaderTitle\">Security Verification</h1>
        </div>
        <div style=\"$styleContent\">
            <p style=\"margin-top: 0;\">Hello <strong>{student_name}</strong>,</p>
            <p>To access the details of your recommendation request <strong>#{tracking_id}</strong>, please use the verification code below:</p>
            <div style=\"text-align: center;\">
                <div style=\"$styleCode\">{otp}</div>
            </div>
            <p style=\"font-size: 14px; color: #6b7280;\">This code will expire in 10 minutes. If you did not request this code, please ignore this email or contact support if you have concerns.</p>
        </div>
        <div style=\"$styleFooter\">
            &copy; " . date('Y') . " AAMD Recommendations. All rights reserved.
        </div>
    </div>
</div>";

        DB::table('email_templates')->updateOrInsert(
            ['name' => 'tracking_verification'],
            [
                'subject' => 'Verification Code - {tracking_id}',
                'body' => $body,
                'variables' => json_encode(['student_name', 'tracking_id', 'otp']),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DB::table('email_templates')->where('name', 'tracking_verification')->delete();
    }
};
