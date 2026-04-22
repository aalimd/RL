<?php

namespace App\Mail;

use App\Models\Request as RequestModel;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestStatusUpdated extends Mailable
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
            ['request_status_updated', 'request_status_update'],
            $this->templateVariables(),
            'Request status updated: {status} - {tracking_id}'
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
            view: 'emails.request-status-updated',
            text: 'emails.text.request-status-updated',
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
        $studentMessage = $this->statusMessage();

        return [
            'student_name' => $this->request->student_name,
            'status' => ucfirst($this->request->status),
            'admin_message' => $studentMessage,
            'student_message' => $studentMessage,
            'rejection_reason' => $this->request->rejection_reason ?? '',
            'tracking_link' => $this->trackingUrl,
            'tracking_id' => $this->request->tracking_id,
            'request_id' => (string) $this->request->id,
            'status_explanation' => $studentMessage,
        ];
    }

    protected function statusMessage(): string
    {
        $message = $this->request->status === 'Rejected'
            ? ($this->request->rejection_reason ?? null)
            : ($this->request->admin_message ?? null);

        return trim((string) $message);
    }
}
