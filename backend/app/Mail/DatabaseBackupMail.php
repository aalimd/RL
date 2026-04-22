<?php

namespace App\Mail;

use App\Services\EmailBrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $backupPath;
    public $backupDate;
    public array $branding;
    public string $subjectLine;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($backupPath)
    {
        $this->backupPath = $backupPath;
        $this->backupDate = date('Y-m-d H:i:s');
        $this->branding = app(EmailBrandingService::class)->getBranding();
        $this->subjectLine = sprintf(
            'Database backup completed - %s - %s',
            $this->branding['site_name'],
            date('Y-m-d')
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.database_backup',
            text: 'emails.text.database_backup',
            with: [
                'backupDate' => $this->backupDate,
                'branding' => $this->branding,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->backupPath)
                ->as('backup_' . date('Y-m-d') . '.sql')
                ->withMime('application/sql'),
        ];
    }
}
