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
        // Common Styles
        $styleBody = "font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: 100%;";
        $styleContainer = "max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); margin-top: 40px; margin-bottom: 40px;";
        $styleHeader = "background-color: #4F46E5; padding: 30px; text-align: center;";
        $styleHeaderTitle = "color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -0.5px;";
        $styleContent = "padding: 40px 30px; color: #374151; font-size: 16px; line-height: 1.6;";
        $styleButtonContainer = "text-align: center; margin-top: 30px; margin-bottom: 30px;";
        $styleButton = "background-color: #4F46E5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; font-size: 16px; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);";
        $styleFooter = "background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb;";
        $styleLabel = "display: block; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; margin-bottom: 4px; letter-spacing: 0.05em;";
        $styleValue = "font-size: 18px; color: #111827; font-weight: 500; margin-bottom: 20px;";
        $styleCode = "font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #4F46E5; background: #EEF2FF; padding: 20px; border-radius: 8px; display: inline-block; margin: 20px 0;";

        // 1. Two Factor Code
        $body2FA = "
<div style=\"$styleBody\">
    <div style=\"$styleContainer\">
        <div style=\"$styleHeader\">
            <h1 style=\"$styleHeaderTitle\">AAMD Recommendations</h1>
        </div>
        <div style=\"$styleContent\">
            <p style=\"margin-top: 0;\">Hello,</p>
            <p>You requested a secure login verification code. Please use the following code to complete your login.</p>
            <div style=\"text-align: center;\">
                <div style=\"$styleCode\">{code}</div>
            </div>
            <p style=\"font-size: 14px; color: #6b7280;\">This code will expire in 10 minutes. If you did not request this code, please ignore this email or contact support if you have concerns.</p>
        </div>
        <div style=\"$styleFooter\">
            &copy; " . date('Y') . " AAMD Recommendations. All rights reserved.
        </div>
    </div>
</div>";

        // 2. Request Submitted (Student)
        $bodyStudent = "
<div style=\"$styleBody\">
    <div style=\"$styleContainer\">
        <div style=\"$styleHeader\">
            <h1 style=\"$styleHeaderTitle\">Request Received</h1>
        </div>
        <div style=\"$styleContent\">
            <p style=\"margin-top: 0;\">Hi <strong>{student_name}</strong>,</p>
            <p>We have successfully received your recommendation letter request. Our team will review it shortly.</p>
            
            <div style=\"background-color: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0;\">
                <span style=\"$styleLabel\">Request ID</span>
                <div style=\"$styleValue\">#{request_id}</div>
                
                <span style=\"$styleLabel\">Submitted On</span>
                <div style=\"$styleValue\">" . date('M d, Y') . "</div>
            </div>

            <div style=\"$styleButtonContainer\">
                <a href=\"{tracking_link}\" style=\"$styleButton\">Track Your Request</a>
            </div>
            
            <p style=\"font-size: 14px; color: #6b7280;\">You can use your Request ID or the link above to check the status of your comprehensive recommendation letter at any time.</p>
        </div>
        <div style=\"$styleFooter\">
            &copy; " . date('Y') . " AAMD Recommendations. All rights reserved.
        </div>
    </div>
</div>";

        // 3. Request Status Update
        $bodyUpdate = "
<div style=\"$styleBody\">
    <div style=\"$styleContainer\">
        <div style=\"$styleHeader\">
            <h1 style=\"$styleHeaderTitle\">Status Update</h1>
        </div>
        <div style=\"$styleContent\">
            <p style=\"margin-top: 0;\">Hi <strong>{student_name}</strong>,</p>
            <p>There has been an update to your recommendation letter request.</p>
            
            <div style=\"background-color: #ECFDF5; border: 1px solid #D1FAE5; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;\">
                <span style=\"color: #065F46; font-weight: 600; font-size: 14px; text-transform: uppercase;\">New Status</span>
                <div style=\"color: #059669; font-size: 24px; font-weight: 800; margin-top: 5px;\">{status}</div>
            </div>

            <p>Click the button below to view the full details or download your document if available.</p>

            <div style=\"$styleButtonContainer\">
                <a href=\"{tracking_link}\" style=\"$styleButton\">View Details</a>
            </div>
        </div>
        <div style=\"$styleFooter\">
            &copy; " . date('Y') . " AAMD Recommendations. All rights reserved.
        </div>
    </div>
</div>";

        // 4. Request Submitted (Admin)
        $bodyAdmin = "
<div style=\"$styleBody\">
    <div style=\"$styleContainer\">
        <div style=\"$styleHeader\">
            <h1 style=\"$styleHeaderTitle\">New Request</h1>
        </div>
        <div style=\"$styleContent\">
            <p style=\"margin-top: 0;\">Hello Admin,</p>
            <p>A new recommendation letter request has been submitted and requires your attention.</p>
            
            <div style=\"background-color: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0;\">
                <span style=\"$styleLabel\">Applicant Name</span>
                <div style=\"$styleValue\">{student_name}</div>
                
                <span style=\"$styleLabel\">University / Destination</span>
                <div style=\"$styleValue\">{university}</div>

                <span style=\"$styleLabel\">Request ID</span>
                <div style=\"$styleValue\">#{request_id}</div>
            </div>

            <div style=\"$styleButtonContainer\">
                <a href=\"{admin_link}\" style=\"$styleButton\">Process Request</a>
            </div>
        </div>
        <div style=\"$styleFooter\">
            &copy; " . date('Y') . " AAMD Recommendations. All rights reserved.
        </div>
    </div>
</div>";

        // Definition Array
        $templates = [
            [
                'name' => 'two_factor_code',
                'subject' => 'Your Verification Code',
                'body' => $body2FA,
                'variables' => json_encode(['code']),
            ],
            [
                'name' => 'request_submitted_student',
                'subject' => 'We received your recommendation request',
                'body' => $bodyStudent,
                'variables' => json_encode(['student_name', 'request_id', 'tracking_link']),
            ],
            [
                'name' => 'request_status_update',
                'subject' => 'Update on your request: {status}',
                'body' => $bodyUpdate,
                'variables' => json_encode(['student_name', 'status', 'tracking_link']),
            ],
            [
                'name' => 'request_submitted_admin',
                'subject' => 'New Recommendation Request: {student_name}',
                'body' => $bodyAdmin,
                'variables' => json_encode(['student_name', 'university', 'request_id', 'admin_link']),
            ],
        ];

        foreach ($templates as $t) {
            DB::table('email_templates')->updateOrInsert(
                ['name' => $t['name']],
                [
                    'subject' => $t['subject'],
                    'body' => $t['body'],
                    'variables' => $t['variables'],
                    'updated_at' => now(),
                    // 'created_at' is handled by insert if new, but updateOrInsert doesn't set created_at on update. 
                    // That's fine for this purpose.
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ideally we would revert content, but keeping it is safer.
    }
};
