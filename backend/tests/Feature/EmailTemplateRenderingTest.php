<?php

namespace Tests\Feature;

use App\Mail\RequestStatusUpdated;
use App\Mail\TestEmailMail;
use App\Mail\TrackingVerificationCode;
use App\Mail\TwoFactorCodeMail;
use App\Models\Request as RequestRecord;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_mail_uses_real_branding_and_professional_copy(): void
    {
        $this->seedEmailBranding();

        $mailable = new TwoFactorCodeMail('582941', 'Sara Ahmed', 'complete sign-in');
        $html = $mailable->render();
        $text = view($mailable->content()->text, $mailable->content()->with)->render();

        $this->assertSame('Your verification code for RL Portal', $mailable->envelope()->subject);
        $this->assertStringContainsString('RL Portal', $html);
        $this->assertStringContainsString('complete sign-in', $html);
        $this->assertStringContainsString('582941', $html);
        $this->assertStringContainsString('support@example.test', $html);
        $this->assertStringContainsString('complete sign-in', $text);
        $this->assertStringNotContainsString('Dr. Alzahrani EM', $html);
        $this->assertStringNotContainsString('🔐', $html);
    }

    public function test_status_update_mail_renders_rejection_reason_in_html_and_text(): void
    {
        $this->seedEmailBranding();

        $request = RequestRecord::create([
            'tracking_id' => 'RQ-48291',
            'student_name' => 'Sara',
            'last_name' => 'Ahmed',
            'student_email' => 'sara@example.test',
            'verification_token' => 'ID-48291',
            'university' => 'King Saud University',
            'purpose' => 'Graduate admission',
            'training_period' => '2026-04',
            'status' => 'Rejected',
            'rejection_reason' => 'The request is missing the final transcript.',
        ]);

        $mailable = new RequestStatusUpdated($request);
        $html = $mailable->render();
        $text = view($mailable->content()->text, $mailable->content()->with)->render();

        $this->assertSame('Request status updated: Rejected - RQ-48291', $mailable->envelope()->subject);
        $this->assertStringContainsString('The request is missing the final transcript.', $html);
        $this->assertStringContainsString('The request is missing the final transcript.', $text);
        $this->assertStringContainsString('RL Portal', $html);
        $this->assertStringNotContainsString('Please do not reply directly', $html);
    }

    public function test_tracking_verification_mail_is_clear_and_non_spammy(): void
    {
        $this->seedEmailBranding();

        $request = RequestRecord::create([
            'tracking_id' => 'RQ-58291',
            'student_name' => 'Sara',
            'last_name' => 'Ahmed',
            'student_email' => 'sara@example.test',
            'verification_token' => 'ID-58291',
            'university' => 'King Saud University',
            'purpose' => 'Graduate admission',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ]);

        $mailable = new TrackingVerificationCode($request, '582941');
        $html = $mailable->render();

        $this->assertSame('Your access code for request RQ-58291', $mailable->envelope()->subject);
        $this->assertStringContainsString('582941', $html);
        $this->assertStringContainsString('Verify access to your request', $html);
        $this->assertStringNotContainsString('Recommendation Letter System', $html);
        $this->assertStringNotContainsString('🔐', $html);
    }

    public function test_admin_test_email_endpoint_sends_test_email_mailable(): void
    {
        Mail::fake();
        $this->seedEmailBranding();

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->post(route('admin.settings.test-email'), [
                'email' => 'deliverability@example.test',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Mail::assertSent(TestEmailMail::class);
    }

    private function seedEmailBranding(): void
    {
        Settings::updateOrCreate(['key' => 'siteName'], ['value' => 'RL Portal']);
        Settings::updateOrCreate(['key' => 'mailFromName'], ['value' => 'RL Portal']);
        Settings::updateOrCreate(['key' => 'mailFromAddress'], ['value' => 'support@example.test']);
    }
}
