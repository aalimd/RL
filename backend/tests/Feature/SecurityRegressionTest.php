<?php

namespace Tests\Feature;

use App\Mail\TwoFactorCodeMail;
use App\Mail\RequestStatusUpdated;
use App\Mail\TrackingVerificationCode;
use App\Models\Request as RequestRecord;
use App\Models\Settings;
use App\Models\Template;
use App\Models\User;
use App\Services\BrowserLetterPdfService;
use App\Services\GoogleDriveLetterBackupService;
use App\Services\LetterPdfService;
use App\Services\LetterService;
use ArPHP\I18N\Arabic as ArabicText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_privileged_api_login_requires_second_factor_before_token_is_issued(): void
    {
        Mail::fake();

        $user = $this->createAdminUser([
            'two_factor_method' => 'email',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(202)->assertJson([
            'requires_two_factor' => true,
            'two_factor_method' => 'email',
        ]);

        $user->refresh();

        $this->assertNotNull($user->two_factor_email_code);
        $this->assertNotNull($user->two_factor_expires_at);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Mail::assertSent(TwoFactorCodeMail::class);
    }

    public function test_privileged_api_login_with_valid_second_factor_can_access_sensitive_routes(): void
    {
        $user = $this->createAdminUser([
            'two_factor_method' => 'email',
            'two_factor_confirmed_at' => now(),
            'two_factor_email_code' => Hash::make('123456'),
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'two_factor_code' => '123456',
        ]);

        $loginResponse->assertOk()->assertJsonStructure([
            'user' => ['id', 'email'],
            'token',
        ]);

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/requests')
            ->assertOk()
            ->assertJson([
                'requests' => [],
            ]);
    }

    public function test_public_letter_requires_verified_tracking_session(): void
    {
        $request = $this->createApprovedRequest();

        $this->get(route('public.letter', ['tracking_id' => $request->tracking_id]))
            ->assertForbidden();
    }

    public function test_public_letter_is_available_with_verified_tracking_session(): void
    {
        $request = $this->createApprovedRequest();

        $this->withSession([
            'tracking_verified_request_id' => $request->id,
            'tracking_verified_tracking_id' => $request->tracking_id,
            'tracking_verified_until' => now()->addMinutes(30)->timestamp,
        ])->get(route('public.letter', ['tracking_id' => $request->tracking_id]))
            ->assertOk()
            ->assertSee($request->student_name)
            ->assertSee('Print / Save');
    }

    public function test_public_letter_pdf_is_served_inline_for_verified_tracking_session(): void
    {
        $request = $this->createApprovedRequest();

        $this->withSession([
            'tracking_verified_request_id' => $request->id,
            'tracking_verified_tracking_id' => $request->tracking_id,
            'tracking_verified_until' => now()->addMinutes(30)->timestamp,
        ])->get(route('public.letter.pdf', ['tracking_id' => $request->tracking_id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="Recommendation_Letter_' . $request->tracking_id . '.pdf"');
    }

    public function test_tracking_lookup_rejects_malformed_tracking_id_before_lookup(): void
    {
        Mail::fake();

        $this->post(route('public.tracking.post'), [
            'trackingId' => 'aaa',
            'verificationToken' => 'ID-TRACK-1',
        ])
            ->assertSessionHasErrors([
                'trackingId' => 'Tracking ID must be in the format REC-2026-AB12CD34.',
            ])
            ->assertSessionMissing('2fa_request_id');

        Mail::assertNothingSent();
    }

    public function test_tracking_lookup_shows_specific_message_for_valid_format_but_wrong_id_pair(): void
    {
        Mail::fake();

        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK001',
            'verification_token' => 'ID-TRACK-1',
        ]);

        $this->post(route('public.tracking.post'), [
            'trackingId' => $request->tracking_id,
            'verificationToken' => 'WRONG-ID-TRACK',
        ])
            ->assertRedirect()
            ->assertSessionHas('error', 'We could not find a request matching this Tracking ID and Student / National ID. Please check both values and try again.')
            ->assertSessionMissing('2fa_request_id');

        Mail::assertNothingSent();
    }

    public function test_tracking_lookup_normalizes_valid_tracking_id_and_starts_otp_verification(): void
    {
        Mail::fake();

        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK002',
            'student_email' => 'tracking.student@example.test',
            'verification_token' => 'ID-TRACK-2',
        ]);

        $this->post(route('public.tracking.post'), [
            'trackingId' => 'rec-2026-track002',
            'verificationToken' => 'ID-TRACK-2',
        ])
            ->assertRedirect(route('public.tracking.verify'))
            ->assertSessionHas('2fa_request_id', $request->id)
            ->assertSessionHas('2fa_tracking_id', $request->tracking_id)
            ->assertSessionHas('success', function ($message) {
                return is_string($message)
                    && str_contains($message, 'Request found.')
                    && str_contains($message, '6-digit verification code');
            });

        Mail::assertSent(TrackingVerificationCode::class);
    }

    public function test_tracking_lookup_shows_state_message_for_archived_request(): void
    {
        Mail::fake();

        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-ARCH0001',
            'verification_token' => 'ID-ARCH-1',
            'status' => 'Archived',
        ]);

        $this->post(route('public.tracking.post'), [
            'trackingId' => $request->tracking_id,
            'verificationToken' => 'ID-ARCH-1',
        ])
            ->assertRedirect()
            ->assertSessionHas('error', 'This request is archived and is no longer available in the student tracker. Please contact administration if you still need help.')
            ->assertSessionMissing('2fa_request_id');

        Mail::assertNothingSent();
    }

    public function test_tracking_verify_page_shows_masked_delivery_hint_and_resend_controls(): void
    {
        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK003',
        ]);

        $this->withSession([
            '2fa_otp' => '123456',
            '2fa_expires' => now()->addMinutes(5)->toDateTimeString(),
            '2fa_request_id' => $request->id,
            '2fa_tracking_id' => $request->tracking_id,
            '2fa_delivery_method' => 'email',
            '2fa_delivery_hint' => 'tr***@ex***.test',
        ])->get(route('public.tracking.verify'))
            ->assertOk()
            ->assertSee('Verify Access')
            ->assertSee($request->tracking_id)
            ->assertSee('tr***@ex***.test')
            ->assertSee('Code expires in')
            ->assertSee('Send New Code');
    }

    public function test_public_tracking_verification_shows_clear_message_for_wrong_code(): void
    {
        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK004',
        ]);

        $this->from(route('public.tracking.verify'))
            ->withSession([
                '2fa_otp' => '654321',
                '2fa_expires' => now()->addMinutes(5)->toDateTimeString(),
                '2fa_request_id' => $request->id,
                '2fa_tracking_id' => $request->tracking_id,
                '2fa_delivery_method' => 'email',
                '2fa_delivery_hint' => 'tr***@ex***.test',
            ])->post(route('public.tracking.verify.post'), [
                'otp' => '123456',
            ])
            ->assertRedirect(route('public.tracking.verify'))
            ->assertSessionHas('error', 'Invalid verification code. Please check the 6-digit code and try again.')
            ->assertSessionHas('2fa_request_id', $request->id)
            ->assertSessionHas('2fa_tracking_id', $request->tracking_id);
    }

    public function test_expired_public_tracking_code_keeps_request_context_for_resend(): void
    {
        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK005',
        ]);

        $this->from(route('public.tracking.verify'))
            ->withSession([
                '2fa_otp' => '123456',
                '2fa_expires' => now()->subMinute()->toDateTimeString(),
                '2fa_request_id' => $request->id,
                '2fa_tracking_id' => $request->tracking_id,
                '2fa_delivery_method' => 'email',
                '2fa_delivery_hint' => 'tr***@ex***.test',
            ])->post(route('public.tracking.verify.post'), [
                'otp' => '123456',
            ])
            ->assertRedirect(route('public.tracking.verify'))
            ->assertSessionHas('error', 'This verification code has expired. Request a new code below.')
            ->assertSessionMissing('2fa_otp')
            ->assertSessionMissing('2fa_expires')
            ->assertSessionHas('2fa_request_id', $request->id)
            ->assertSessionHas('2fa_tracking_id', $request->tracking_id);
    }

    public function test_public_tracking_verification_resend_requires_an_active_tracking_session(): void
    {
        Mail::fake();

        $this->post(route('public.tracking.verify.resend'))
            ->assertRedirect(route('public.tracking'))
            ->assertSessionHas('error', 'Your verification session expired. Please track your request again.');

        Mail::assertNothingSent();
    }

    public function test_public_tracking_verification_resend_issues_a_fresh_code(): void
    {
        Mail::fake();

        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK006',
            'student_email' => 'tracking.resend@example.test',
        ]);

        $previousOtp = '123456';

        $this->withSession([
            '2fa_otp' => $previousOtp,
            '2fa_expires' => now()->addMinute()->toDateTimeString(),
            '2fa_request_id' => $request->id,
            '2fa_tracking_id' => $request->tracking_id,
            '2fa_delivery_method' => 'email',
            '2fa_delivery_hint' => 'tr***@ex***.test',
        ])->post(route('public.tracking.verify.resend'))
            ->assertRedirect(route('public.tracking.verify'))
            ->assertSessionHas('2fa_request_id', $request->id)
            ->assertSessionHas('2fa_tracking_id', $request->tracking_id)
            ->assertSessionHas('2fa_delivery_method', 'email')
            ->assertSessionHas('2fa_delivery_hint', function ($hint) use ($request) {
                return is_string($hint)
                    && str_contains($hint, 'tr')
                    && str_contains($hint, '.test');
            })
            ->assertSessionHas('2fa_otp', function ($otp) use ($previousOtp) {
                return is_string($otp)
                    && preg_match('/^\d{6}$/', $otp) === 1
                    && $otp !== $previousOtp;
            })
            ->assertSessionHas('2fa_expires', function ($expiresAt) {
                return $expiresAt !== null;
            })
            ->assertSessionHas('success', function ($message) {
                return is_string($message)
                    && str_contains($message, 'We sent a new 6-digit verification code');
            });

        Mail::assertSent(TrackingVerificationCode::class, function ($mail) use ($request) {
            return $mail->hasTo($request->student_email);
        });
    }

    public function test_public_tracking_verification_can_remember_browser_for_future_visits(): void
    {
        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK007',
            'student_email' => 'remember.browser@example.test',
        ]);

        $response = $this->withSession([
            '2fa_otp' => '123456',
            '2fa_expires' => now()->addMinutes(5)->toDateTimeString(),
            '2fa_request_id' => $request->id,
            '2fa_tracking_id' => $request->tracking_id,
            '2fa_delivery_method' => 'email',
            '2fa_delivery_hint' => 're***@ex***.test',
        ])->post(route('public.tracking.verify.post'), [
            'otp' => '123456',
            'remember_browser' => '1',
        ]);

        $response->assertOk()
            ->assertDontSee('Trusted browser')
            ->assertSee('Require a code on this browser');

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($candidate) => $candidate->getName() === 'trusted_tracking_browser');

        $this->assertNotNull($cookie);
    }

    public function test_tracking_lookup_skips_email_code_on_a_trusted_browser(): void
    {
        Mail::fake();

        $request = $this->createTrackableRequest([
            'tracking_id' => 'REC-2026-TRACK008',
            'student_email' => 'trusted.browser@example.test',
            'verification_token' => 'ID-TRACK-TRUST',
            'status' => 'Approved',
        ]);

        $rememberUntil = now()->addDays(30)->timestamp;
        $trustedCookie = json_encode([
            'remember_until' => $rememberUntil,
            'proof' => $this->trackingTrustedBrowserProof($request, $rememberUntil),
        ], JSON_UNESCAPED_SLASHES);

        $this->withCookie('trusted_tracking_browser', $trustedCookie)
            ->post(route('public.tracking.post'), [
                'trackingId' => $request->tracking_id,
                'verificationToken' => 'ID-TRACK-TRUST',
            ])
            ->assertOk()
            ->assertDontSee('Trusted browser')
            ->assertSee('Your recommendation letter is ready.')
            ->assertSee('Require a code on this browser');

        Mail::assertNothingSent();
    }

    public function test_tracking_results_show_clear_next_steps_for_student_statuses(): void
    {
        $cases = [
            [
                'tracking_id' => 'REC-2026-STAT0001',
                'status' => 'Submitted',
                'expected' => 'No action is needed yet. Keep your Tracking ID handy and check back for updates.',
            ],
            [
                'tracking_id' => 'REC-2026-STAT0002',
                'status' => 'Under Review',
                'expected' => 'No action is needed from you right now. Wait for another status update or message from administration.',
            ],
            [
                'tracking_id' => 'REC-2026-STAT0003',
                'status' => 'Needs Revision',
                'expected' => 'Use the edit button below, update the requested details, and resubmit your request.',
                'admin_message' => 'Please correct your training period before we continue.',
            ],
            [
                'tracking_id' => 'REC-2026-STAT0004',
                'status' => 'Approved',
                'expected' => 'Open your letter, review it, and download the PDF if you need a copy.',
            ],
            [
                'tracking_id' => 'REC-2026-STAT0005',
                'status' => 'Rejected',
                'expected' => 'Review the reason below. If anything is unclear, contact administration before creating a new request.',
                'rejection_reason' => 'We could not verify the required eligibility information.',
            ],
        ];

        foreach ($cases as $case) {
            $request = $this->createTrackableRequest([
                'tracking_id' => $case['tracking_id'],
                'status' => $case['status'],
                'admin_message' => $case['admin_message'] ?? null,
                'rejection_reason' => $case['rejection_reason'] ?? null,
            ]);

            $response = $this->withSession([
                '2fa_otp' => '123456',
                '2fa_expires' => now()->addMinutes(5)->toDateTimeString(),
                '2fa_request_id' => $request->id,
                '2fa_tracking_id' => $request->tracking_id,
                '2fa_delivery_method' => 'email',
                '2fa_delivery_hint' => 'tr***@ex***.test',
            ])->post(route('public.tracking.verify.post'), [
                'otp' => '123456',
            ]);

            $response
                ->assertOk()
                ->assertSee('What happens next')
                ->assertSee($case['expected']);

            if ($case['status'] === 'Approved') {
                $response->assertSee(route('public.letter', ['tracking_id' => $request->tracking_id]), false);
            }
        }
    }

    public function test_final_tracking_states_mark_review_step_as_completed(): void
    {
        foreach (['Approved', 'Rejected'] as $status) {
            $request = $this->createTrackableRequest([
                'tracking_id' => 'REC-2026-' . strtoupper(substr($status, 0, 4)) . 'DONE',
                'status' => $status,
                'rejection_reason' => $status === 'Rejected' ? 'Missing required eligibility information.' : null,
            ]);

            $this->withSession([
                '2fa_otp' => '123456',
                '2fa_expires' => now()->addMinutes(5)->toDateTimeString(),
                '2fa_request_id' => $request->id,
                '2fa_tracking_id' => $request->tracking_id,
                '2fa_delivery_method' => 'email',
                '2fa_delivery_hint' => 'tr***@ex***.test',
            ])->post(route('public.tracking.verify.post'), [
                'otp' => '123456',
            ])
                ->assertOk()
                ->assertSee('Completed')
                ->assertDontSee('In Progress');
        }
    }

    public function test_request_wizard_step_one_requires_purpose_details_when_other_is_selected(): void
    {
        $response = $this->post(route('public.request.wizard'), [
            'step' => 1,
            'action' => 'next',
            'data' => [
                'student_name' => 'Wizard',
                'last_name' => 'Student',
                'student_email' => 'wizard.student@example.test',
                'gender' => 'male',
                'university' => 'Test University',
                'verification_token' => 'ID-WIZ-001',
                'training_period' => '2026-04',
                'purpose' => 'Other',
                'deadline' => now()->addDays(5)->format('Y-m-d'),
            ],
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors([
                'data.purpose_other' => 'Please describe the purpose when you select Other',
            ]);

        $this->get(route('public.request'))
            ->assertOk()
            ->assertSee('Student & Request Information', false)
            ->assertSee('Purpose Details')
            ->assertSee('Email remains the main update channel. Add a phone number only if you want administration to have another contact method.');
    }

    public function test_request_wizard_review_step_is_read_only(): void
    {
        $template = Template::create([
            'name' => 'Wizard Template',
            'language' => 'en',
            'header_content' => '<p>Header</p>',
            'body_content' => '<p>Body</p>',
            'footer_content' => '<p>Footer</p>',
            'layout_settings' => [],
            'is_active' => true,
        ]);

        $this->withSession([
            'wizard_step' => 3,
            'wizard_data' => [
                'student_name' => 'Wizard',
                'last_name' => 'Student',
                'student_email' => 'wizard.student@example.test',
                'university' => 'Test University',
                'verification_token' => 'ID-WIZ-002',
                'training_period' => '2026-04',
                'purpose' => 'Other',
                'purpose_other' => 'Saudi Board application',
                'deadline' => now()->addDays(7)->format('Y-m-d'),
                'content_option' => 'template',
                'template_id' => $template->id,
                'notes' => 'Please emphasize clinical teamwork.',
            ],
        ])->get(route('public.request'))
            ->assertOk()
            ->assertSee('Step 3 is read-only.')
            ->assertSee('Other: Saudi Board application')
            ->assertDontSee('<select name="data[purpose]"', false)
            ->assertDontSee('<input type="date" name="data[deadline]"', false)
            ->assertDontSee('<textarea name="data[notes]"', false);
    }

    public function test_request_wizard_keeps_student_on_content_step_when_template_validation_fails(): void
    {
        $this->withSession([
            'wizard_step' => 2,
            'wizard_data' => [
                'student_name' => 'Wizard',
                'last_name' => 'Student',
                'student_email' => 'wizard.content@example.test',
                'gender' => 'female',
                'university' => 'Test University',
                'verification_token' => 'ID-WIZ-004',
                'training_period' => '2026-04',
                'purpose' => 'Residency',
                'deadline' => now()->addDays(8)->format('Y-m-d'),
            ],
        ])->post(route('public.request.wizard'), [
            'step' => 2,
            'action' => 'next',
            'data' => [
                'content_option' => 'template',
                'template_id' => '',
            ],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors([
                'template' => 'Please select a template',
            ]);

        $this->get(route('public.request'))
            ->assertOk()
            ->assertSee('Letter Content')
            ->assertSee('Please select a template');
    }

    public function test_request_wizard_submission_persists_other_purpose_details_in_form_data(): void
    {
        Mail::fake();

        $template = Template::create([
            'name' => 'Wizard Submit Template',
            'language' => 'en',
            'header_content' => '<p>Header</p>',
            'body_content' => '<p>Body</p>',
            'footer_content' => '<p>Footer</p>',
            'layout_settings' => [],
            'is_active' => true,
        ]);

        $wizardData = [
            'student_name' => 'Wizard',
            'last_name' => 'Student',
            'student_email' => 'wizard.submit@example.test',
            'gender' => 'female',
            'university' => 'Test University',
            'verification_token' => 'ID-WIZ-003',
            'training_period' => '2026-04',
            'purpose' => 'Other',
            'purpose_other' => 'Saudi Board application',
            'deadline' => now()->addDays(10)->format('Y-m-d'),
            'content_option' => 'template',
            'template_id' => $template->id,
            'notes' => 'Please highlight leadership and reliability.',
        ];

        $this->withSession([
            'wizard_step' => 3,
            'wizard_data' => $wizardData,
        ])->post(route('public.request.wizard'), [
            'step' => 3,
            'action' => 'submit',
        ])
            ->assertRedirect(route('public.request'))
            ->assertSessionMissing('wizard_step');

        $request = RequestRecord::query()->latest('id')->firstOrFail();

        $this->assertSame('Other', $request->purpose);
        $this->assertIsArray($request->form_data);
        $this->assertSame('Saudi Board application', $request->form_data['purpose_other'] ?? null);
        $this->assertSame('Other', $request->form_data['purpose'] ?? null);
    }

    public function test_pdf_letter_template_renders_single_page_for_standard_letter_content(): void
    {
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $template->update([
            'header_content' => '
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:40%; font-size:8pt; line-height:1.2;">
                            <strong>Kingdom of Saudi Arabia</strong><br>
                            National Guard Health Affairs<br>
                            King Abdulaziz Medical City - Jeddah<br>
                            Department of Emergency Medicine
                        </td>
                        <td style="width:20%; text-align:center;">
                            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="Logo" style="height:56px;">
                        </td>
                        <td style="width:40%; font-size:8pt; line-height:1.2; text-align:right;">
                            المملكة العربية السعودية<br>
                            الشؤون الصحية بوزارة الحرس الوطني<br>
                            مدينة الملك عبدالعزيز الطبية - جدة<br>
                            قسم طب الطوارئ
                        </td>
                    </tr>
                </table>
                <hr style="margin:8px 0 6px; border:none; border-top:1px solid #94a3b8;">
            ',
            'body_content' => '
                <p style="text-align:center; font-weight:700; font-size:12pt;">Dr. {{studentName}} {{lastName}}</p>
                <p style="text-align:center; font-weight:700;">To Whom It May Concern,</p>
                <p>This letter is to certify that Dr. {{studentName}} completed a rotation in the Emergency Department at King Abdulaziz Medical City, Jeddah during {{trainingPeriod}} as part of the medical internship program.</p>
                <p>Throughout this rotation, Dr. {{studentName}} demonstrated solid clinical knowledge, strong professionalism, and dependable teamwork. The student interacted effectively with patients, residents, consultants, nursing staff, and other healthcare team members.</p>
                <p>Dr. {{studentName}} showed particular interest in Emergency Medicine, with good situational awareness, appropriate prioritization, and the ability to work efficiently in a fast-paced environment. The student was receptive to feedback and showed continuous improvement during the rotation.</p>
                <p>Based on this performance, work ethic, and interpersonal skills, I believe Dr. {{studentName}} would be a valuable addition to any training program or institution.</p>
            ',
            'footer_content' => '
                <table style="width:100%; border-collapse:collapse; font-size:7pt; line-height:1.15;">
                    <tr>
                        <td style="width:33%;">P.O. Box 9515<br>Jeddah 21423<br>Kingdom of Saudi Arabia</td>
                        <td style="width:34%; text-align:center;">FAX: 624 7444</td>
                        <td style="width:33%; text-align:right;">ص.ب 9515<br>جدة 21423<br>المملكة العربية السعودية</td>
                    </tr>
                </table>
            ',
        ]);

        $request->update([
            'training_period' => '2026-04',
        ]);

        $compiled = app(LetterPdfService::class)->compile($request->fresh(), $template->fresh());

        $this->assertSame(1, $compiled['pdf_page_count']);
        $this->assertContains($compiled['fit']['status'], ['fits', 'auto_fitted']);
    }

    public function test_letter_pdf_service_auto_fits_long_content_to_one_a4_page(): void
    {
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $template->update([
            'body_content' => str_repeat(
                '<p>Dr. {{studentName}} consistently demonstrated clinical maturity, strong communication, dependable teamwork, and thoughtful follow-through across the emergency medicine rotation.</p>',
                24
            ),
            'footer_content' => '<p style="font-size:7pt;">King Abdulaziz Medical City, Jeddah</p>',
        ]);

        $compiled = app(LetterPdfService::class)->compile($request->fresh(), $template->fresh());

        $this->assertSame(1, $compiled['pdf_page_count']);
        $this->assertSame('auto_fitted', $compiled['fit']['status']);
        $this->assertTrue($compiled['fit']['can_export']);
    }

    public function test_letter_pdf_service_reports_when_content_still_exceeds_one_a4_page(): void
    {
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $template->update([
            'body_content' => str_repeat(
                '<p>Dr. {{studentName}} delivered sustained excellent performance across the rotation, including extensive clinical reflections, structured feedback summaries, and detailed competency descriptions for each week of training.</p>',
                55
            ),
            'footer_content' => str_repeat('<p style="font-size:7pt;">Emergency Medicine Department, King Abdulaziz Medical City, Jeddah</p>', 6),
        ]);

        $compiled = app(LetterPdfService::class)->compile($request->fresh(), $template->fresh());

        $this->assertGreaterThan(1, $compiled['pdf_page_count']);
        $this->assertSame('too_long', $compiled['fit']['status']);
        $this->assertFalse($compiled['fit']['can_export']);
        $this->assertNotEmpty($compiled['fit']['overflow_reason']);
    }

    public function test_letter_pdf_service_keeps_arabic_output_to_one_a4_page(): void
    {
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $template->update([
            'language' => 'ar',
            'body_content' => '
                <p style="text-align:right;">إلى من يهمه الأمر</p>
                <p style="text-align:right;">نفيدكم بأن الدكتور {{studentName}} {{lastName}} أكمل فترة تدريب سريري في قسم طب الطوارئ خلال {{trainingPeriod}}، وأظهر مستوى مهنياً متميزاً والتزاماً واضحاً بالتعلم والعمل الجماعي.</p>
                <p style="text-align:right;">كان تعامله مع المرضى والزملاء والاستشاريين احترافياً، وأثبت قدرة جيدة على تحمل المسؤولية والاستفادة من التغذية الراجعة بشكل مستمر.</p>
            ',
            'layout_settings' => [
                'language' => 'ar',
                'direction' => 'rtl',
                'fontFamily' => 'DejaVu Sans, sans-serif',
                'fontSize' => 12,
                'margins' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                ],
                'footer' => [
                    'enabled' => true,
                ],
            ],
        ]);

        $compiled = app(LetterPdfService::class)->compile($request->fresh(), $template->fresh());

        $this->assertSame(1, $compiled['pdf_page_count']);
        $this->assertContains($compiled['fit']['status'], ['fits', 'auto_fitted']);
        $this->assertStringContainsString('DejaVu Sans', $compiled['pdf_html']);
    }

    public function test_public_letter_pdf_refuses_to_serve_multi_page_official_output(): void
    {
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $template->update([
            'body_content' => str_repeat(
                '<p>Dr. {{studentName}} completed extensive documented duties, reflective summaries, case logs, competency narratives, multidisciplinary evaluations, and longitudinal commentary across the full emergency medicine rotation.</p>',
                60
            ),
            'footer_content' => str_repeat('<p style="font-size:7pt;">Emergency Medicine Department, King Abdulaziz Medical City, Jeddah</p>', 8),
        ]);

        $this->withSession([
            'tracking_verified_request_id' => $request->id,
            'tracking_verified_tracking_id' => $request->tracking_id,
            'tracking_verified_until' => now()->addMinutes(30)->timestamp,
        ])->get(route('public.letter.pdf', ['tracking_id' => $request->tracking_id]))
            ->assertStatus(409);
    }

    public function test_letter_service_keeps_only_one_inline_signature_and_qr_placeholder_across_template_sections(): void
    {
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $template->update([
            'header_content' => '<div>{{signature}}</div><div>{{signature}}</div>',
            'body_content' => '<div>{{qrCode}}</div><div>{{qrCode}}</div><div>{{signature}}</div>',
            'footer_content' => '<div>{{qrCode}}</div>',
            'signature_image' => 'https://example.test/signature.png',
            'stamp_image' => 'https://example.test/stamp.png',
        ]);

        $content = app(LetterService::class)->generateLetterContent($request->fresh(), $template->fresh());
        $combined = ($content['header'] ?? '') . ($content['body'] ?? '') . ($content['footer'] ?? '');

        $this->assertSame(1, substr_count($combined, 'official-signature'));
        $this->assertSame(1, substr_count($combined, 'qr-code-container'));
    }

    public function test_pdf_html_preparation_shapes_arabic_and_removes_rtl_direction_for_visual_glyphs(): void
    {
        $service = app(LetterService::class);
        $arabic = new ArabicText();

        $html = '<td style="text-align: right; direction: rtl; font-family: \'DejaVu Sans\', sans-serif;">المملكة العربية السعودية</td>';
        $prepared = $service->prepareHtmlForPdf($html);

        $this->assertStringNotContainsString('direction: rtl', $prepared);
        $this->assertStringContainsString($arabic->utf8Glyphs('المملكة العربية السعودية'), $prepared);
        $this->assertStringContainsString('text-align: right', $prepared);
    }

    public function test_html_sanitizer_handles_signature_markup_without_falling_back(): void
    {
        Log::spy();

        $service = app(LetterService::class);
        $html = '<div class="official-signature"><div style="margin-bottom: 5px;"><img src="https://example.test/signature.png" style="max-height: 80px; display: block;" alt="Signature"></div><div style="margin-top: -40px; margin-left: 100px;"><img src="https://example.test/stamp.png" style="max-height: 100px; opacity: 0.8;" alt="Stamp"></div></div>';

        $sanitized = $service->sanitizeHtml($html);

        $this->assertStringContainsString('official-signature', $sanitized);
        $this->assertStringContainsString('max-height:80px', str_replace(' ', '', $sanitized));
        $this->assertStringContainsString('margin-left:100px', str_replace(' ', '', $sanitized));
        $this->assertStringNotContainsString('display: block', $sanitized);
        $this->assertStringNotContainsString('opacity: 0.8', $sanitized);

        Log::shouldNotHaveReceived('warning');
    }

    public function test_api_status_update_generates_verify_token_and_clears_stale_admin_message(): void
    {
        Mail::fake();

        $user = $this->createAdminUser();
        $template = Template::create([
            'name' => 'Approval Template',
            'header_content' => '<p>Header</p>',
            'body_content' => '<p>Letter for {{studentName}} {{lastName}}</p>',
            'footer_content' => '<p>Footer</p>',
            'layout_settings' => [],
            'is_active' => true,
        ]);
        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-STATUS01',
            'student_name' => 'Student',
            'last_name' => 'One',
            'student_email' => 'student@example.test',
            'verification_token' => 'ID-12345',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Needs Revision',
            'admin_message' => 'Old revision note',
            'template_id' => $template->id,
            'form_data' => [
                'template_id' => $template->id,
                'gender' => 'male',
            ],
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'two_factor_code' => '123456',
        ]);

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/requests/' . $request->id . '/status', [
                'status' => 'Approved',
            ])
            ->assertOk();

        $request->refresh();

        $this->assertSame('Approved', $request->status);
        $this->assertNull($request->admin_message);
        $this->assertNull($request->rejection_reason);
        $this->assertNotNull($request->verify_token);

        Mail::assertSent(RequestStatusUpdated::class);
    }

    public function test_api_request_update_ignores_token_mutations_even_for_admin(): void
    {
        $user = $this->createAdminUser();
        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-TOKEN01',
            'student_name' => 'Student',
            'last_name' => 'Tokens',
            'student_email' => 'tokens@example.test',
            'verification_token' => 'ID-ORIGINAL',
            'verify_token' => 'verify-original',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'two_factor_code' => '123456',
        ]);

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/requests/' . $request->id, [
                'studentName' => 'Student Updated',
                'verificationToken' => 'ID-MUTATED',
                'verifyToken' => 'verify-mutated',
                'formData' => [
                    'verify_token' => 'nested-mutated',
                    'verification_token' => 'nested-id-mutated',
                ],
            ])
            ->assertOk();

        $request->refresh();

        $this->assertSame('Student Updated', $request->student_name);
        $this->assertSame('ID-ORIGINAL', $request->verification_token);
        $this->assertSame('verify-original', $request->verify_token);
    }

    public function test_bulk_approve_reuses_status_side_effects(): void
    {
        Mail::fake();

        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $requests = collect([
            RequestRecord::create([
                'tracking_id' => 'REC-2026-BULK01',
                'student_name' => 'Student',
                'last_name' => 'One',
                'student_email' => 'bulk-one@example.test',
                'verification_token' => 'ID-BULK-1',
                'university' => 'Test University',
                'purpose' => 'Residency',
                'training_period' => '2026-04',
                'status' => 'Needs Revision',
                'admin_message' => 'Needs update',
            ]),
            RequestRecord::create([
                'tracking_id' => 'REC-2026-BULK02',
                'student_name' => 'Student',
                'last_name' => 'Two',
                'student_email' => 'bulk-two@example.test',
                'verification_token' => 'ID-BULK-2',
                'university' => 'Test University',
                'purpose' => 'Residency',
                'training_period' => '2026-05',
                'status' => 'Submitted',
            ]),
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post('/admin/requests/bulk', [
                'ids' => $requests->pluck('id')->values()->toJson(),
                'action' => 'approve',
            ])
            ->assertRedirect();

        foreach ($requests as $request) {
            $request->refresh();
            $this->assertSame('Approved', $request->status);
            $this->assertNull($request->admin_message);
            $this->assertNotNull($request->verify_token);
        }

        Mail::assertSent(RequestStatusUpdated::class, 2);
    }

    public function test_bulk_status_update_can_target_all_filtered_requests(): void
    {
        Mail::fake();

        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $matchingRequests = collect([
            RequestRecord::create([
                'tracking_id' => 'REC-2026-FILTER01',
                'student_name' => 'Filter',
                'last_name' => 'One',
                'student_email' => 'filter-one@example.test',
                'verification_token' => 'ID-FILTER-1',
                'university' => 'Shared University',
                'purpose' => 'Residency',
                'training_period' => '2026-04',
                'status' => 'Submitted',
            ]),
            RequestRecord::create([
                'tracking_id' => 'REC-2026-FILTER02',
                'student_name' => 'Filter',
                'last_name' => 'Two',
                'student_email' => 'filter-two@example.test',
                'verification_token' => 'ID-FILTER-2',
                'university' => 'Shared University',
                'purpose' => 'Residency',
                'training_period' => '2026-05',
                'status' => 'Submitted',
            ]),
        ]);

        $unmatchedRequest = RequestRecord::create([
            'tracking_id' => 'REC-2026-FILTER03',
            'student_name' => 'Other',
            'last_name' => 'Student',
            'student_email' => 'filter-three@example.test',
            'verification_token' => 'ID-FILTER-3',
            'university' => 'Different University',
            'purpose' => 'Residency',
            'training_period' => '2026-06',
            'status' => 'Submitted',
        ]);

        $sharedMessage = 'We have started reviewing your request and will contact you if anything else is needed.';

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->post('/admin/requests/bulk', [
                'operation' => 'status',
                'selection_scope' => 'filtered',
                'filters' => json_encode([
                    'status' => 'Submitted',
                    'university' => 'Shared University',
                ], JSON_THROW_ON_ERROR),
                'status' => 'Under Review',
                'admin_message' => $sharedMessage,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Updated 2 request(s) to Under Review.');

        foreach ($matchingRequests as $request) {
            $request->refresh();
            $this->assertSame('Under Review', $request->status);
            $this->assertSame($sharedMessage, $request->admin_message);
        }

        $unmatchedRequest->refresh();
        $this->assertSame('Submitted', $unmatchedRequest->status);
        $this->assertNull($unmatchedRequest->admin_message);

        Mail::assertSent(RequestStatusUpdated::class, 2);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'bulk_request_status_update',
        ]);
    }

    public function test_admin_requests_page_renders_bulk_status_controls(): void
    {
        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        RequestRecord::create([
            'tracking_id' => 'REC-2026-BULKUI01',
            'student_name' => 'Bulk',
            'last_name' => 'Ui',
            'student_email' => 'bulk-ui@example.test',
            'verification_token' => 'ID-BULK-UI',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->get('/admin/requests')
            ->assertOk()
            ->assertSee('Change Status')
            ->assertSee('Bulk status update')
            ->assertSee('All requests currently in the list')
            ->assertSee('Export to Google Drive')
            ->assertSee('Back up approved letters to Google Drive')
            ->assertSee('Export Letters PDF')
            ->assertSee('Export approved letters as PDF');
    }

    public function test_admin_can_save_google_drive_settings_and_encrypt_service_account_json(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $serviceAccountJson = json_encode([
            'client_email' => 'drive-bot@example.test',
            'private_key' => "-----BEGIN PRIVATE KEY-----\nFAKE\n-----END PRIVATE KEY-----\n",
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->from(route('admin.settings'))
            ->put(route('admin.settings.update'), [
                'settingsGroup' => 'google_drive',
                'googleDriveEnabled' => 'on',
                'googleDriveServiceAccountJson' => $serviceAccountJson,
                'googleDriveFolderId' => 'https://drive.google.com/drive/folders/folder-123456',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success', 'Settings updated successfully!');

        $this->assertSame('true', Settings::getValue('googleDriveEnabled'));
        $this->assertSame('folder-123456', Settings::getValue('googleDriveFolderId'));
        $this->assertSame($serviceAccountJson, Settings::getValue('googleDriveServiceAccountJson'));
        $this->assertNotSame($serviceAccountJson, Settings::where('key', 'googleDriveServiceAccountJson')->value('value'));
    }

    public function test_admin_can_save_browserless_settings_and_encrypt_token(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $token = 'browserless-secret-token';

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->from(route('admin.settings'))
            ->put(route('admin.settings.update'), [
                'settingsGroup' => 'pdf_export',
                'pdfExportDriver' => 'browserless',
                'browserlessBaseUrl' => 'https://production-sfo.browserless.io',
                'browserlessToken' => $token,
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success', 'Settings updated successfully!');

        $this->assertSame('browserless', Settings::getValue('pdfExportDriver'));
        $this->assertSame('https://production-sfo.browserless.io', Settings::getValue('browserlessBaseUrl'));
        $this->assertSame($token, Settings::getValue('browserlessToken'));
        $this->assertNotSame($token, Settings::where('key', 'browserlessToken')->value('value'));
    }

    public function test_bulk_rejected_status_requires_shared_message(): void
    {
        Mail::fake();

        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-BULKREJECT',
            'student_name' => 'Bulk',
            'last_name' => 'Reject',
            'student_email' => 'bulk-reject@example.test',
            'verification_token' => 'ID-BULK-REJECT',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->from('/admin/requests')
            ->post('/admin/requests/bulk', [
                'operation' => 'status',
                'selection_scope' => 'selected',
                'ids' => json_encode([$request->id], JSON_THROW_ON_ERROR),
                'status' => 'Rejected',
                'admin_message' => '',
            ])
            ->assertRedirect('/admin/requests')
            ->assertSessionHasErrors([
                'admin_message' => 'A shared rejection reason is required for bulk rejection.',
            ]);

        $request->refresh();
        $this->assertSame('Submitted', $request->status);
        $this->assertNull($request->rejection_reason);

        Mail::assertNothingSent();
    }

    public function test_admin_can_download_single_approved_letter_pdf(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $request = $this->createApprovedRequest();
        $fakePdfService = $this->bindFakeBrowserLetterPdfService();

        $response = $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->get(route('admin.requests.letter-pdf', $request->id));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="Recommendation_Letter_' . $request->tracking_id . '.pdf"');

        $this->assertSame([$request->id], $fakePdfService->renderedRequestIds);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_download_letter_pdf',
            'target_id' => $request->id,
        ]);
    }

    public function test_browser_letter_pdf_service_uses_browserless_when_configured(): void
    {
        Http::fake([
            'https://production-sfo.browserless.io/pdf?token=browserless-secret-token' => Http::response('%PDF-BROWSERLESS', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        Settings::updateOrCreate(['key' => 'pdfExportDriver'], ['value' => 'browserless']);
        Settings::updateOrCreate(['key' => 'browserlessBaseUrl'], ['value' => 'https://production-sfo.browserless.io']);
        Settings::updateOrCreate(['key' => 'browserlessToken'], ['value' => 'browserless-secret-token']);

        $request = $this->createApprovedRequest();

        $pdf = app(BrowserLetterPdfService::class)->renderRequestPdf($request);

        $this->assertSame('%PDF-BROWSERLESS', $pdf['binary']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://production-sfo.browserless.io/pdf?token=browserless-secret-token') {
                return false;
            }

            $data = $request->data();

            return isset($data['html'], $data['options'])
                && $data['options']['format'] === 'A4'
                && $data['options']['printBackground'] === true
                && $data['options']['preferCSSPageSize'] === true;
        });
    }

    public function test_browser_letter_pdf_service_auto_uses_browserless_in_production_when_token_exists(): void
    {
        Http::fake([
            'https://production-sfo.browserless.io/pdf?token=browserless-secret-token' => Http::response('%PDF-BROWSERLESS-AUTO', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        config(['app.env' => 'production']);
        Settings::where('key', 'pdfExportDriver')->delete();
        Settings::updateOrCreate(['key' => 'browserlessBaseUrl'], ['value' => 'https://production-sfo.browserless.io']);
        Settings::updateOrCreate(['key' => 'browserlessToken'], ['value' => 'browserless-secret-token']);

        $request = $this->createApprovedRequest();

        $pdf = app(BrowserLetterPdfService::class)->renderRequestPdf($request);

        $this->assertSame('%PDF-BROWSERLESS-AUTO', $pdf['binary']);
    }

    public function test_browser_letter_pdf_service_rejects_non_pdf_browserless_response(): void
    {
        Http::fake([
            'https://production-sfo.browserless.io/pdf?token=browserless-secret-token' => Http::response('{"ok":true}', 200, [
                'Content-Type' => 'application/json',
            ]),
        ]);

        Settings::updateOrCreate(['key' => 'pdfExportDriver'], ['value' => 'browserless']);
        Settings::updateOrCreate(['key' => 'browserlessBaseUrl'], ['value' => 'https://production-sfo.browserless.io']);
        Settings::updateOrCreate(['key' => 'browserlessToken'], ['value' => 'browserless-secret-token']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('it was not a valid PDF');

        app(BrowserLetterPdfService::class)->renderRequestPdf($this->createApprovedRequest());
    }

    public function test_admin_pdf_export_surfaces_browserless_configuration_error(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $request = $this->createApprovedRequest();

        Settings::updateOrCreate(['key' => 'pdfExportDriver'], ['value' => 'browserless']);
        Settings::where('key', 'browserlessToken')->delete();

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->from('/admin/requests')
            ->post(route('admin.requests.letters.export-pdf'), [
                'selection_scope' => 'selected',
                'ids' => json_encode([$request->id], JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect('/admin/requests')
            ->assertSessionHas('error', function ($message) {
                return is_string($message)
                    && str_contains($message, 'Browserless is not fully configured')
                    && str_contains($message, 'PDF Export Renderer');
            });
    }

    public function test_admin_can_test_browserless_connection(): void
    {
        Http::fake([
            'https://production-sfo.browserless.io/pdf?token=browserless-secret-token' => Http::response('%PDF-TEST', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        Settings::updateOrCreate(['key' => 'pdfExportDriver'], ['value' => 'browserless']);
        Settings::updateOrCreate(['key' => 'browserlessBaseUrl'], ['value' => 'https://production-sfo.browserless.io']);
        Settings::updateOrCreate(['key' => 'browserlessToken'], ['value' => 'browserless-secret-token']);

        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->post(route('admin.settings.test-browserless'))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Browserless generated a PDF successfully.',
            ]);
    }

    public function test_admin_can_export_selected_approved_letters_as_zip(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $approved = $this->createApprovedRequest();
        $submitted = RequestRecord::create([
            'tracking_id' => 'REC-2026-NOTAPPROVED1',
            'student_name' => 'Pending',
            'last_name' => 'Student',
            'student_email' => 'pending@example.test',
            'verification_token' => 'ID-PENDING',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-05',
            'status' => 'Submitted',
        ]);

        $fakePdfService = $this->bindFakeBrowserLetterPdfService();

        $response = $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->post(route('admin.requests.letters.export-pdf'), [
                'selection_scope' => 'selected',
                'ids' => json_encode([$approved->id, $submitted->id], JSON_THROW_ON_ERROR),
            ]);

        $response->assertOk()
            ->assertHeader('content-type', 'application/zip');

        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));
        $this->assertSame([$approved->id], $fakePdfService->zipRequestIds);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_export_letters_pdf_zip',
        ]);
    }

    public function test_admin_can_export_filtered_approved_letters_as_zip(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $template = Template::create([
            'name' => 'Filter Template',
            'header_content' => '<p>Header</p>',
            'body_content' => '<p>Body</p>',
            'footer_content' => '<p>Footer</p>',
            'layout_settings' => [],
            'is_active' => true,
        ]);

        $approvedA = RequestRecord::create([
            'tracking_id' => 'REC-2026-EXPORTA1',
            'student_name' => 'Export',
            'last_name' => 'Alpha',
            'student_email' => 'export-alpha@example.test',
            'verification_token' => 'ID-EXPORT-A',
            'verify_token' => 'verify-export-a',
            'university' => 'Drive University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Approved',
            'template_id' => $template->id,
            'form_data' => ['template_id' => $template->id, 'gender' => 'male'],
        ]);

        $approvedB = RequestRecord::create([
            'tracking_id' => 'REC-2026-EXPORTB1',
            'student_name' => 'Export',
            'last_name' => 'Beta',
            'student_email' => 'export-beta@example.test',
            'verification_token' => 'ID-EXPORT-B',
            'verify_token' => 'verify-export-b',
            'university' => 'Drive University',
            'purpose' => 'Residency',
            'training_period' => '2026-05',
            'status' => 'Approved',
            'template_id' => $template->id,
            'form_data' => ['template_id' => $template->id, 'gender' => 'male'],
        ]);

        RequestRecord::create([
            'tracking_id' => 'REC-2026-EXPORTC1',
            'student_name' => 'Export',
            'last_name' => 'Gamma',
            'student_email' => 'export-gamma@example.test',
            'verification_token' => 'ID-EXPORT-C',
            'university' => 'Other University',
            'purpose' => 'Residency',
            'training_period' => '2026-06',
            'status' => 'Approved',
        ]);

        $fakePdfService = $this->bindFakeBrowserLetterPdfService();

        $response = $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->post(route('admin.requests.letters.export-pdf'), [
                'selection_scope' => 'filtered',
                'filters' => json_encode([
                    'status' => 'All',
                    'university' => 'Drive University',
                ], JSON_THROW_ON_ERROR),
            ]);

        $response->assertOk()
            ->assertHeader('content-type', 'application/zip');

        $this->assertEqualsCanonicalizing([$approvedA->id, $approvedB->id], $fakePdfService->zipRequestIds);
    }

    public function test_admin_can_sync_single_approved_letter_to_google_drive(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $request = $this->createApprovedRequest();
        $fakeDriveService = $this->bindFakeGoogleDriveLetterBackupService();

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->from(route('admin.requests.show', $request->id))
            ->post(route('admin.requests.letter-drive', $request->id))
            ->assertRedirect(route('admin.requests.show', $request->id))
            ->assertSessionHas('success', 'Letter backed up to Google Drive successfully.');

        $request->refresh();

        $this->assertSame([$request->id], $fakeDriveService->syncedRequestIds);
        $this->assertSame('synced', $request->drive_backup_status);
        $this->assertSame('drive-file-' . $request->id, $request->drive_backup_file_id);
        $this->assertSame('https://drive.google.com/file/d/drive-file-' . $request->id . '/view', $request->drive_backup_url);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_sync_letter_google_drive',
            'target_id' => $request->id,
        ]);
    }

    public function test_admin_can_export_selected_approved_letters_to_google_drive(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $approved = $this->createApprovedRequest();
        $submitted = RequestRecord::create([
            'tracking_id' => 'REC-2026-NOTDRIVE01',
            'student_name' => 'Pending',
            'last_name' => 'Student',
            'student_email' => 'pending-drive@example.test',
            'verification_token' => 'ID-PENDING-DRIVE',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-05',
            'status' => 'Submitted',
        ]);

        $fakeDriveService = $this->bindFakeGoogleDriveLetterBackupService();

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->from('/admin/requests')
            ->post(route('admin.requests.letters.export-drive'), [
                'selection_scope' => 'selected',
                'ids' => json_encode([$approved->id, $submitted->id], JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect('/admin/requests')
            ->assertSessionHas('success', function ($message) {
                return is_string($message)
                    && str_contains($message, 'Backed up 1 approved letter(s) to Google Drive.')
                    && str_contains($message, 'skipped because they are not approved');
            });

        $this->assertSame([$approved->id], $fakeDriveService->syncedRequestIds);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_export_letters_google_drive',
        ]);
    }

    public function test_admin_can_export_filtered_approved_letters_to_google_drive(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $template = Template::create([
            'name' => 'Drive Template',
            'header_content' => '<p>Header</p>',
            'body_content' => '<p>Body</p>',
            'footer_content' => '<p>Footer</p>',
            'layout_settings' => [],
            'is_active' => true,
        ]);

        $approvedA = RequestRecord::create([
            'tracking_id' => 'REC-2026-DRIVEA1',
            'student_name' => 'Drive',
            'last_name' => 'Alpha',
            'student_email' => 'drive-alpha@example.test',
            'verification_token' => 'ID-DRIVE-A',
            'verify_token' => 'verify-drive-a',
            'university' => 'Drive University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Approved',
            'template_id' => $template->id,
            'form_data' => ['template_id' => $template->id, 'gender' => 'male'],
        ]);

        $approvedB = RequestRecord::create([
            'tracking_id' => 'REC-2026-DRIVEB1',
            'student_name' => 'Drive',
            'last_name' => 'Beta',
            'student_email' => 'drive-beta@example.test',
            'verification_token' => 'ID-DRIVE-B',
            'verify_token' => 'verify-drive-b',
            'university' => 'Drive University',
            'purpose' => 'Residency',
            'training_period' => '2026-05',
            'status' => 'Approved',
            'template_id' => $template->id,
            'form_data' => ['template_id' => $template->id, 'gender' => 'male'],
        ]);

        RequestRecord::create([
            'tracking_id' => 'REC-2026-DRIVEC1',
            'student_name' => 'Drive',
            'last_name' => 'Gamma',
            'student_email' => 'drive-gamma@example.test',
            'verification_token' => 'ID-DRIVE-C',
            'university' => 'Other University',
            'purpose' => 'Residency',
            'training_period' => '2026-06',
            'status' => 'Approved',
        ]);

        $fakeDriveService = $this->bindFakeGoogleDriveLetterBackupService();

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->from('/admin/requests')
            ->post(route('admin.requests.letters.export-drive'), [
                'selection_scope' => 'filtered',
                'filters' => json_encode([
                    'status' => 'All',
                    'university' => 'Drive University',
                ], JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect('/admin/requests')
            ->assertSessionHas('success', function ($message) {
                return is_string($message)
                    && str_contains($message, 'Backed up 2 approved letter(s) to Google Drive.');
            });

        $this->assertEqualsCanonicalizing([$approvedA->id, $approvedB->id], $fakeDriveService->syncedRequestIds);
    }

    public function test_admin_request_details_page_shows_download_pdf_button_for_approved_request(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $request = $this->createApprovedRequest();
        $request->forceFill([
            'drive_backup_status' => 'synced',
            'drive_backup_file_name' => 'Recommendation_Letter_' . $request->tracking_id . '.pdf',
            'drive_backup_file_id' => 'drive-file-' . $request->id,
            'drive_backup_url' => 'https://drive.google.com/file/d/drive-file-' . $request->id . '/view',
            'drive_backup_synced_at' => now(),
        ])->save();

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->get(route('admin.requests.show', $request->id))
            ->assertOk()
            ->assertSee(route('admin.requests.letter-pdf', $request->id), false)
            ->assertSee(route('admin.requests.letter-drive', $request->id), false)
            ->assertSee('Download PDF')
            ->assertSee('Google Drive Backup')
            ->assertSee('Open Drive File')
            ->assertSee('Copy Link');
    }

    public function test_admin_rejected_status_message_survives_to_student_tracking_view(): void
    {
        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-REJECT1',
            'student_name' => 'Rejected',
            'last_name' => 'Student',
            'student_email' => 'rejected@example.test',
            'verification_token' => 'ID-REJECT',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->patch('/admin/requests/' . $request->id . '/status', [
                'status' => 'Rejected',
                'admin_message' => 'Missing required documentation.',
            ])
            ->assertRedirect();

        $request->refresh();

        $this->assertSame('Rejected', $request->status);
        $this->assertNull($request->admin_message);
        $this->assertSame('Missing required documentation.', $request->rejection_reason);

        $html = view('public.tracking', [
            'settings' => [],
            'request' => $request,
            'id' => $request->tracking_id,
        ])->render();

        $this->assertStringContainsString('Missing required documentation.', $html);
    }

    public function test_admin_approved_status_message_survives_to_student_tracking_view(): void
    {
        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-APPROVE1',
            'student_name' => 'Approved',
            'last_name' => 'Student',
            'student_email' => 'approved@example.test',
            'verification_token' => 'ID-APPROVED',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->patch('/admin/requests/' . $request->id . '/status', [
                'status' => 'Approved',
                'admin_message' => 'Please review the final wording before you submit it to programs.',
            ])
            ->assertRedirect();

        $request->refresh();

        $this->assertSame('Approved', $request->status);
        $this->assertSame('Please review the final wording before you submit it to programs.', $request->admin_message);
        $this->assertNull($request->rejection_reason);

        $html = view('public.tracking', [
            'settings' => [],
            'request' => $request,
            'id' => $request->tracking_id,
        ])->render();

        $this->assertStringContainsString('Message from administration', $html);
        $this->assertStringContainsString('Please review the final wording before you submit it to programs.', $html);
    }

    public function test_admin_request_details_page_renders_attachment_link_even_with_unexpected_training_period(): void
    {
        $user = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);

        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-ATTACH1',
            'student_name' => 'Student',
            'last_name' => 'Attachment',
            'student_email' => 'attachment@example.test',
            'verification_token' => 'ID-ATTACH',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => 'unexpected-format',
            'status' => 'Submitted',
            'document_path' => 'uploads/demo.pdf',
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->get('/admin/requests/' . $request->id)
            ->assertOk()
            ->assertSee('/admin/requests/' . $request->id . '/document', false)
            ->assertSee('unexpected-format');
    }

    public function test_template_update_is_reflected_in_public_letter_output(): void
    {
        $admin = $this->createAdminUser();
        $request = $this->createApprovedRequest();
        $template = Template::findOrFail($request->template_id);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->put(route('admin.templates.update', $template->id), [
                'name' => 'Updated Template',
                'header_content' => '<p>Updated Header {{trackingId}}</p>',
                'body_content' => '<p>Updated letter for {{fullName}}</p>',
                'footer_content' => '<p>Updated Footer</p>',
                'signature_name' => 'Dr. {{lastName}} Reviewer',
                'signature_title' => 'Training Director',
                'signature_department' => 'Emergency Department',
                'signature_institution' => 'King Abdulaziz Medical City',
                'signature_email' => 'director@example.test',
                'signature_phone' => '123456789',
                'language' => 'en',
                'is_active' => 'on',
                'layout_settings' => [
                    'fontFamily' => "'Courier New', monospace",
                    'fontSize' => 13,
                    'margins' => [
                        'top' => 20,
                        'right' => 20,
                        'bottom' => 20,
                        'left' => 20,
                    ],
                    'border' => [
                        'enabled' => 1,
                        'width' => 2,
                        'style' => 'solid',
                        'color' => '#057f3a',
                    ],
                    'qrCode' => [
                        'enabled' => 0,
                    ],
                    'footer' => [
                        'enabled' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.templates'));

        $this->withSession([
            'tracking_verified_request_id' => $request->id,
            'tracking_verified_tracking_id' => $request->tracking_id,
            'tracking_verified_until' => now()->addMinutes(30)->timestamp,
        ])->get(route('public.letter', ['tracking_id' => $request->tracking_id]))
            ->assertOk()
            ->assertSee('Updated Header ' . $request->tracking_id, false)
            ->assertSee('Updated letter for', false)
            ->assertDontSee('Letter for Letter Student', false)
            ->assertDontSee('Scan to Verify', false)
            ->assertSee('Courier New', false);
    }

    public function test_admin_preview_returns_browser_letter_content(): void
    {
        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $request = $this->createApprovedRequest();

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->get(route('admin.requests.preview', $request->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('fit')
            ->assertJsonFragment([
                'body' => '<p>Letter for Letter Student</p>',
            ]);
    }

    public function test_admin_can_approve_request_even_if_optional_pdf_route_is_too_long(): void
    {
        Mail::fake();

        $admin = $this->createAdminUser([
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
        ]);
        $template = Template::create([
            'name' => 'Too Long Template',
            'header_content' => '<p>Header</p>',
            'body_content' => str_repeat(
                '<p>Dr. {{studentName}} completed extensive documented duties, reflective summaries, case logs, competency narratives, multidisciplinary evaluations, and longitudinal commentary across the full emergency medicine rotation.</p>',
                60
            ),
            'footer_content' => str_repeat('<p style="font-size:7pt;">Emergency Medicine Department, King Abdulaziz Medical City, Jeddah</p>', 8),
            'layout_settings' => [],
            'is_active' => true,
        ]);
        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-OVERFLOW1',
            'student_name' => 'Overflow',
            'last_name' => 'Student',
            'student_email' => 'overflow@example.test',
            'verification_token' => 'ID-OVERFLOW',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
            'template_id' => $template->id,
            'form_data' => [
                'template_id' => $template->id,
                'gender' => 'male',
            ],
        ]);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->patch(route('admin.requests.update-status', $request->id), [
                'status' => 'Approved',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Status updated successfully!');

        $this->assertSame('Approved', $request->fresh()->status);
    }

    public function test_template_editor_prefers_newer_autosaved_draft_when_reopened(): void
    {
        $admin = $this->createAdminUser();
        $template = Template::create([
            'name' => 'Saved Template',
            'header_content' => '<p>Saved Header</p>',
            'body_content' => '<p>Saved Body</p>',
            'footer_content' => '<p>Saved Footer</p>',
            'language' => 'en',
            'layout_settings' => [],
            'is_active' => true,
        ]);

        $template->update([
            'draft_data' => [
                'name' => 'Draft Template Name',
                'header_content' => '<p>Draft Header</p>',
                'body_content' => '<p>Draft Body</p>',
                'footer_content' => '<p>Draft Footer</p>',
                'signature_name' => 'Draft Signer',
                'language' => 'ar',
                'layout_settings' => [
                    'fontSize' => 14,
                ],
            ],
            'last_draft_saved_at' => now()->addMinute(),
        ]);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->get(route('admin.templates.edit', $template->id))
            ->assertOk()
            ->assertSee('Draft Template Name')
            ->assertSee('Draft Header', false)
            ->assertSee('Draft Body', false)
            ->assertSee('Draft Footer', false)
            ->assertSee('Draft Signer')
            ->assertSee('Unsaved draft restored');
    }

    private function createAdminUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'username' => 'adminuser',
            'password' => 'Password123!',
            'role' => 'admin',
            'is_active' => true,
            'two_factor_method' => 'email',
            'two_factor_confirmed_at' => now(),
            'two_factor_email_code' => Hash::make('123456'),
            'two_factor_expires_at' => now()->addMinutes(10),
        ], $overrides));
    }

    private function createTrackableRequest(array $overrides = []): RequestRecord
    {
        return RequestRecord::create(array_merge([
            'tracking_id' => 'REC-2026-TRACK100',
            'student_name' => 'Tracking',
            'last_name' => 'Student',
            'student_email' => 'tracking@example.test',
            'verification_token' => 'ID-TRACK-100',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
        ], $overrides));
    }

    private function createApprovedRequest(): RequestRecord
    {
        $template = Template::create([
            'name' => 'Test Template',
            'header_content' => '<p>Header</p>',
            'body_content' => '<p>Letter for {{studentName}} {{lastName}}</p>',
            'footer_content' => '<p>Footer</p>',
            'layout_settings' => [],
            'is_active' => true,
        ]);

        return RequestRecord::create([
            'tracking_id' => 'REC-2026-LETTER1',
            'student_name' => 'Letter',
            'last_name' => 'Student',
            'student_email' => 'letter@example.test',
            'verification_token' => 'ID-LETTER',
            'verify_token' => 'verify-token-123',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Approved',
            'template_id' => $template->id,
            'form_data' => [
                'template_id' => $template->id,
                'gender' => 'male',
            ],
        ]);
    }

    private function bindFakeBrowserLetterPdfService(): object
    {
        $fake = new class(app(LetterService::class)) extends BrowserLetterPdfService {
            public array $renderedRequestIds = [];
            public array $zipRequestIds = [];

            public function renderRequestPdf(RequestRecord $request): array
            {
                $this->renderedRequestIds[] = $request->id;

                return [
                    'binary' => '%PDF-FAKE-' . $request->tracking_id,
                    'filename' => $this->pdfFilename($request),
                    'tracking_id' => $request->tracking_id,
                ];
            }

            public function buildZipArchive(iterable $requests): array
            {
                $collected = [];
                foreach ($requests as $request) {
                    $collected[] = $request;
                    $this->zipRequestIds[] = $request->id;
                }

                $zipPath = tempnam(sys_get_temp_dir(), 'fake-letter-zip-');
                if ($zipPath === false) {
                    throw new \RuntimeException('Could not create fake ZIP path.');
                }

                @unlink($zipPath);
                $zipPath .= '.zip';

                $zip = new \ZipArchive();
                $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                foreach ($collected as $request) {
                    $zip->addFromString($this->pdfFilename($request), 'FAKE PDF CONTENT');
                }
                $zip->close();

                return [
                    'path' => $zipPath,
                    'filename' => 'Recommendation_Letters_Test.zip',
                    'exported_count' => count($collected),
                    'failed' => [],
                ];
            }
        };

        $this->app->instance(BrowserLetterPdfService::class, $fake);

        return $fake;
    }

    private function bindFakeGoogleDriveLetterBackupService(): object
    {
        $fake = new class extends GoogleDriveLetterBackupService {
            public array $syncedRequestIds = [];

            public function __construct()
            {
            }

            public function configurationSummary(): array
            {
                return [
                    'enabled' => true,
                    'configured' => true,
                    'service_account_email' => 'drive-bot@example.test',
                    'folder_id' => 'folder-123456',
                    'folder_url' => 'https://drive.google.com/drive/folders/folder-123456',
                ];
            }

            public function parseServiceAccountJsonString(string $rawJson): array
            {
                $decoded = json_decode($rawJson, true);
                if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
                    throw new \RuntimeException('Google Drive service account JSON must include client_email and private_key.');
                }

                return [
                    'client_email' => $decoded['client_email'],
                    'private_key' => $decoded['private_key'],
                    'token_uri' => 'https://oauth2.googleapis.com/token',
                ];
            }

            public function normalizeFolderReference(null|string $value): ?string
            {
                $value = trim((string) $value);
                if ($value === '') {
                    return null;
                }

                if (preg_match('~/folders/([a-zA-Z0-9_-]+)~', $value, $matches) === 1) {
                    return $matches[1];
                }

                return $value;
            }

            public function testConnection(): array
            {
                return [
                    'service_account_email' => 'drive-bot@example.test',
                    'folder_id' => 'folder-123456',
                    'folder_name' => 'Recommendation Letters',
                    'folder_url' => 'https://drive.google.com/drive/folders/folder-123456',
                ];
            }

            public function syncRequest(RequestRecord $request): array
            {
                $this->syncedRequestIds[] = $request->id;

                $request->forceFill([
                    'drive_backup_status' => 'synced',
                    'drive_backup_file_id' => 'drive-file-' . $request->id,
                    'drive_backup_file_name' => 'Recommendation_Letter_' . $request->tracking_id . '.pdf',
                    'drive_backup_url' => 'https://drive.google.com/file/d/drive-file-' . $request->id . '/view',
                    'drive_backup_error' => null,
                    'drive_backup_synced_at' => now(),
                ])->save();

                return [
                    'request_id' => $request->id,
                    'tracking_id' => $request->tracking_id,
                    'file_id' => 'drive-file-' . $request->id,
                    'file_name' => 'Recommendation_Letter_' . $request->tracking_id . '.pdf',
                    'file_url' => 'https://drive.google.com/file/d/drive-file-' . $request->id . '/view',
                    'folder_url' => 'https://drive.google.com/drive/folders/folder-123456',
                ];
            }

            public function syncMany(iterable $requests): array
            {
                $synced = [];

                foreach ($requests as $request) {
                    $synced[] = $this->syncRequest($request);
                }

                return [
                    'synced_count' => count($synced),
                    'failed' => [],
                    'synced' => $synced,
                    'folder_url' => 'https://drive.google.com/drive/folders/folder-123456',
                    'folder_id' => 'folder-123456',
                ];
            }
        };

        $this->app->instance(GoogleDriveLetterBackupService::class, $fake);

        return $fake;
    }

    private function trackingTrustedBrowserProof(RequestRecord $request, int $rememberUntil): string
    {
        $appKey = (string) config('app.key', '');
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if ($decoded !== false) {
                $appKey = $decoded;
            }
        }

        return hash_hmac('sha256', implode('|', [
            trim((string) $request->verification_token),
            strtolower(trim((string) $request->student_email)),
            (string) $rememberUntil,
        ]), $appKey);
    }
}
