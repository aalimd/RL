<?php

namespace Tests\Feature;

use App\Mail\TwoFactorCodeMail;
use App\Mail\RequestStatusUpdated;
use App\Models\Request as RequestRecord;
use App\Models\Template;
use App\Models\User;
use App\Services\LetterService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            ->assertSee($request->student_name);
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

        $letterService = app(LetterService::class);
        $content = $letterService->generateLetterContent($request->fresh(), $template->fresh());

        $data = [
            'request' => $request->fresh(),
            'layout' => $content['layout'],
            'header' => $letterService->sanitizeHtml($content['header']),
            'body' => $letterService->sanitizeHtml($content['body']),
            'footer' => $letterService->sanitizeHtml($content['footer']),
            'signature' => $content['signature'],
            'qrCode' => $content['qrCode'] ?? '',
        ];

        $html = view('pdf.letter', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', $data['layout']['fontFamily'] ?? 'DejaVu Sans');

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        $this->assertSame(1, $pdf->getCanvas()->get_page_count());
    }

    public function test_api_status_update_generates_verify_token_and_clears_stale_admin_message(): void
    {
        Mail::fake();

        $user = $this->createAdminUser();
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
}
