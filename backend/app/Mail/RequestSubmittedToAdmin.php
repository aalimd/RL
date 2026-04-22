<?php

namespace App\Mail;

use App\Models\Request as RequestModel;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestSubmittedToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public RequestModel $request;
    public string $detailsUrl;
    public array $branding;
    public string $subjectLine;
    public ?string $renderedBody;
    public ?string $renderedText;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->detailsUrl = url('/admin/requests/' . $request->id);
        $template = app(EmailTemplateRenderer::class)->render(
            'request_submitted_admin',
            $this->templateVariables(),
            'New request received - {student_name} ({tracking_id})'
        );

        $this->branding = $template['branding'];
        $this->subjectLine = $template['subject'];
        $this->renderedBody = $template['body_html'];
        $this->renderedText = $template['body_text'];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-submitted-admin',
            text: 'emails.text.request-submitted-admin',
            with: [
                'request' => $this->request,
                'detailsUrl' => $this->detailsUrl,
                'body' => $this->renderedBody,
                'textBody' => $this->renderedText,
                'branding' => $this->branding,
            ],
        );
    }

    protected function templateVariables(): array
    {
        $fullName = trim(implode(' ', array_filter([
            $this->request->student_name,
            $this->request->middle_name,
            $this->request->last_name,
        ])));

        return [
            'student_name' => $this->request->student_name,
            'student_full_name' => $fullName,
            'student_email' => $this->request->student_email,
            'purpose' => $this->request->purpose ?? '',
            'request_id' => (string) $this->request->id,
            'tracking_id' => $this->request->tracking_id,
            'university' => $this->request->university ?? '',
            'admin_link' => $this->detailsUrl,
            'submitted_at' => optional($this->request->created_at)->format('M d, Y h:i A') ?? '',
        ];
    }
}
