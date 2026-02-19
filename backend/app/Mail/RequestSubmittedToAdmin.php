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
    public ?string $renderedBody;
    protected $template;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->detailsUrl = url('/admin/requests/' . $request->id);
        $this->template = \App\Models\EmailTemplate::where('name', 'request_submitted_admin')->first();
        $this->renderedBody = $this->template ? $this->replaceVariables($this->template->body) : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template ? $this->replaceVariables($this->template->subject) : 'New Recommendation Request Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-submitted-admin',
            with: [
                'request' => $this->request,
                'detailsUrl' => $this->detailsUrl,
                'body' => $this->renderedBody,
            ],
        );
    }

    protected function replaceVariables($content)
    {
        $vars = [
            '{student_name}' => $this->request->student_name,
            '{purpose}' => $this->request->purpose,
            '{request_id}' => (string) $this->request->id,
            '{university}' => $this->request->university ?? 'Not specified',
            '{admin_link}' => $this->detailsUrl,
        ];
        return str_replace(array_keys($vars), array_values($vars), $content);
    }
}
