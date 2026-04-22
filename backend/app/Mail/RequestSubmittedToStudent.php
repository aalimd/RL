<?php

namespace App\Mail;

use App\Models\Request as RequestModel;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestSubmittedToStudent extends Mailable
{
    use Queueable, SerializesModels;

    public RequestModel $request;
    public string $trackingUrl;
    public array $branding;
    public string $subjectLine;
    public ?string $renderedBody;
    public ?string $renderedText;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->trackingUrl = url('/track/' . $request->tracking_id);
        $template = app(EmailTemplateRenderer::class)->render(
            'request_submitted_student',
            $this->templateVariables(),
            'Request received - Tracking ID {tracking_id}'
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
            view: 'emails.request-submitted-student',
            text: 'emails.text.request-submitted-student',
            with: [
                'request' => $this->request,
                'trackingUrl' => $this->trackingUrl,
                'body' => $this->renderedBody,
                'textBody' => $this->renderedText,
                'branding' => $this->branding,
            ],
        );
    }

    protected function templateVariables(): array
    {
        return [
            'student_name' => $this->request->student_name,
            'tracking_id' => $this->request->tracking_id,
            'request_id' => (string) $this->request->id,
            'tracking_link' => $this->trackingUrl,
            'university' => $this->request->university ?? '',
            'purpose' => $this->request->purpose ?? '',
            'student_email' => $this->request->student_email,
            'submitted_at' => optional($this->request->created_at)->format('M d, Y h:i A') ?? '',
        ];
    }
}
