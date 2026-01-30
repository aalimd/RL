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

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->trackingUrl = url('/track/' . $request->tracking_id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Recommendation Request Has Been Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-status-updated',
        );
    }
}
