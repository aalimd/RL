@php
    $contentHtml = isset($body) && $body
        ? $body
        : view('emails.partials.request-status-updated-body', [
            'request' => $request,
        ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'Status update',
    'title' => 'Your request status has changed',
    'summary' => 'Review the latest outcome or next step for your recommendation request.',
    'contentHtml' => $contentHtml,
    'ctaUrl' => $trackingUrl,
    'ctaLabel' => 'Review status',
    'closingNote' => 'Your tracking page will always show the current status and any related notes.',
])
