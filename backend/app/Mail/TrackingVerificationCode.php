<?php

namespace App\Mail;

use App\Models\Request;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrackingVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public $requestModel;
    public $otp;
    public array $branding;
    public string $subjectLine;
    public ?string $renderedBody;
    public ?string $renderedText;

    /**
     * Create a new message instance.
     */
    public function __construct(Request $requestModel, $otp)
    {
        $this->requestModel = $requestModel;
        $this->otp = $otp;
        $template = app(EmailTemplateRenderer::class)->render(
            'tracking_verification',
            $this->templateVariables(),
            'Your access code for request {tracking_id}'
        );

        $this->branding = $template['branding'];
        $this->subjectLine = $template['subject'];
        $this->renderedBody = $template['body_html'];
        $this->renderedText = $template['body_text'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tracking_verification',
            text: 'emails.text.tracking_verification',
            with: [
                'body' => $this->renderedBody,
                'textBody' => $this->renderedText,
                'branding' => $this->branding,
                'requestModel' => $this->requestModel,
                'otp' => $this->otp,
            ],
        );
    }

    protected function templateVariables(): array
    {
        return [
            'student_name' => $this->requestModel->student_name,
            'tracking_id' => $this->requestModel->tracking_id,
            'otp' => $this->otp,
            'request_id' => (string) $this->requestModel->id,
            'expires_in_minutes' => '5',
        ];
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
