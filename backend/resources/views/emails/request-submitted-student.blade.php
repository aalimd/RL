@php
    $contentHtml = isset($body) && $body
        ? $body
        : view('emails.partials.request-submitted-student-body', [
            'request' => $request,
        ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'Request received',
    'title' => 'Your request is in the queue',
    'summary' => 'We received your request and saved the tracking details you will need for future updates.',
    'contentHtml' => $contentHtml,
    'ctaUrl' => $trackingUrl,
    'ctaLabel' => 'Open tracking page',
    'closingNote' => 'Keep your tracking ID available whenever you need to review the latest status.',
])
