<?php

namespace App\Mail;

use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $recipientName;
    public string $actionLabel;
    public array $branding;
    public string $subjectLine;
    public ?string $renderedBody;
    public ?string $renderedText;

    public function __construct(string $code, ?string $recipientName = null, string $actionLabel = 'complete sign-in')
    {
        $this->code = $code;
        $this->recipientName = trim((string) $recipientName) ?: 'there';
        $this->actionLabel = $actionLabel;

        $template = app(EmailTemplateRenderer::class)->render(
            'two_factor_code',
            [
                'code' => $this->code,
                'student_name' => $this->recipientName,
                'user_name' => $this->recipientName,
                'action_label' => $this->actionLabel,
                'expires_in_minutes' => '10',
            ],
            'Your verification code for {site_name}'
        );

        $this->branding = $template['branding'];
        $this->subjectLine = $template['subject'];
        $this->renderedBody = $template['body_html'];
        $this->renderedText = $template['body_text'];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-code',
            text: 'emails.text.two-factor-code',
            with: [
                'body' => $this->renderedBody,
                'textBody' => $this->renderedText,
                'branding' => $this->branding,
                'code' => $this->code,
                'recipientName' => $this->recipientName,
                'actionLabel' => $this->actionLabel,
            ],
        );
    }
}
