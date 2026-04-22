<?php

namespace App\Mail;

use App\Services\EmailBrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $branding;
    public string $subjectLine;

    public function __construct()
    {
        $this->branding = app(EmailBrandingService::class)->getBranding();
        $this->subjectLine = 'SMTP test successful - ' . $this->branding['site_name'];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test-email',
            text: 'emails.text.test-email',
            with: [
                'branding' => $this->branding,
            ],
        );
    }
}
