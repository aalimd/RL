@php
    $contentHtml = view('emails.partials.test-email-body', [
        'branding' => $branding ?? [],
    ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'Email test',
    'title' => 'Email delivery is working',
    'summary' => 'This message was sent from the application to verify SMTP connectivity and sender settings.',
    'contentHtml' => $contentHtml,
])
