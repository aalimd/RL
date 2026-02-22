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
    public ?string $renderedBody;
    protected $template;

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->trackingUrl = url('/track/' . $request->tracking_id);
        $this->template = \App\Models\EmailTemplate::whereIn('name', ['request_status_updated', 'request_status_update'])->first();
        $this->renderedBody = $this->template ? $this->replaceVariables($this->template->body) : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template ? $this->replaceVariables($this->template->subject) : 'Your Recommendation Request Has Been Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-status-updated',
            with: [
                'request' => $this->request,
                'trackingUrl' => $this->trackingUrl,
                'body' => $this->renderedBody,
            ],
        );
    }

    protected function replaceVariables($content)
    {
        $vars = [
            '{student_name}' => $this->request->student_name,
            '{status}' => ucfirst($this->request->status),
            '{admin_message}' => $this->request->admin_message ?? '',
            '{tracking_link}' => $this->trackingUrl,
            '{tracking_id}' => $this->request->tracking_id,
            '{request_id}' => (string) $this->request->id,
        ];
        return str_replace(array_keys($vars), array_values($vars), $content);
    }
}
