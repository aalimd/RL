<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Request as RequestModel;

class RequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public RequestModel $request;
    public string $trackingUrl;
    protected $template;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->trackingUrl = url('/track/' . $request->tracking_id);
        $this->template = \App\Models\EmailTemplate::where('name', 'request_status_updated')->first();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template ? $this->replaceVariables($this->template->subject) : 'Your Recommendation Request Has Been Updated',
        );
    }

    public function content(): Content
    {
        $body = $this->template ? $this->replaceVariables($this->template->body) : "Status updated to {$this->request->status}.";

        return new Content(
            view: 'emails.generic',
            with: ['body' => $body, 'subject' => $this->envelope()->subject],
        );
    }

    protected function replaceVariables($content)
    {
        $vars = [
            '{student_name}' => $this->request->student_name,
            '{status}' => ucfirst($this->request->status),
            '{admin_message}' => $this->request->admin_message ?? '',
            '{tracking_link}' => $this->trackingUrl,
        ];
        return str_replace(array_keys($vars), array_values($vars), $content);
    }
}
