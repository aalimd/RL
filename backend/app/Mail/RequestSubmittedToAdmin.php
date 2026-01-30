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

    public function __construct(RequestModel $request)
    {
        $this->request = $request;
        $this->detailsUrl = url('/admin/requests/' . $request->id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Recommendation Request Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-submitted-admin',
        );
    }
}
