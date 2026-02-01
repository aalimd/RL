<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Request as RequestModel;

class RequestSubmittedToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public RequestModel $request;
    public string $detailsUrl;
    protected $template;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->detailsUrl = url('/admin/requests/' . $request->id);
        $this->template = \App\Models\EmailTemplate::where('name', 'request_submitted_admin')->first();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template ? $this->replaceVariables($this->template->subject) : 'New Recommendation Request Received',
        );
    }

    public function content(): Content
    {
        $body = $this->template ? $this->replaceVariables($this->template->body) : "New request from {$this->request->student_name}.";

        return new Content(
            view: 'emails.generic',
            with: ['body' => $body, 'subject' => $this->envelope()->subject],
        );
    }

    protected function replaceVariables($content)
    {
        $vars = [
            '{student_name}' => $this->request->student_name,
            '{purpose}' => $this->request->purpose,
            '{admin_link}' => $this->detailsUrl,
        ];
        return str_replace(array_keys($vars), array_values($vars), $content);
    }
}
